# Mapper: Map arrays into PHP objects and collections

A lightweight library to map array data into PHP **immutable objects, collections and strongly typed arrays** via constructors.

## Benchmark

Check `benchmark/` directory for more details about the implementation.

| Benchmark          | Revs     | Its | mem_peak | Mode | rstdev     |
|--------------------|----------|-----|---------|------|------------|
| `cuyz/valinor` | 5000 | 5   | 2.429mb | 92.058μs     | ±1.38%     |
| `symfony/serializer` | 5000 | 5   | 3.178mb |  45.425μs    | **±0.81%** |
| `guennichi/mapper`   | 5000    | 5   | **1.622mb** | **12.407μs**     | **±0.81%** |


## Installation

```bash
composer require guennichi/mapper
```

## Usage

```php
use Guennichi\Collection\Collection;

final class Person
{
    public function __construct(public readonly string $name) {}
}

/**
 * @extends Collection<Person>
 */
final class PersonCollection extends Collection
{
    public function __construct(Person ...$elements)
    {
        parent::__construct(...$elements);
    }
}

$input = [
    ['name' => 'Person1'],
    ['name' => 'Person2'],
    ['name' => 'Person3'],
];

$mapper = new Guennichi\Mapper\Mapper(/** dependencies */)

$output = $mapper->map($input, PersonCollection::class);
// Result instance of PersonCollection(Person{"name": "Person1"}, Person{"name": "Person2"}, Person{"name": "Person3"})
```

### Dedication

Special thanks to my colleague [@Gabriel Ostrolucký](https://github.com/ostrolucky) for his support and advices to make this happen.
