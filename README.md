<p align="center">
    <a href="https://github.com/saeedhosan/Additions/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/saeedhosan/Additions/tests.yml?branch=main&label=tests&style=round-square"></a>
    <a href="https://packagist.org/packages/saeedhosan/Additions"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/saeedhosan/Additions?1"></a>
    <a href="https://packagist.org/packages/saeedhosan/Additions"><img alt="Latest Version" src="https://img.shields.io/packagist/v/saeedhosan/Additions"></a>
    <a href="https://packagist.org/packages/saeedhosan/Additions"><img alt="License" src="https://img.shields.io/github/license/saeedhosan/Additions?1"></a>
</p>

# Laravel Additions

The Additions package provides Laravel Eloquent, supports, traits, and additional classes.

# Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Model Concerns](#model-concerns)
    - [HasSlug Trait](#hasslug-trait)
    - [HasUuid Trait](#hasuuid-trait)
    - [HasRouteBinding](#HasRouteBinding)
    - [HasStaticAccess](#hasstaticaccess)
- [Support](#support)
    - [Traits](#support-traits)
    - [Path](#support-path)
    - [Json](#support-json)
    - [EnvEditor](#envEditor)
- [Eloquent Relations](#eloquent-relations)
    - [BelongsToOne](#belongsToOne)

<!-- -   [Global Helpers](#model-concerns) -->

## Introduction

This package provide collection of reusable php classes to improve everyday Laravel development.

It provides **Eloquent model concerns**, **support utilities**, and **lightweight traits** that solve common problems—cleanly, safely, and in a Laravel-native way.

Each snippet is intentionally minimal, well-tested, and easy to drop into real-world projects.

## Installation

You can install the package via composer:

```bash
composer require saeedhosan/Additions
```

## Model Concerns

### HasSlug Trait

The `HasSlug` trait adds automatic, unique slug generation to Eloquent models.

It generates slugs from a source attribute, ensures uniqueness at the database level, and keeps slugs in sync when the source value changes.

#### Usage the HasSlug trait

Apply the trait to any Eloquent model:

```php
use Additions\Models\Concerns\HasSlug;

class Post extends Model
{
    use HasSlug;
}
```

> **Note** By default, the slug will be generated from the name attribute and stored in the slug column.

When a model is created, a slug is automatically generated from the name attribute and stored in the slug column.

**Slug Generation & Uniqueness**

```php
Post::create(['name' => 'Fake Name'])->slug;     // fake-name
Post::create(['name' => 'Fake Name'])->slug;     // fake-name-1
Post::create(['name' => 'Fake Name'])->slug;     // fake-name-2
```

#### Customizable Slug keys and methods

```php
class Post extends Model
{
    use HasSlug;

    /**
     * Get the slug key name.
     */
    public function getSlugKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the source field for generating slugs.
     */
    public function getSlugSourceName(): string
    {
        return 'title';
    }

    /**
     * Generate a new unique slug for the model.
     */
    public function generateUniqueSlug(): string
    {
        // generate a unique slug
    }

    /**
     * Determine if the slug should be regenerated on update.
     */
    protected function shouldRegenerateSlug(): bool
    {
        return true;
    }
}
```

#### Finding a Model by Slug

```php
$post = Post::findBySlug('fake-name');
```

Returns the first matching model or null if no record exists.

#### Automatic Model Events

The trait hooks into Eloquent lifecycle events.
**On Create**
A slug is generated if the slug column is empty.

```php
Post::create(['name' => 'My Test Name']);
// slug: my-test-name

// Get unique when slug by number for existing record
Post::create(['name' => 'My Test Name']);
// slug: my-test-name-1
```

**On Update**
The slug is regenerated when the source attribute changes.

```php
$post = Post::create(['name' => 'Original Name']);

$post->update(['name' => 'Updated Name']);
// slug: updated-name
```

### HasUuid Trait

The `HasUuid` trait adds automatic UUID generation and lookup capabilities to Eloquent models.

It ensures every model receives a unique UUID on creation while allowing full control over column naming and behavior.

### Usage the HasUuid trait

Apply the trait to any Eloquent model:

```php
use Additions\Models\Concerns\HasUuid;

class Post extends Model
{
    use HasUuid;

    protected $fillable = ['name'];
}
```

When a model is created, a UUID is automatically generated and stored in the uuid column.

#### Finding a Model by UUID

```php
$post = Post::findByUuid($uuid);
```

Returns the matching model instance or null if no record is found.

#### Automatic UUID Generation

UUIDs are generated during the creating model event.

```php
$post = Post::create(['name' => 'Cyber']);

$post->uuid; // string (26 characters) by default
```

- UUIDs are unique per record
- Existing UUID values are never overridden

#### Accessing the UUID Value

```php
$post->getUuidKey();
```

Returns the UUID value for the model, or null if it has not been generated yet.

#### Customizing the UUID Column

You may override the UUID column name by redefining getUuidKeyName():

```php
class Post extends Model
{
    use HasUuid;

    public function getUuidKeyName(): string
    {
        return 'public_id';
    }
}
```

This allows you to store UUIDs in a custom column while keeping all behavior intact.

#### Manual UUID Assignment

If a UUID is manually provided, the trait will respect it:

```php
Post::create([
    'name' => 'Custom',
    'uuid' => 'custom-uuid',
]);
```

The `HasUuid` trait will not overwrite the existing values.

#### Database Considerations

For best results add a unique index on the UUID column

```php
$table->uuid('uuid')->unique();
```

### HasRouteBinding

This feature makes Eloquent’s `find()` method resolve models using the **route key** instead of the primary key.

It is useful when your models are identified by a slug, UUID, or any custom route key.

Apply the `HasRouteBinding` trait to your model:

```php
use Additions\Models\Concerns\HasRouteBinding;

class Post extends Model
{
    use HasRouteBinding;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

With the trait applied:

```php
Post::find('my-post-slug');
```

Is equivalent to:

```php
Post::where('slug', 'my-post-slug')->first();
```

Instead of querying by the primary key.

**How It Works**

- The model swaps Laravel’s default Eloquent builder
- A custom builder (RouteKeyBuilder) overrides find()
- find() now queries using getRouteKeyName()
- No other query behavior is affected.

**Example with UUIDs**

```php
class Order extends Model
{
    use HasRouteBinding;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

Order::find('01HFYQ3P2YF4K8J9Q6Z8M2X7A1');
```

### HasStaticAccess

The `HasStaticAccess` trait provides a small set of static helpers for Eloquent models, allowing you to access common model metadata and queries without manually instantiating the model.

#### Why This Trait Exists

This trait offers a clean, explicit way to do that while staying aligned with Laravel’s conventions.

#### Usage the HasStaticAccess trait class

Attach the trait to any Eloquent model:

```php
use Additions\Models\Concerns\HasStaticAccess;

class User extends Model
{
    use HasStaticAccess;
}
```

#### Available Static Access

```php
User::tableName();       // Returns the table name
User::routeKeyName();    // Returns the route key name
User::fields();          // Returns fillable attributes
User::findByKey($key);   // Find model by route key
User::findByRouteKey($key); // Alias of findByKey
```

#### Benefits

- Static access without instantiate.
- Improved readability – Clear intent in routing, and helpers.
- Zero side effects – Uses fresh model instances internally.

## Support

### Support Traits

#### CreateInstance Trait

The `CreateInstance` trait provides convenient, expressive ways to create class instances using either the Laravel service container or a lightweight static cache.

It is designed for **service-style classes** where controlled instantiation improves clarity and reuse.

Attach the trait to any class:

```php
use Additions\Support\Traits\CreateInstance;

class ReportGenerator
{
    use CreateInstance;

    public function __construct(private string $name) {}

    public function getName(){
        return $this->name;
    }
}
```

**Creating Instances via the Container**

Use `make()` to resolve the class through Laravel’s service container.

```php
$report = ReportGenerator::make('Laravel is')->getName(); // Laravel is
```

**Creating Cached Static Instances**

Use `init()` to create and reuse instances based on constructor arguments.

```php
$first  = ReportGenerator::init('PHP is');
$first->getName(); // PHP is

$second = ReportGenerator::init('PHP is');

$first === $second; // true
```

- Instances are cached per argument signature
- Subsequent calls with the same arguments return the same instance
- Ideal for stateless, reusable objects

#### PreventInstance Trait

The `PreventInstance` trait ensures a class cannot be instantiated.

It is intended for static-only utility classes where object creation would be a design error.

Apply the trait to a class meant for static usage only:

```php
use Additions\Support\Traits\PreventInstance;

class StringHelpers
{
    use PreventInstance;

    public static function upper(string $value): string
    {
        return strtoupper($value);
    }
}
```

Attempting to instantiate the class will throw a LogicException:

```php
new StringHelpers();
// LogicException: StringHelpers cannot be instantiated.
```

The error message clearly communicates the intended usage.

### Support Path

The `Path` class provides simple, cross-platform utilities for working with file system paths.

It focuses on **normalization**, **joining**, and **safe path inspection**, without side effects.

#### Joining Paths

Join multiple path segments into a clean, normalized path:

```php
use Additions\Support\Path;

Path::join('storage', 'app', 'files');
// storage/app/files
```

Handles duplicate slashes and mixed separators automatically.

#### Normalizing Paths

Normalize a path by: Converting `\` to `/` Removing `./` and removing duplicate slashes

```php
Path::normalize('storage\\app//./files');
// storage/app/files
```

#### Current File Directory

Get the directory of the file where Path::current() is called:

```php
Path::current('config', 'files');
// /current/file/dir/config/files
```

Useful for resolving paths relative to the calling file.

#### Resolving Real Paths

Resolve a path to its absolute form (if it exists):

```php
Path::real('./storage/app');
// /full/path/to/storage/app
```

Returns null if the path does not exist.

#### Replacing Path Segments

Replace the first occurrence:

```php
Path::replaceFirst('storage', 'public', 'storage/app/file.txt');
// public/app/file.txt
```

Replace all occurrences:

```php
Path::replace('/', '-', 'storage/app/file.txt');
// storage-app-file.txt
```

#### Path Information

Get common path parts:

```php
Path::dirname('/var/www/index.php');
// /var/www

Path::basename('/var/www/index.php');
// index.php

Path::filename('/var/www/index.php');
// index

Path::extension('/var/www/index.php');
// php
```

### Absolute Path Detection

Check if a path is absolute (Linux or Windows):

```php
Path::isAbsolute('/var/www');     // true
Path::isAbsolute('C:\\Windows'); // true
Path::isAbsolute('storage/app'); // false
```

### Support Json

The `Json` class provides a simple, safe way to **check** and **decode** JSON values without throwing errors.

It is designed for defensive code where input may be invalid, empty, or unknown.

**Checking if a Value Is JSON**

Use `Json::is()` to determine whether a value is a **valid JSON string**.

```php
use Additions\Support\Json;

Json::is('{"name":"Saeed"}'); // true
Json::is('[1,2,3]');          // true
Json::is('"string"');         // true
Json::is('null');             // true

Json::is('{invalid-json}');   // false
Json::is('');                 // false
Json::is(123);                // false
Json::is(null);               // false
```

Only valid JSON strings return true.

**Decoding JSON Safely**

Use Json::decode() to decode JSON into an array without exceptions.

```php
Json::decode('{"name":"Saeed"}');
// ['name' => 'Saeed']

Json::decode('[1,2,3]');
// [1, 2, 3]
```

**Default Fallback Value**

You may provide a default value when decoding fails:

```php
$default = ['default' => true];

Json::decode(null, $default);            // ['default' => true]
Json::decode('{invalid-json}', $default); // ['default' => true]
Json::decode('"string"', $default);       // ['default' => true]
Json::decode('123', $default);             // ['default' => true]
```

**Summary**

- `Json::is()` checks if a value is valid JSON
- `Json::decode()` safely decodes JSON into an array
- No exceptions, no warnings
- Ideal for handling user input, config values, or external data

## Eloquent relations

### BelongsToOne

The `BelongsToOne` relation provides a one-to-one relationship through a pivot table.
It behaves like `belongsToMany`, but returns a single related model.

**Defining the Relationship**

Use the `HasBelongsToOne` trait and define the relation with the pivot table and keys:

```php
use Illuminate\Database\Eloquent\Model;
use Additions\Eloquent\Concerns\HasBelongsToOne;
use Additions\Eloquent\Relations\BelongsToOne;

class Blog extends Model
{
    use HasBelongsToOne;

    public function author(): BelongsToOne
    {
        return $this->belongsToOne(Author::class, 'author_blog', 'blog_id', 'author_id');
    }
}
```

Accessing the relation returns a single model (or `null`):

```php
$author = $blog->author;
```

Attach and update the relationship through the pivot table:

```php
$blog->author()->attach($authorId);
$blog->author()->sync([$authorId]);
```

**Eager Loading**

Eager load it like any other relation:

```php
$blogs = Blog::query()->with('author')->get();
```

### EnvEditor

The `EnvEditor` support class provides a simple way to modify laravel environment variables.

It handles quoting, escaping, and ensures your environment configuration stays consistent.

```php
use Additions\Support\EnvEditor;

EnvEditor::addKey('APP_NAME', 'My Application');
// APP_NAME="My Application"

EnvEditor::editKey('APP_DEBUG', 'true');

EnvEditor::setKey('APP_URL', 'https://example.com');

EnvEditor::keyExists('APP_DEBUG'); // bool
```

The `put()` method works like `setKey()` and before check with `keyExists`:

```php
EnvEditor::put('DB_HOST', 'localhost'); // add new
EnvEditor::put('DB_HOST', '127.0.0.1'); // Updates existing
```
