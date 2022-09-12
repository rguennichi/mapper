<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory\Type\Source;

use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionParameter;
use RuntimeException;

class PhpDocumentorParameterTypeFactory implements ParameterTypeFactoryInterface
{
    private const TAG_NAME = 'param';

    /** @var array<int, array<string, TypeInterface>> */
    private array $params = [];

    private readonly DocBlockFactoryInterface $docBlockFactory;

    public function __construct(
        ?DocBlockFactoryInterface $docBlockFactory = null,
        private readonly ContextFactory $contextFactory = new ContextFactory(),
    ) {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
    }

    public function create(ReflectionParameter $reflectionParameter): ?TypeInterface
    {
        if (!$docComment = $reflectionParameter->getDeclaringFunction()->getDocComment()) {
            return null;
        }

        $name = $reflectionParameter->getName();
        if (isset($this->params[$checksum = crc32($docComment)])) {
            return $this->params[$checksum][$name] ?? null;
        }

        $context = $this->contextFactory->createFromReflector($reflectionParameter);

        $this->params[$checksum] = [];
        /** @var Param $param */
        foreach ($this->docBlockFactory->create($docComment, $context)->getTagsWithTypeByName(self::TAG_NAME) as $param) {
            $varName = $param->getVariableName();
            $type = $param->getType();
            if (null === $varName || null === $type) {
                continue;
            }

            $this->params[$checksum][$varName] = $this->convertType($type);
        }

        return $this->params[$checksum][$name] ?? null;
    }

    private function convertType(Type $type): TypeInterface
    {
        if ($type instanceof Compound) {
            $phpdocTypes = $type;
            $convertedTypes = [];

            $hasNullType = false;

            foreach ($phpdocTypes as $phpdocType) {
                if (!$hasNullType && $phpdocType instanceof Null_) {
                    $hasNullType = true;
                }

                $convertedTypes[] = $this->convertType($phpdocType);
            }

            if ($hasNullType && 2 === \count($convertedTypes)) {
                if ($convertedTypes[0]::class === NullType::class) {
                    return new NullableType($convertedTypes[1]);
                }

                return new NullableType($convertedTypes[0]);
            }

            return new CompoundType(...$convertedTypes);
        }

        if ($type instanceof AbstractList) {
            $keyType = $type->getKeyType();

            return match ($type::class) {
                Array_::class => new ArrayType(
                    match ($keyType::class) {
                        Integer::class, Compound::class => new IntegerType(),
                        String_::class => new StringType(),
                        default => throw new RuntimeException('Invalid key type "%s' . $keyType::class . '"'),
                    },
                    $this->convertType($type->getValueType()),
                ),
                Collection::class => new CollectionType($this->getClassname($type->getFqsen()), $this->convertType($type->getValueType())),
                default => throw new RuntimeException(sprintf('List of type "%s" is not supported', $type->__toString())),
            };
        }

        if ($type instanceof String_) {
            return new StringType();
        }

        if ($type instanceof Integer) {
            return new IntegerType();
        }

        if ($type instanceof Boolean) {
            return new BooleanType();
        }

        if ($type instanceof Object_) {
            $classname = $this->getClassname($type->getFqsen());

            return isset(DateTimeType::SUPPORTED_TYPES[$classname]) ? new DateTimeType($classname) : new ObjectType($classname);
        }

        return match ($type::class) {
            Null_::class => new NullType(),
            Float_::class => new FloatType(),
            Nullable::class => new NullableType($this->convertType($type->getActualType())),
            default => throw new RuntimeException(sprintf('Type "%s" is not supported', $type::class)),
        };
    }

    private function getClassname(?Fqsen $fqsen): string
    {
        return trim((string) $fqsen?->__toString(), '\\');
    }
}
