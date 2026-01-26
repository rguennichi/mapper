# Complete Usage Guide

This guide provides comprehensive documentation for all features of the guennichi/mapper library.

## Table of Contents

1. [Configuration & Setup](#configuration--setup)
2. [Basic Usage](#basic-usage)
3. [Collections](#collections)
4. [Nested Objects](#nested-objects)
5. [Type System](#type-system)
6. [Attributes Reference](#attributes-reference)
7. [Performance Optimization](#performance-optimization)
8. [Error Handling](#error-handling)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

## Configuration & Setup

### Development Setup

For development and testing, use `InMemoryConstructorRepository`:

```php
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Repository\InMemoryConstructorRepository;

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
```

### Production Setup

For production environments, use `PhpCacheFileConstructorRepository` to cache constructor metadata:

```php
use Guennichi\Mapper\Mapper;
use Guennichi\Mapper\Metadata\ConstructorFetcher;
use Guennichi\Mapper\Metadata\Factory\ArgumentFactory;
use Guennichi\Mapper\Metadata\Factory\ArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ConstructorFactory;
use Guennichi\Mapper\Metadata\Factory\PhpDocumentorArgumentTypeFactory;
use Guennichi\Mapper\Metadata\Factory\ReflectionArgumentTypeFactory;
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
        new PhpCacheFileConstructorRepository('/var/cache/mapper'),
    ),
);
```

**Note:** The cache directory must be writable. The repository automatically creates a `mapper_metadata.php` file containing precompiled constructor metadata.

### Dependency Injection Container Setup

If you're using a dependency injection container, you can register the mapper as a service:

```php
// Example with Symfony DI
services:
    Guennichi\Mapper\MapperInterface:
        class: Guennichi\Mapper\Mapper
        arguments:
            - '@Guennichi\Mapper\Metadata\ConstructorFetcher'
    
    Guennichi\Mapper\Metadata\ConstructorFetcher:
        arguments:
            - '@Guennichi\Mapper\Metadata\Factory\ConstructorFactory'
            - '@Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface'
    
    Guennichi\Mapper\Metadata\Repository\ConstructorRepositoryInterface:
        class: Guennichi\Mapper\Metadata\Repository\PhpCacheFileConstructorRepository
        arguments:
            - '%kernel.cache_dir%/mapper'
    
    # ... other dependencies
```

## Basic Usage

### Simple Object Mapping

The mapper converts arrays to objects by matching array keys to constructor parameter names:

```php
final class User
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
];

$user = $mapper($data, User::class);
// $user is now a User object with readonly properties
```

### Parameter Matching

The mapper matches array keys to constructor parameters in two ways:

1. **By name** (preferred): Array key matches parameter name
2. **By position** (fallback): Array key matches parameter position (0, 1, 2, ...)

```php
// Both work the same way:
$data1 = ['name' => 'John', 'email' => 'john@example.com']; // By name
$data2 = [0 => 'John', 1 => 'john@example.com']; // By position

$user = $mapper($data1, User::class);
// or
$user = $mapper($data2, User::class);
```

### Optional Parameters

Optional parameters (with default values) work as expected:

```php
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly bool $inStock = true, // Optional with default
    ) {}
}

// Works with or without 'inStock'
$product1 = $mapper(['name' => 'Laptop', 'price' => 999.99], Product::class);
$product2 = $mapper(['name' => 'Mouse', 'price' => 29.99, 'inStock' => false], Product::class);
```

## Collections

Collections allow you to map arrays of objects into strongly-typed collection classes.

### Collection Requirements

For a class to be recognized as a collection, it must:

1. **Extend a class that implements `\Traversable`** (like `\IteratorAggregate`)
2. **Have a variadic constructor** (using `...` spread operator)

### Example: Basic Collection

```php
// Base collection class (you can use your own or create one)
abstract class Collection implements \IteratorAggregate
{
    public function __construct(public readonly array $collection) {}
    
    public function getIterator(): \Traversable
    {
        yield from $this->collection;
    }
}

// Your collection class
/**
 * @extends Collection<Product>
 */
final class ProductCollection extends Collection
{
    public function __construct(Product ...$products) // Variadic constructor
    {
        parent::__construct($products);
    }
}

// Usage
$productsData = [
    ['name' => 'Laptop', 'price' => 999.99, 'inStock' => true],
    ['name' => 'Mouse', 'price' => 29.99, 'inStock' => false],
    ['name' => 'Keyboard', 'price' => 79.99, 'inStock' => true],
];

$products = $mapper($productsData, ProductCollection::class);
// $products is a ProductCollection containing Product objects
```

### PHPDoc Annotation

Always include the `@extends Collection<ItemType>` annotation for proper type inference:

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
```

### Nested Collections

Collections can contain other collections:

```php
/**
 * @extends Collection<Order>
 */
final class OrderList extends Collection
{
    public function __construct(Order ...$orders)
    {
        parent::__construct($orders);
    }
}

final class Order
{
    /**
     * @param ProductCollection $products
     */
    public function __construct(
        public readonly string $id,
        public readonly ProductCollection $products,
    ) {}
}

$data = [
    [
        'id' => 'order-1',
        'products' => [
            ['name' => 'Laptop', 'price' => 999.99],
            ['name' => 'Mouse', 'price' => 29.99],
        ],
    ],
];

$orders = $mapper($data, OrderList::class);
```

## Nested Objects

The mapper automatically handles nested objects recursively:

```php
final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $zipCode,
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
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'zipCode' => '10001',
    ],
];

$person = $mapper($data, Person::class);
// $person->address is automatically an Address object
```

### Deep Nesting

Nesting can be as deep as needed:

```php
final class Country
{
    public function __construct(public readonly string $name) {}
}

final class City
{
    public function __construct(
        public readonly string $name,
        public readonly Country $country,
    ) {}
}

final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly City $city,
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
        'city' => [
            'name' => 'New York',
            'country' => ['name' => 'USA'],
        ],
    ],
];

$person = $mapper($data, Person::class);
```

## Type System

The mapper supports a wide range of PHP types and provides type validation.

### Scalar Types

Basic scalar types are fully supported:

```php
final class Example
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly float $price,
        public readonly bool $active,
    ) {}
}
```

### Nullable Types

Use the `?` prefix for nullable types:

```php
final class User
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email, // Can be null
        public readonly ?int $age = null, // Optional and nullable
    ) {}
}

