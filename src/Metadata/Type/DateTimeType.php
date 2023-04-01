<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

/**
 * @template T of \DateTimeInterface
 *
 * @extends ObjectType<T>
 *
 * @property class-string<\DateTime|\DateTimeImmutable> $classname
 */
class DateTimeType extends ObjectType
{
    /**
     * @param class-string<T> $classname
     */
    public function __construct(string $classname)
    {
        if (\DateTimeInterface::class === $classname) {
            $classname = \DateTimeImmutable::class;
        }

        parent::__construct($classname);
    }

    public function resolve(mixed $value, Argument $argument): \DateTimeInterface
    {
        $timezone = false;
        if (\is_array($value) && ($value['date'] ?? false)) {
            $timezone = $value['timezone'] ?? false;
            $value = $value['date'];
        }

        if (!\is_string($value)) {
            if ($value instanceof $this->classname) {
                return $value;
            }

            throw new InvalidTypeException($value, 'string');
        }

        if ($format = $argument->getAttribute(DateTimeFormat::class)?->format ?? false) {
            $object = $this->classname::createFromFormat($format, $value, $timezone ? new \DateTimeZone($timezone) : null);
            if (!$object) {
                throw new InvalidTypeException($value, "string<$format>");
            }

            return $object;
        }

        return new $this->classname($value, $timezone ? new \DateTimeZone($timezone) : null);
    }
}
