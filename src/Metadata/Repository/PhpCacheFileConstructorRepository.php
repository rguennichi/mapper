<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Repository;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Metadata\Model\Argument;
use Guennichi\Mapper\Metadata\Model\Constructor;
use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\ObjectType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use Guennichi\Mapper\Metadata\Util\PhpCodeGenerator;
use Symfony\Component\Filesystem\Filesystem;

class PhpCacheFileConstructorRepository implements ConstructorRepositoryInterface
{
    private readonly string $cacheFilename;
    private readonly Filesystem $filesystem;

    /** @var array<string, Constructor> */
    private array $cache = [];

    /** @var array<string, callable(): Constructor> */
    private array $precompiledConstructors;

    public function __construct(string $cacheDirectory)
    {
        $this->filesystem = new Filesystem();
        $this->cacheFilename = rtrim($cacheDirectory, '/') . '/mapper_metadata.php';
    }

    public function add(Constructor $constructor): void
    {
        $this->precompiledConstructors ??= require $this->cacheFilename;
        if ($this->precompiledConstructors[$constructor->classname] ?? false) {
            // Avoid duplicating already existing classnames
            return;
        }

        $this->dump(
            str_replace(
                '//',
                sprintf(
                    "//\n\t'%s' => static fn() => %s,\n",
                    $constructor->classname,
                    $this->generateConstructorCode($constructor),
                ),
                file_get_contents($this->cacheFilename) ?: '',
            ),
        );

        $this->cache[$constructor->classname] = $constructor;
    }

    public function get(string $classname): ?Constructor
    {
        if ($constructor = $this->cache[$classname] ?? false) {
            return $constructor;
        }

        $this->precompiledConstructors ??= require $this->cacheFilename;
        if ($closure = $this->precompiledConstructors[$classname] ?? false) {
            return $this->cache[$classname] = $closure();
        }

        return null;
    }

    public function clear(): void
    {
        $this->cache = $this->precompiledConstructors = [];

        $this->dump("<?php\nreturn [\n\t//\n];");
    }

    private function generateConstructorCode(Constructor $constructor): string
    {
        return PhpCodeGenerator::new($constructor::class, [
            PhpCodeGenerator::str($constructor->classname),
            PhpCodeGenerator::array(array_map(fn (Argument $argument) => PhpCodeGenerator::new(
                $argument::class,
                [
                    PhpCodeGenerator::str($argument->name),
                    $this->generateTypeCode($argument->type),
                    PhpCodeGenerator::bool($argument->optional),
                    PhpCodeGenerator::bool($argument->variadic),
                    PhpCodeGenerator::bool($argument->trusted),
                    PhpCodeGenerator::bool($argument->flexible),
                    PhpCodeGenerator::array(array_map(fn (Attribute $attribute) => PhpCodeGenerator::new(
                        $attribute::class,
                        array_map(fn ($arg) => var_export($arg, true), array_values(get_object_vars($attribute))),
                    ), $argument->attributes)),
                ],
            ), $constructor->arguments)),
            $this->generateTypeCode($constructor->type),
        ]);
    }

    private function generateTypeCode(TypeInterface $type): string
    {
        if ($type instanceof CollectionType) {
            return PhpCodeGenerator::new($type::class, [
                PhpCodeGenerator::str($type->classname),
                $this->generateTypeCode($type->itemType),
            ]);
        }

        if ($type instanceof ObjectType) {
            return PhpCodeGenerator::new($type::class, [PhpCodeGenerator::str($type->classname)]);
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
                [PhpCodeGenerator::array(array_map(fn (TypeInterface $type) => $this->generateTypeCode($type), $type->types))],
            );
        }

        if ($type instanceof NullableType) {
            return PhpCodeGenerator::new($type::class, [$this->generateTypeCode($type->innerType)]);
        }

        return PhpCodeGenerator::new($type::class);
    }

    private function dump(string $content): void
    {
        $this->filesystem->dumpFile($this->cacheFilename, $content);

        // Compile cached file into bytecode cache
        if (\function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->cacheFilename, true);
        }
    }
}
