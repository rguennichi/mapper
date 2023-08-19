<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Exception\MapperException;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\BackedEnumType;
use Guennichi\Mapper\Metadata\Type\BooleanType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\DateTimeType;
use Guennichi\Mapper\Metadata\Type\FalseType;
use Guennichi\Mapper\Metadata\Type\FloatType;
use Guennichi\Mapper\Metadata\Type\IntegerType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\NullType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\StringType;
use Guennichi\Mapper\Metadata\Type\TrueType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use Guennichi\Mapper\Metadata\Util\ClassnameDateTimeTypeChecker;
use Guennichi\Mapper\Metadata\Util\PhpDocumentorClassFetcher;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\PseudoTypes\False_;
use phpDocumentor\Reflection\PseudoTypes\True_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;

class PhpDocumentorArgumentTypeFactory implements ArgumentTypeFactoryInterface
{
    private readonly DocBlockFactoryInterface $docBlockFactory;

    /** @var array<int, array<string, TypeInterface>> */
    private array $cache = [];

    public function __construct(
        DocBlockFactoryInterface $docBlockFactory = null,
        private readonly ContextFactory $contextFactory = new ContextFactory(),
    ) {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
    }

    public function __invoke(\ReflectionParameter $reflectionParameter): TypeInterface
    {
        if (!$docComment = $reflectionParameter->getDeclaringFunction()->getDocComment()) {
            throw MapperException::createFromReflectionParameter('Doc type not found', $reflectionParameter);
        }

        $name = $reflectionParameter->getName();
        if ($cachedTypes = $this->cache[$checksum = crc32($docComment)] ?? null) {
            if (!($type = $cachedTypes[$name] ?? null)) {
                throw MapperException::createFromReflectionParameter('Doc type not found', $reflectionParameter);
            }

            return $type;
        }

        $this->cache[$checksum] = [];
        foreach (
            $this->docBlockFactory
                ->create($docComment, $this->contextFactory->createFromReflector($reflectionParameter))
                ->getTagsWithTypeByName('param') as $arg
        ) {
            \assert($arg instanceof Param);

            $argName = $arg->getVariableName();
            $argType = $arg->getType();

            if (!$argName || !$argType) {
                continue;
            }

            $this->cache[$checksum][$argName] = $this->createFromPhpDocumentorType(
                $argType,
                $reflectionParameter->getDeclaringClass()?->getName(),
                $argName,
            );
        }

        if (!($type = $this->cache[$checksum][$name] ?? null)) {
            throw MapperException::createFromReflectionParameter('Doc type not found', $reflectionParameter);
        }

        return $type;
    }

    private function createFromPhpDocumentorType(Type $documentorType, ?string $classname, string $argument): TypeInterface
    {
        if ($documentorType instanceof Compound) {
            $types = [];
            foreach ($documentorType as $item) {
                $types[] = $this->createFromPhpDocumentorType($item, $classname, $argument);
            }

            if (\count($types) < 2) {
                throw MapperException::createFromClassnameArgument(sprintf('Compound type should at least have two types, "%d" type found.', \count($types)), $classname, $argument);
            }

            if (2 === \count($types) && array_filter($types, static fn (TypeInterface $type) => $type instanceof NullType)) {
                return new NullableType($types[0] instanceof NullableType ? $types[1] : $types[0]);
            }

            return new CompoundType($types);
        }

        if ($documentorType instanceof Object_) {
            $classname = PhpDocumentorClassFetcher::fromFqsen($documentorType->getFqsen());

            if (is_subclass_of($classname, \BackedEnum::class)) {
                $backingReflectionType = (new \ReflectionEnum($classname))->getBackingType();
                if (!$backingReflectionType instanceof \ReflectionNamedType) {
                    throw MapperException::createFromClassnameArgument(sprintf('Backing type should always be of type "%s", "%s" given.', \ReflectionNamedType::class, get_debug_type($backingReflectionType)), $classname, $argument);
                }

                return new BackedEnumType($classname, match ($backingReflectionType->getName()) {
                    'string' => new StringType(),
                    'int' => new IntegerType(),
                    default => throw MapperException::createFromClassnameArgument(sprintf('Backing enum should be either "string" or "int", "%s" given.', $backingReflectionType->getName()), $classname, $argument),
                });
            }

            return ClassnameDateTimeTypeChecker::isDateTime($classname) ?
                /* @phpstan-ignore-next-line */
                new DateTimeType($classname) :
                new ObjectType($classname);
        }

        if ($documentorType instanceof Array_) {
            return new ArrayType(
                $this->createFromPhpDocumentorType($documentorType->getKeyType(), $classname, $argument),
                $this->createFromPhpDocumentorType($documentorType->getValueType(), $classname, $argument),
            );
        }

        if ($documentorType instanceof String_) {
            return new StringType();
        }

        if ($documentorType instanceof Integer) {
            return new IntegerType();
        }

        return match ($documentorType::class) {
            Collection::class => new CollectionType(
                PhpDocumentorClassFetcher::fromFqsen($documentorType->getFqsen()),
                $this->createFromPhpDocumentorType($documentorType->getValueType(), $classname, $argument),
            ),
            Nullable::class => new NullableType(
                $this->createFromPhpDocumentorType($documentorType->getActualType(), $classname, $argument),
            ),
            Float_::class => new FloatType(),
            True_::class => new TrueType(),
            False_::class => new FalseType(),
            Boolean::class => new BooleanType(),
            Null_::class => new NullType(),
            Mixed_::class => new MixedType(),
            default => throw MapperException::createFromClassnameArgument(sprintf('Doc type "%s" is not yet supported', $documentorType->__toString()), $classname, $argument),
        };
    }
}
