<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

class DateTimeType extends ObjectType
{
    public const SUPPORTED_TYPES = [
        \DateTimeInterface::class => true,
        \DateTimeImmutable::class => true,
        \DateTime::class => true,
    ];

    public function __construct(string $classname)
    {
        if (!isset(self::SUPPORTED_TYPES[$classname])) {
            throw new \RuntimeException(sprintf('Expecting "%s", "%s" given', implode('|', array_keys(self::SUPPORTED_TYPES)), $classname));
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

        $dateTimeFormat = $context->attribute(DateTimeFormat::class)?->format ?? null;
        if (null !== $dateTimeFormat) {
            $object = \DateTime::class === $this->classname ? \DateTime::createFromFormat($dateTimeFormat, $input) : \DateTimeImmutable::createFromFormat($dateTimeFormat, $input);
            if (false === $object) {
                throw new UnexpectedValueException(false, $this->classname, $context);
            }

            return $object;
        }

        try {
            return \DateTime::class === $this->classname ? new \DateTime($input) : new \DateTimeImmutable($input);
        } catch (\Exception) {
            throw new UnexpectedValueException($input, $this->classname, $context);
        }
    }
}