$data = ['name' => 'John']; // email and age are null
$user = $mapper($data, User::class);
```

### Arrays

Arrays are supported with PHPDoc type hints for better type inference:

```php
final class Product
{
    /**
     * @param array<string> $tags
     * @param array<Image> $images
     */
    public function __construct(
        public readonly string $name,
        public readonly array $tags, // Array of strings
        public readonly array $images, // Array of Image objects
    ) {}
}

final class Image
{
    public function __construct(
        public readonly string $url,
        public readonly string $alt,
    ) {}
}

$data = [
    'name' => 'Laptop',
    'tags' => ['electronics', 'computers', 'gaming'],
    'images' => [
        ['url' => 'https://example.com/img1.jpg', 'alt' => 'Front view'],
        ['url' => 'https://example.com/img2.jpg', 'alt' => 'Side view'],
    ],
];

$product = $mapper($data, Product::class);
```

### Backed Enums

Both string-backed and int-backed enums are supported:

```php
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}

final class Task
{
    public function __construct(
        public readonly string $title,
        public readonly Status $status,
        public readonly Priority $priority,
    ) {}
}

$data = [
    'title' => 'Complete documentation',
    'status' => 'active', // String value
    'priority' => 2, // Int value
];

$task = $mapper($data, Task::class);
```

### DateTime Types

Both `\DateTime` and `\DateTimeImmutable` are supported:

```php
final class Event
{
    public function __construct(
        public readonly string $name,
        public readonly \DateTime $startsAt,
        public readonly \DateTimeImmutable $endsAt,
    ) {}
}

$data = [
    'name' => 'Conference',
    'startsAt' => '2023-12-01T10:00:00+00:00', // ISO 8601 format
    'endsAt' => '2023-12-01T18:00:00+00:00',
];

