<?php

use Additions\Eloquent\Concerns\HasBelongsToOne;
use Additions\Eloquent\Relations\BelongsToOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assumptions: this feature is a relation/trait only (no auth/validation surfaces to test here).
 */
class BelongsToOneBlog extends Model
{
    use HasBelongsToOne;

    protected $table = 'belongs_to_one_blogs';

    protected $guarded = [];

    public $timestamps = false;

    public function author(): BelongsToOne
    {
        return $this->belongsToOne(BelongsToOneAuthor::class, 'author_blog', 'blog_id', 'author_id');
    }

    public function authorWithDefaults(): BelongsToOne
    {
        return $this->belongsToOne(BelongsToOneAuthor::class, 'author_blog', '', '');
    }

    public function authorWithCustomKeys(): BelongsToOne
    {
        return $this->belongsToOne(
            BelongsToOneAuthor::class,
            'author_blog',
            'blog_id',
            'author_id',
            'slug',
            'uuid',
            'customRelationName'
        );
    }
}

class BelongsToOneAuthor extends Model
{
    protected $table = 'belongs_to_one_authors';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    config([
        'database.default' => 'testing',
        'database.connections.testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ],
    ]);

    Schema::dropIfExists('author_blog');
    Schema::dropIfExists('belongs_to_one_authors');
    Schema::dropIfExists('belongs_to_one_blogs');

    Schema::create('belongs_to_one_blogs', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
    });

    Schema::create('belongs_to_one_authors', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('uuid')->nullable();
    });

    Schema::create('author_blog', function (Blueprint $table) {
        $table->unsignedBigInteger('blog_id');
        $table->unsignedBigInteger('author_id');
    });
});

test('returns a single related model', function () {

    $blog = BelongsToOneBlog::query()->create(['name' => 'Blog A', 'slug' => 'blog-a']);

    $firstAuthor = BelongsToOneAuthor::query()->create(['name' => 'Author One', 'uuid' => 'a-1']);
    $secondAuthor = BelongsToOneAuthor::query()->create(['name' => 'Author Two', 'uuid' => 'a-2']);

    $blog->author()->attach([$firstAuthor->id, $secondAuthor->id]);

    $related = $blog->author;

    expect($related)->toBeInstanceOf(BelongsToOneAuthor::class);
    expect([$firstAuthor->id, $secondAuthor->id])->toContain($related->id);
});

test('returns null when no related model exists', function () {
    $blog = BelongsToOneBlog::query()->create(['name' => 'Blog A', 'slug' => 'blog-a']);

    expect($blog->author)->toBeNull();
});

test('eager loads a single related model per parent', function () {
    $blogA = BelongsToOneBlog::query()->create(['name' => 'Blog A', 'slug' => 'blog-a']);
    $blogB = BelongsToOneBlog::query()->create(['name' => 'Blog B', 'slug' => 'blog-b']);

    $authorOne = BelongsToOneAuthor::query()->create(['name' => 'Author One', 'uuid' => 'a-1']);
    $authorTwo = BelongsToOneAuthor::query()->create(['name' => 'Author Two', 'uuid' => 'a-2']);
    $authorThree = BelongsToOneAuthor::query()->create(['name' => 'Author Three', 'uuid' => 'a-3']);

    $blogA->author()->attach([$authorOne->id, $authorTwo->id]);
    $blogB->author()->attach([$authorThree->id]);

    $blogs = BelongsToOneBlog::query()->with('author')->orderBy('id')->get();

    expect($blogs)->toHaveCount(2);
    expect($blogs->first()->author)->toBeInstanceOf(BelongsToOneAuthor::class);
    expect($blogs->last()->author)->toBeInstanceOf(BelongsToOneAuthor::class);
    expect($blogs->first()->author->id)->toBe($authorOne->id);
    expect($blogs->last()->author->id)->toBe($authorThree->id);
});

test('initializes missing relations as null during eager loading', function () {
    $blogWithAuthor = BelongsToOneBlog::query()->create(['name' => 'Blog A', 'slug' => 'blog-a']);
    $blogWithoutAuthor = BelongsToOneBlog::query()->create(['name' => 'Blog B', 'slug' => 'blog-b']);

    $author = BelongsToOneAuthor::query()->create(['name' => 'Author One', 'uuid' => 'a-1']);
    $blogWithAuthor->author()->attach([$author->id]);

    $blogs = BelongsToOneBlog::query()->with('author')->orderBy('id')->get();

    expect($blogs->first()->author)->toBeInstanceOf(BelongsToOneAuthor::class);
    expect($blogs->last()->author)->toBeNull();
    expect($blogs->last()->getRelation('author'))->toBeNull();
});

test('uses default pivot and key names when empty strings are provided', function () {
    $blog = new BelongsToOneBlog;

    $relation = $blog->authorWithDefaults();

    expect($relation->getForeignPivotKeyName())->toBe($blog->getForeignKey());
    expect($relation->getRelatedPivotKeyName())->toBe((new BelongsToOneAuthor)->getForeignKey());
    expect($relation->getParentKeyName())->toBe($blog->getKeyName());
    expect($relation->getRelatedKeyName())->toBe((new BelongsToOneAuthor)->getKeyName());
});

test('uses provided parent and related keys when given', function () {
    $blog = new BelongsToOneBlog;

    $relation = $blog->authorWithCustomKeys();

    expect($relation->getParentKeyName())->toBe('slug');
    expect($relation->getRelatedKeyName())->toBe('uuid');
});
