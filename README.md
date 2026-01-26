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

### 1. Set Up the Mapper

```php
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Repository\InMemoryConstructorRepository;

// Create the mapper instance
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
        new InMemoryConstructorRepository(), // Use PhpCacheFileConstructorRepository for production
    ),
);
```

### 2. Define Your Classes

```php
// Simple object
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly bool $inStock,
    ) {}
}

// Collection class (for arrays of objects)
/**
 * @extends Collection<Product>
 */
final class ProductCollection extends Collection
{
    public function __construct(Product ...$products)
    {
        parent::__construct($products);
    }
}
```

### 3. Map Arrays to Objects

```php
// Map a single object
$data = [
    'name' => 'Laptop',
    'price' => 1299.99,
    'inStock' => true,
];

$product = $mapper($data, Product::class);
// $product is now a Product object with readonly properties

// Map a collection
$productsData = [
    ['name' => 'Laptop', 'price' => 1299.99, 'inStock' => true],
    ['name' => 'Mouse', 'price' => 29.99, 'inStock' => false],
];

$products = $mapper($productsData, ProductCollection::class);
// $products is now a ProductCollection containing Product objects
```

## Real-World Example: Mapping API Responses

One of the most common use cases is transforming API responses into strongly-typed objects. Here's a complete example:

```php
use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Name;

// API Response DTOs
final class ApiProduct
{
    /**
     * @param array<ApiImage> $images
     */
    public function __construct(
        #[Name('productName')] // API uses 'productName', we use 'name'
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $images,
        public readonly ApiOfferList $offers,
        #[Flexible] // API sends 'yes'/'no', we want boolean
        public readonly bool $marketplace,
        #[DateTimeFormat('Y-m-d')] // Custom date format
        public readonly \DateTimeInterface $createdAt,
        public readonly ProductTypeEnum $type,
    ) {}
}

final class ApiImage
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
    ) {}
}

final class ApiOffer
{
    public function __construct(
        public readonly string $title,
        public readonly ApiImage $image,
        public readonly float $rating,
        #[Flexible]
        public readonly bool $active,
    ) {}
}

/**
 * @extends Collection<ApiOffer>
 */
final class ApiOfferList extends Collection
{
    public function __construct(ApiOffer ...$offers)
    {
        parent::__construct($offers);
    }
}

enum ProductTypeEnum: string
{
    case Type1 = 'type1';
    case Type2 = 'type2';
}

// Abstract base collection class
abstract class Collection implements \IteratorAggregate
{
    public function __construct(public readonly array $collection) {}
    
    public function getIterator(): \Traversable
    {
        yield from $this->collection;
    }
}

// Usage: Transform API response
$apiResponse = [
    'productName' => 'Gaming Laptop',
    'description' => 'High-performance gaming laptop',
    'images' => [
        ['title' => 'Front view', 'url' => 'https://example.com/front.jpg'],
        ['title' => 'Side view', 'url' => 'https://example.com/side.jpg'],
    ],
    'offers' => [
        [
            'title' => 'Special Offer',
            'image' => ['title' => 'Offer', 'url' => 'https://example.com/offer.jpg'],
            'rating' => 4.5,
            'active' => 'yes', // Flexible attribute converts 'yes' to true
        ],
    ],
    'marketplace' => 'yes',
    'createdAt' => '2023-02-10',
    'type' => 'type1',
];

$product = $mapper($apiResponse, ApiProduct::class);
// Now you have a fully typed, immutable object with nested objects and collections!
```

## Migration from `serialize()` / `unserialize()`

If you're currently using PHP's `serialize()` and `unserialize()` functions, here's how to migrate:

### Before (using serialize)

```php
// Storing data
$product = new Product('Laptop', 1299.99, true);
$serialized = serialize($product);
file_put_contents('product.dat', $serialized);

// Loading data
$serialized = file_get_contents('product.dat');
$product = unserialize($serialized);
```

**Problems:**
- ❌ Not type-safe
- ❌ Requires the exact class to exist
- ❌ Security concerns with untrusted data
- ❌ No validation

### After (using Mapper)

```php
// Storing data (as JSON)
$product = new Product('Laptop', 1299.99, true);
$data = [
    'name' => $product->name,
    'price' => $product->price,
    'inStock' => $product->inStock,
];
file_put_contents('product.json', json_encode($data));

// Loading data
$data = json_decode(file_get_contents('product.json'), true);
$product = $mapper($data, Product::class);
```

**Benefits:**
- ✅ Type-safe with validation
- ✅ Works with immutable objects
- ✅ Safe with untrusted data (validates types)
- ✅ Human-readable JSON format
- ✅ Better performance in most cases

## Basic Usage

### Simple Object Mapping

The mapper matches array keys to constructor parameter names:

```php
final class User
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}

$data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
$user = $mapper($data, User::class);
```

### Collections

Collections must:
1. Extend a class that implements `\Traversable`
2. Have a variadic constructor (using `...`)

```php
/**
 * @extends Collection<User>
 */
final class UserList extends Collection
{
    public function __construct(User ...$users)
    {
        parent::__construct($users);
    }
}

$usersData = [
    ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
    ['name' => 'Jane', 'email' => 'jane@example.com', 'age' => 25],
];
$users = $mapper($usersData, UserList::class);
```

### Nested Objects

The mapper automatically handles nested objects:

```php
final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

final class Person
{
    public function __construct(
        public readonly string $name,
        public readonly Address $address,
    ) {}
}

$data = [
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
    ],
];
$person = $mapper($data, Person::class);
```

## Supported Types

- ✅ Scalar types: `string`, `int`, `float`, `bool`
- ✅ Arrays (with PHPDoc type hints)
- ✅ Nullable types: `?string`, `?int`, etc.
- ✅ Backed Enums: `enum MyEnum: string`
- ✅ DateTime / DateTimeImmutable
- ✅ Nested objects
- ✅ Collections
- ✅ Union types (compound types)

## Attributes

Mapper provides several attributes to customize mapping behavior:

- **`#[Name('fieldName')]`** - Map array key to different parameter name
- **`#[Flexible]`** - Allow flexible type coercion (e.g., 'yes'/'no' → boolean)
- **`#[DateTimeFormat('Y-m-d')]`** - Custom DateTime format parsing
- **`#[Trusted]`** - Skip type validation for performance (use with caution)

See [docs/USAGE.md](docs/USAGE.md) for detailed attribute documentation.

## Production Setup

For production, use `PhpCacheFileConstructorRepository` for better performance:

```php
use Guennichi\Mapper\Metadata\Repository\PhpCacheFileConstructorRepository;

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
        new PhpCacheFileConstructorRepository('/path/to/cache/directory'),
    ),
);
```

## Learn More

- 📖 **[Complete Documentation](docs/USAGE.md)** - Comprehensive guide with all features
- 🎯 **Attributes Reference** - Detailed attribute documentation
- ⚡ **Performance Tips** - Optimization strategies
- 🐛 **Troubleshooting** - Common issues and solutions

## License

MIT

## Credits

Special thanks to [@Gabriel Ostrolucký](https://github.com/ostrolucky) for his support and advice to make this happen.