$event = $mapper($data, Event::class);
```

**Default Format:** The mapper uses `DATE_ATOM` (ISO 8601) format by default. See the `#[DateTimeFormat]` attribute for custom formats.

### Union Types (Compound Types)

Union types are supported via PHPDoc annotations:

```php
final class Value
{
    /**
     * @param string|int $value
     */
    public function __construct(
        public readonly string|int $value,
    ) {}
}

// Both work:
$value1 = $mapper(['value' => 'hello'], Value::class);
$value2 = $mapper(['value' => 42], Value::class);
```

The mapper tries each type in the union until one succeeds.

### Mixed Types

For truly dynamic data, use `mixed`:

```php
final class Config
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value, // Can be any type
    ) {}
}
```

**Note:** With `mixed`, type validation is skipped. Consider using `#[Trusted]` attribute for better performance.

## Attributes Reference

Attributes allow you to customize mapping behavior for specific parameters or classes.

### `#[Name('fieldName')]`

Maps an array key to a different constructor parameter name. Useful when API field names differ from your property names.

```php
use Guennichi\Mapper\Attribute\Name;

final class Product
{
    public function __construct(
        #[Name('productName')] // Array key 'productName' maps to $name
        public readonly string $name,
        #[Name('productPrice')]
        public readonly float $price,
    ) {}
}

$data = [
    'productName' => 'Laptop', // Maps to $name
    'productPrice' => 999.99,  // Maps to $price
];

$product = $mapper($data, Product::class);
```

### `#[Flexible]`

Allows flexible type coercion, useful for handling loosely typed input data (like API responses that send strings instead of booleans).

```php
use Guennichi\Mapper\Attribute\Flexible;

final class Settings
{
    public function __construct(
        #[Flexible]
        public readonly bool $enabled, // 'yes', 'no', '1', '0', true, false all work
        #[Flexible]
        public readonly bool $active,
    ) {}
}

$data = [
    'enabled' => 'yes', // Converted to true
    'active' => 'no',   // Converted to false
];

$settings = $mapper($data, Settings::class);
```

**Supported conversions:**
- String `'yes'`, `'1'`, `'true'` → `true`
- String `'no'`, `'0'`, `'false'` → `false`
- Numeric strings → numbers (when target is int/float)

### `#[DateTimeFormat('format')]`

Specifies a custom DateTime format for parsing date strings. Default is `DATE_ATOM` (ISO 8601).

```php
use Guennichi\Mapper\Attribute\DateTimeFormat;

final class Event
{
    public function __construct(
        #[DateTimeFormat('Y-m-d')] // Custom format: 2023-12-01
        public readonly \DateTimeInterface $date,
        
        #[DateTimeFormat('m-Y H:i')] // Another format: 12-2023 14:30
        public readonly \DateTimeImmutable $timestamp,
        
        // No attribute = uses DATE_ATOM format
        public readonly \DateTime $isoDate,
    ) {}
}

$data = [
    'date' => '2023-12-01',
    'timestamp' => '12-2023 14:30',
    'isoDate' => '2023-12-01T14:30:00+00:00',
];

$event = $mapper($data, Event::class);
```

**Common formats:**
- `'Y-m-d'` - 2023-12-01
- `'Y-m-d H:i:s'` - 2023-12-01 14:30:00
- `'m-Y H:i'` - 12-2023 14:30
- `DATE_ATOM` - ISO 8601 (default)

### `#[Trusted]`

Skips type validation for performance optimization. Use only when you're certain the input data is correctly typed.

```php
use Guennichi\Mapper\Attribute\Trusted;

// Class-level: All parameters are trusted
#[Trusted]
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly array $tags, // No validation for array contents
    ) {}
}

// Parameter-level: Only this parameter is trusted
final class User
{
    public function __construct(
        public readonly string $name,
        #[Trusted]
        public readonly array $metadata, // This array is trusted, others are validated
    ) {}
}
```

