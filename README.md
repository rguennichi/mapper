# Mapper: Map arrays into PHP objects and collections

A lightweight library to map array data into PHP **immutable objects, collections and strongly typed arrays** via constructors.

## Benchmark

Check `benchmark/` directory for more details about the implementation.

| Benchmark          | Revs     | Its | mem_peak    | Mode | rstdev    |
|--------------------|----------|-----|-------------|------|-----|
| `cuyz/valinor` | 50000 | 3   | **2.428mb** | 88.361μs     | ±0.97%     |
| `symfony/serializer` | 50000 | 3   | 3.157mb     |  30.784μs    | ±1.96%    |
| `guennichi/mapper`   | 50000    | 3   | 2.484mb     | **10.646μs**     |  **±0.75%**   |


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
