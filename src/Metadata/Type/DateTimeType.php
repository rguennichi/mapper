<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

class DateTimeType extends ObjectType
{
    /**
     * @param class-string<\DateTimeInterface> $classname
     */
    public function __construct(string $classname)
    {
        if (
            \DateTimeInterface::class !== $classname &&
            !\in_array($classname, [\DateTime::class, \DateTimeImmutable::class]) &&
            !is_subclass_of($classname, \DateTime::class) &&
            !is_subclass_of($classname, \DateTimeImmutable::class)
        ) {
            throw new \RuntimeException(sprintf('Expecting instance of "%s", "%s" given', \DateTimeInterface::class, $classname));
        }

        parent::__construct($classname);
    }

    public function resolve(mixed $input, Context $context): object
    {
        if ($input instanceof $this->classname) {
            return $input;
        }

        $context->visitClassname($this->classname);

        if (!\is_string($input)) {
            throw new UnexpectedValueException($input, 'string', $context);
        }

        $classname = \DateTimeInterface::class === $this->classname ? \DateTimeImmutable::class : $this->classname;
        $dateTimeFormat = $context->attribute(DateTimeFormat::class)?->format ?? null;
        if (null !== $dateTimeFormat) {
            if (false === $object = $classname::createFromFormat($dateTimeFormat, $input)) {
                throw new UnexpectedValueException(false, $this->classname, $context);
            }

            return $object;
        }

        try {
            return new $classname($input);
        } catch (\Exception) {
            throw new UnexpectedValueException($input, $classname, $context);
        }
    }
}