**⚠️ Warning:** Only use `#[Trusted]` when:
- You control the data source completely
- The data has already been validated elsewhere
- Performance is critical and you've benchmarked the difference

**Performance impact:** Trusted parameters skip type resolution for scalar arrays, providing a small performance boost.

## Performance Optimization

### Use File Cache in Production

Always use `PhpCacheFileConstructorRepository` in production:

```php
$repository = new PhpCacheFileConstructorRepository('/var/cache/mapper');
$mapper = new Mapper(/* ... */);
```

The cache file is automatically generated and includes OPcache invalidation for optimal performance.

### Use `#[Trusted]` Selectively

For high-traffic endpoints with trusted data sources, use `#[Trusted]` on classes or parameters:

```php
#[Trusted]
final class ApiResponse
{
    public function __construct(
        public readonly array $data, // No validation = faster
    ) {}
}
```

### Cache the Mapper Instance

The mapper is stateless and can be safely cached/reused:

```php
// In your DI container or service locator
$mapper = new Mapper(/* ... */);
// Reuse the same instance for all mappings
```

### Benchmark Results

The library is optimized for performance:

- **~7x faster** than Symfony Serializer
- **~2x slower** than `unserialize()` but provides type safety and validation
- Memory usage is comparable to `unserialize()`

See the `benchmark/` directory for detailed benchmarks.

## Error Handling

### InvalidTypeException

Thrown when the input data doesn't match the expected type:

```php
use Guennichi\Mapper\Exception\InvalidTypeException;

try {
    $data = ['name' => 123]; // Should be string
    $user = $mapper($data, User::class);
} catch (InvalidTypeException $e) {
    echo $e->getMessage();
    // "Expected value of type "string", "int" given: "123""
}
```

### Common Error Scenarios

**Missing required parameter:**
```php
// User requires 'name' and 'email'
$data = ['name' => 'John']; // Missing 'email'
$user = $mapper($data, User::class); // May fail depending on implementation
```

**Wrong type:**
```php
$data = ['age' => 'thirty']; // Should be int
$user = $mapper($data, User::class); // InvalidTypeException
```

**Invalid enum value:**
```php
$data = ['status' => 'invalid']; // Not a valid enum case
$task = $mapper($data, Task::class); // InvalidTypeException
```

**Invalid DateTime format:**
```php
$data = ['date' => 'not-a-date'];
$event = $mapper($data, Event::class); // InvalidTypeException
```

### Debugging Tips

1. **Check the exception message** - It tells you what type was expected and what was given
2. **Verify your PHPDoc annotations** - Incorrect type hints can cause issues
3. **Check array keys** - Ensure they match parameter names or use `#[Name]` attribute
4. **Validate nested structures** - Errors in nested objects show the full path

## Best Practices

### 1. Use Readonly Properties

Always use `readonly` properties for immutability:

```php
final class Product
{
    public function __construct(
        public readonly string $name, // ✅ Good
        public readonly float $price,
    ) {}
}
```

### 2. Add PHPDoc Annotations

PHPDoc annotations help with type inference, especially for arrays:

```php
final class Product
{
    /**
     * @param array<string> $tags
     * @param array<Image> $images
     */
    public function __construct(
        public readonly array $tags,
        public readonly array $images,
    ) {}
}
```

### 3. Use Final Classes

Prefer `final` classes to prevent inheritance issues:

```php
final class User // ✅ Good
{
    // ...
}
```

### 4. Collection Structure

Always follow the collection pattern:

```php
/**
 * @extends Collection<ItemType>
 */
final class ItemCollection extends Collection
{
    public function __construct(ItemType ...$items) // Variadic
    {
        parent::__construct($items);
    }
}
```

### 5. Use Attributes Appropriately

- Use `#[Name]` when API field names differ from your property names
- Use `#[Flexible]` for loosely typed API responses
- Use `#[DateTimeFormat]` for custom date formats
- Use `#[Trusted]` sparingly and only with trusted data sources

### 6. Handle Errors Gracefully

Always wrap mapping in try-catch blocks:

