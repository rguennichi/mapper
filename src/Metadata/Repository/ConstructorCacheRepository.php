<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Metadata\Member\Constructor;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use Guennichi\Mapper\Util\PhpCodeGenerator;
use Symfony\Component\Filesystem\Filesystem;

class ConstructorCacheRepository implements ConstructorRepositoryInterface
{
    public const CACHE_FILENAME = 'mapper_metadata.php';

    private readonly ConstructorRepositoryInterface $inMemoryRepository;
    private readonly Filesystem $filesystem;
    private readonly string $cacheFile;
    /** @var array<string, callable> */
    private readonly array $cachedConstructors;

    public function __construct(private readonly string $cacheDirectory)
    {
        $this->filesystem = new Filesystem();
        $this->inMemoryRepository = new ConstructorInMemoryRepository();
        $this->cacheFile = $this->cacheDirectory . '/' . self::CACHE_FILENAME;
        if (file_exists($this->cacheFile)) {
            $this->cachedConstructors = require $this->cacheFile;
        } else {
            /* @phpstan-ignore-next-line */
            $this->cachedConstructors = [];
            // Create the cache file if not found
            $this->filesystem->dumpFile($this->cacheFile, "<?php\nreturn [\n\t//\n];");
        }
    }

    public function add(Constructor $constructor): void
    {
        $cacheContent = file_get_contents($this->cacheFile) ?: '';

        // Avoid duplicating already existing classnames
        if (str_contains($cacheContent, "'$constructor->classname' => static fn()")) {
            return;
        }

        $this->filesystem->dumpFile(
            $this->cacheFile,
            str_replace(
                '//',
                sprintf(
                    "//\n\t'%s' => static fn() => %s,\n",
                    $constructor->classname,
                    $this->generateConstructorCode($constructor),
                ),
                $cacheContent,
            ),
        );

        $this->inMemoryRepository->add($constructor);
    }

    public function get(string $classname): ?Constructor
    {
        if ($constructor = $this->inMemoryRepository->get($classname)) {
            return $constructor;
        }

        if (!$closure = $this->cachedConstructors[$classname] ?? null) {
            return null;
        }

        $this->inMemoryRepository->add($constructor = $closure());

        return $constructor;
    }

    private function generateConstructorCode(Constructor $constructor): string
    {
        return PhpCodeGenerator::new($constructor::class, [
            "'$constructor->classname'",
            $this->generateTypeCode($constructor->classType),
            PhpCodeGenerator::array(array_map(fn (Parameter $parameter) => PhpCodeGenerator::new(
                $parameter::class,
                [
                    "'$parameter->name'",
                    $this->generateTypeCode($parameter->type),
                    $parameter->required ? 'true' : 'false',
                    PhpCodeGenerator::array(array_map(fn (Attribute $attribute) => PhpCodeGenerator::new(
                        $attribute::class,
                        // TODO: improve this part, as args sorting can be handled incorrectly
                        array_map(fn ($arg) => var_export($arg, true), array_values(get_object_vars($attribute))),
                    ), $parameter->attributes)),
                ],
            ), $constructor->parameters)),
        ]);
    }

    private function generateTypeCode(TypeInterface $type): string
    {
        if ($type instanceof ObjectType) {
            return PhpCodeGenerator::new($type::class, [
                "'$type->classname'",
            ]);
        }

        if ($type instanceof CollectionType) {
            return PhpCodeGenerator::new($type::class, [
                "'$type->classname'",
                $this->generateTypeCode($type->valueType),
            ]);
        }

        if ($type instanceof ArrayType) {
            return PhpCodeGenerator::new($type::class, [
                $this->generateTypeCode($type->keyType),
                $this->generateTypeCode($type->valueType),
            ]);
        }

        if ($type instanceof CompoundType) {
            return PhpCodeGenerator::new(
                $type::class,
                array_map(fn (TypeInterface $type) => $this->generateTypeCode($type), $type->types),
            );
        }

        if ($type instanceof NullableType) {
            return PhpCodeGenerator::new($type::class, [$this->generateTypeCode($type->innerType)]);
        }

        return PhpCodeGenerator::new($type::class);
    }

    public function clear(): void
    {
        $this->filesystem->remove($this->cacheFile);
    }
}
