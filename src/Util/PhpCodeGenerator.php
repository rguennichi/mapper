<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Util;

class PhpCodeGenerator
{
    /**
     * @param array<int, mixed> $arguments
     */
    public static function new(string $classname, array $arguments = []): string
    {
        return sprintf('new %s(%s)', $classname, implode(', ', $arguments));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function array(array $data): string
    {
        if (!$data) {
            return '[]';
        }

        $content = '';
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $value = self::array($value);
            }

            if (!\is_scalar($value)) {
                continue;
            }

            $content .= "\n\t" . var_export($key, true) . ' => ' . (\is_string($value) ? $value : var_export($value, true)) . ',';
        }

        return "[$content\n]";
    }
}