```php
try {
    $user = $mapper($apiData, User::class);
} catch (InvalidTypeException $e) {
    // Log error and handle gracefully
    logger()->error('Failed to map user data', ['error' => $e->getMessage()]);
    throw new ApiException('Invalid user data');
}
```

### 7. Validate API Responses

Before mapping, validate that the API response structure matches expectations:

```php
if (!isset($apiResponse['name']) || !isset($apiResponse['email'])) {
    throw new InvalidApiResponseException('Missing required fields');
}

$user = $mapper($apiResponse, User::class);
```

## Troubleshooting

### Problem: "Expected value of type X, Y given"

**Cause:** The input data type doesn't match the expected type.

**Solution:**
- Check the data you're passing
- Verify PHPDoc annotations are correct
- Use `#[Flexible]` if you need type coercion

### Problem: Collection not recognized

**Cause:** The collection class doesn't meet the requirements.

**Solution:**
- Ensure it extends a class implementing `\Traversable`
- Ensure the constructor is variadic (`...$items`)
- Add `@extends Collection<ItemType>` PHPDoc

### Problem: Nested objects not mapping

**Cause:** Missing or incorrect array structure.

**Solution:**
- Verify the nested array structure matches the object structure
- Check that all required nested object properties are present
- Use `#[Name]` if array keys don't match parameter names

### Problem: DateTime parsing fails

**Cause:** Date format doesn't match the expected format.

**Solution:**
- Use `#[DateTimeFormat]` attribute with the correct format
- Verify the date string format matches
- Check timezone handling if needed

### Problem: Performance issues

**Cause:** Not using caching or too much validation.

**Solution:**
- Use `PhpCacheFileConstructorRepository` in production
- Use `#[Trusted]` on trusted data sources
- Cache the mapper instance
- Profile your code to find bottlenecks

### Problem: Optional parameters not working

**Cause:** Missing default values or incorrect handling.

**Solution:**
- Ensure optional parameters have default values
- Check that you're not passing `null` explicitly when you want to use defaults
- Verify parameter order matches array keys

## Examples

### Complete API Response Mapping Example

```php
use Guennichi\Mapper\Attribute\DateTimeFormat;
use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Attribute\Name;

// API Response Structure
final class ApiUserResponse
{
    /**
     * @param array<ApiPost> $posts
     */
    public function __construct(
        #[Name('user_id')]
        public readonly int $id,
        #[Name('user_name')]
        public readonly string $name,
        public readonly ?string $email,
        #[Flexible]
        public readonly bool $isActive,
        #[DateTimeFormat('Y-m-d\TH:i:s\Z')]
        public readonly \DateTimeImmutable $createdAt,
        public readonly ApiPostList $posts,
    ) {}
}

final class ApiPost
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        #[DateTimeFormat('Y-m-d')]
        public readonly \DateTimeInterface $publishedAt,
    ) {}
}

/**
 * @extends Collection<ApiPost>
 */
final class ApiPostList extends Collection
{
    public function __construct(ApiPost ...$posts)
    {
        parent::__construct($posts);
    }
}

// Usage
$apiResponse = [
    'user_id' => 123,
    'user_name' => 'John Doe',
    'email' => 'john@example.com',
    'isActive' => 'yes', // Flexible converts to true
    'createdAt' => '2023-01-15T10:30:00Z',
    'posts' => [
        [
            'id' => 1,
            'title' => 'First Post',
            'content' => 'Content here',
            'publishedAt' => '2023-01-10',
        ],
        [
            'id' => 2,
            'title' => 'Second Post',
            'content' => 'More content',
            'publishedAt' => '2023-01-12',
        ],
    ],
];

$user = $mapper($apiResponse, ApiUserResponse::class);
// Now you have a fully typed, immutable object!
```

This example demonstrates:
- Custom field name mapping (`#[Name]`)
- Flexible type coercion (`#[Flexible]`)
- Custom DateTime formats (`#[DateTimeFormat]`)
- Nested objects
- Collections
- Nullable types

---

For more information, see the [README.md](../README.md) or check the test fixtures in `tests/Fixture/` for additional examples.
