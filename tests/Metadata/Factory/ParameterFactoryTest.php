<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Metadata\Factory\ParameterFactory;
use Guennichi\Mapper\Metadata\Factory\Type\ParameterTypeFactory;
use Guennichi\Mapper\Metadata\Member\Parameter;
use Guennichi\Mapper\Metadata\Type\TypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Tests\Guennichi\Mapper\Fixture\Attribute\External;
use Tests\Guennichi\Mapper\Fixture\Attribute\Internal;

class ParameterFactoryTest extends TestCase
{
    private ParameterFactory $parameterFactory;
    private ParameterTypeFactory&MockObject $typeFactory;

    protected function setUp(): void
    {
        $this->parameterFactory = new ParameterFactory(
            $this->typeFactory = $this->createMock(ParameterTypeFactory::class),
        );
    }

    /**
     * @dataProvider factoryDataProvider
     */
    public function testItCreatesParameterInstance(\ReflectionParameter $reflectionParameter, Parameter $expectedResult): void
    {
        $this->typeFactory->expects($this->once())
            ->method('create')
            ->with($reflectionParameter)
            ->willReturn($this->createMock(TypeInterface::class));

        self::assertEquals($expectedResult, $this->parameterFactory->create($reflectionParameter));
    }

    /**
     * @return array<array-key, array{ReflectionParameter|null, Parameter}>
     */
    public function factoryDataProvider(): array
    {
        return [
            'parameter_name' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(
                        public readonly string $exampleParam = 'test',
                    ) {
                    }
                }),
                new Parameter(
                    'exampleParam',
                    $this->createMock(TypeInterface::class),
                    false,
                    [],
                ),
            ],
            'optional_parameter' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(
                        public readonly string $param = 'test',
                    ) {
                    }
                }),
                new Parameter(
                    'param',
                    $this->createMock(TypeInterface::class),
                    false,
                    [],
                ),
            ],
            'required_parameter' => [
                $this->createReflectionParameter(new class('test') {
                    public function __construct(
                        public readonly string $param,
                    ) {
                    }
                }),
                new Parameter(
                    'param',
                    $this->createMock(TypeInterface::class),
                    true,
                    [],
                ),
            ],
            'variadic_parameter' => [
                $this->createReflectionParameter(new class('test') {
                    /** @var array<string> */
                    public readonly array $params;

                    public function __construct(
                        string ...$param,
                    ) {
                        $this->params = $param;
                    }
                }),
                new Parameter(
                    'param',
                    $this->createMock(TypeInterface::class),
                    false,
                    [],
                ),
            ],
            'consider_internal_attributes' => [
                $this->createReflectionParameter(new class('test') {
                    public function __construct(
                        #[Internal]
                        public readonly string $param,
                    ) {
                    }
                }),
                new Parameter(
                    'param',
                    $this->createMock(TypeInterface::class),
                    true,
                    [Internal::class => new Internal()],
                ),
            ],
            'ignore_external_attributes' => [
                $this->createReflectionParameter(new class() {
                    public function __construct(
                        #[External]
                        public readonly string $param = 'test',
                    ) {
                    }
                }),
                new Parameter(
                    'param',
                    $this->createMock(TypeInterface::class),
                    false,
                    [],
                ),
            ],
        ];
    }

    private function createReflectionParameter(object $object): ?\ReflectionParameter
    {
        return (new \ReflectionClass($object))->getConstructor()?->getParameters()[0];
    }
}
