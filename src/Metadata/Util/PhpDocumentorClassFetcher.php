<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Util;

use Guennichi\Mapper\Exception\MapperException;
use phpDocumentor\Reflection\Fqsen;

class PhpDocumentorClassFetcher
{
    /**
     * @return class-string
     */
    public static function fromFqsen(?Fqsen $fqsen): string
    {
        $classname = trim($fqsen?->__toString() ?? '', '\\');
        if (!class_exists($classname) && !interface_exists($classname)) {
            throw new MapperException(sprintf('Type "%s" not found', $classname));
        }

        return $classname;
    }
}
