# Mapper: Map arrays into PHP objects and collections

A lightweight, high-performance PHP library that maps array data into **immutable objects, collections, and strongly typed arrays** via constructors. Perfect for transforming API responses, JSON data, or any array structure into type-safe PHP objects.

## Why Use Mapper?

- **🚀 Fast**: Up to 7x faster than Symfony Serializer (see benchmarks below)
- **🔒 Type-safe**: Leverages PHP 8.1+ type system and PHPDoc annotations
- **📦 Immutable**: Works with readonly properties for immutable objects
- **🎯 Simple**: Map arrays to objects using constructor parameters - no setters needed
- **⚡ Production-ready**: Built-in caching for optimal performance

## Benchmark

| Benchmark            | Revs     | Its | mem_peak    | Mode         | rstdev     |
|----------------------|----------|-----|-------------|--------------|------------|
| `unserialize()`      | 5000     | 5   | 1.724mb     | 9.547μs      | ±1.51%     |
| `symfony/serializer` | 5000     | 5   | 3.489mb     | 122.343μs    | ±0.84%     |
| `guennichi/mapper`   | 5000     | 5   | **2.972mb** | **16.638μs** | **±0.41%** |

Check `benchmark/` directory for more details about the implementation.

## Installation

```bash
composer require guennichi/mapper
```

**Requirements:** PHP ^8.1

## Quick Start

```php
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Repository\InMemoryConstructorRepository;

// Create the mapper
$mapper = new Mapper(
    new ConstructorFetcher(
        new ConstructorFactory(
            new ArgumentFactory(
                new ArgumentTypeFactory(
                    new PhpDocumentorArgumentTypeFactory(),
                    new ReflectionArgumentTypeFactory(),
                ),
            ),
        ),
        new InMemoryConstructorRepository(),
    ),
);

// Define your class
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
    ) {}
}

// Map array to object
$data = ['name' => 'Laptop', 'price' => 999.99];
$product = $mapper($data, Product::class);
```

## Real-World Example: API Response Mapping

Transform API responses into strongly-typed objects:

```php
use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Name;

final class ApiProduct
{
    public function __construct(
        #[Name('productName')] // Map 'productName' to $name
        public readonly string $name,
        #[Flexible] // Convert 'yes'/'no' to boolean
        public readonly bool $active,
        #[DateTimeFormat('Y-m-d')] // Custom date format
        public readonly \DateTimeInterface $createdAt,
    ) {}
}

$apiResponse = [
    'productName' => 'Gaming Laptop',
    'active' => 'yes', // Converted to true
    'createdAt' => '2023-02-10',
];

$product = $mapper($apiResponse, ApiProduct::class);
```

See [docs/USAGE.md](docs/USAGE.md) for complete examples with nested objects, collections, and more.

## Migration from `serialize()` / `unserialize()`

**Before:**
```php
$serialized = serialize($product);
$product = unserialize($serialized);
```

**After:**
```php
$data = json_encode(['name' => $product->name, 'price' => $product->price]);
$product = $mapper(json_decode($data, true), Product::class);
```

**Benefits:** Type-safe, works with immutable objects, safe with untrusted data, human-readable JSON.

See [docs/USAGE.md](docs/USAGE.md) for detailed migration guide.

## Features

- ✅ Map arrays to objects via constructors
- ✅ Support for collections, nested objects, enums, DateTime
- ✅ Custom attributes for field mapping, type coercion, date formats
- ✅ Built-in caching for production performance
- ✅ Full type validation and error handling

## Documentation

📖 **[Complete Usage Guide](docs/USAGE.md)** - Comprehensive documentation covering:
- Configuration & setup (development and production)
- Collections and nested objects
- Complete type system reference
- All attributes with examples
- Performance optimization
- Error handling and troubleshooting
- Best practices

## License

MIT

## Credits

Special thanks to [@Gabriel Ostrolucký](https://github.com/ostrolucky) for his support and advice to make this happen.
