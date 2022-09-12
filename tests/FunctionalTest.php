<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper;

use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\MapperInterface;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\ParameterFactory;
use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\PhpDocumentorParameterTypeFactory;
use Guennichi\Mapper\Metadata\Factory\Type\Source\ReflectionParameterTypeFactory;
use Guennichi\Mapper\Metadata\Repository\ConstructorInMemoryRepository;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPUnit\Framework\TestCase;
use Tests\Guennichi\Mapper\Fixture\Something;
use Tests\Guennichi\Mapper\Fixture\SomethingCollection;

class FunctionalTest extends TestCase
{
    private MapperInterface $mapper;

    protected function setUp(): void
    {
        $this->mapper = new Mapper(
            new ConstructorFetcher(
                new ConstructorFactory(
                    new ParameterFactory(
                        new ParameterTypeFactory(
                            new ReflectionParameterTypeFactory(),
                            new PhpDocumentorParameterTypeFactory(
                                DocBlockFactory::createInstance(),
                                new ContextFactory(),
                            ),
                        ),
                    ),
                ),
                new ConstructorInMemoryRepository(),
            ),
        );
    }

    public function testItMapsObjectCollectionWithValidData(): void
    {
        $input = [
            [
                'paramOne' => 'test1',
                'paramTwo' => [
                    'key1' => 123.,
                    'key2' => 456.55,
                ],
                'paramThree' => true,
            ],
            [
                'paramOne' => 'test2',
                'paramTwo' => [
                    'key1' => 444.,
                    'key2' => 666.77,
                ],
                'paramThree' => false,
                'paramFour' => 50,
            ],
            [
                'paramOne' => 'test3',
                'paramTwo' => [
                    'key1' => 444.,
                    'key2' => 666.77,
                ],
                'paramThree' => 'on', // Flexible
            ],
            [
                'paramOne' => 'test4',
                'paramTwo' => [
                    'key1' => 444.,
                    'key2' => 666.77,
                ],
                'paramThree' => 'off', // Flexible => FALSE
                // Skip paramFour...
                'paramFive' => 50, // Compound: int
            ],
            [
                'paramOne' => 'test5',
                'paramTwo' => [
                    'key1' => 444.,
                    'key2' => 666.77,
                ],
                'paramThree' => 'off', // Flexible => FALSE
                // Skip paramFour...
                'paramFive' => [1, 2], // Compound: array
            ],
            [
                'paramOne' => 'test6',
                'paramTwo' => [
                    'key1' => 444.,
                    'key2' => 666.77,
                ],
                'paramThree' => 'off', // Flexible => FALSE
                // Skip paramFour...
                'paramFive' => null, // Compound: null
            ],
        ];

        $expectedResult = new SomethingCollection(
            new Something('test1', [
                'key1' => 123.,
                'key2' => 456.55,
            ], true, 0, null),
            new Something('test2', [
                'key1' => 444.,
                'key2' => 666.77,
            ], false, 50, null),
            new Something('test3', [
                'key1' => 444.,
                'key2' => 666.77,
            ], true, paramFive: null),
            new Something('test4', [
                'key1' => 444.,
                'key2' => 666.77,
            ], false, paramFive: 50),
            new Something('test5', [
                'key1' => 444.,
                'key2' => 666.77,
            ], false, paramFive: [1, 2]),
            new Something('test6', [
                'key1' => 444.,
                'key2' => 666.77,
            ], false, paramFive: null),
        );

        $this->assertEquals($expectedResult, $this->mapper->map($input, SomethingCollection::class));
    }
}
