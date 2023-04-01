<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Util;

use Guennichi\Mapper\Metadata\Util\PhpDocumentorClassFetcher;
use phpDocumentor\Reflection\Fqsen;
use PHPUnit\Framework\TestCase;

class PhpDocumentorClassFetcherTest extends TestCase
{
    public function testItFetchClassStringFromFqsenObject(): void
    {
        self::assertSame('stdClass', PhpDocumentorClassFetcher::fromFqsen(new Fqsen('\stdClass')));
        self::assertSame('Tests\Guennichi\Mapper\Fixture\Collection', PhpDocumentorClassFetcher::fromFqsen(new Fqsen('\Tests\Guennichi\Mapper\Fixture\Collection')));
        self::assertSame('DateTimeInterface', PhpDocumentorClassFetcher::fromFqsen(new Fqsen('\DateTimeInterface')));
    }

    public function testItFailsToFetchClassFromFqsenObjectIfClassDoesNotExists(): void
    {
        self::expectExceptionMessage('Type "Foo\Bar\NotFound" not found');

        PhpDocumentorClassFetcher::fromFqsen(new Fqsen('\Foo\Bar\NotFound'));
    }
}
