<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Additions\Models\Concerns\HasSlug;

uses(RefreshDatabase::class);

function createTable(string $tableName, string $slugKeyName = 'slug', string $slugSourceName = 'name')
{
    Schema::create($tableName, function (Blueprint $table) use ($slugKeyName, $slugSourceName) {
        $table->id();
        $table->string($slugSourceName)->nullable();
        $table->string($slugKeyName)->nullable();
        $table->timestamps();
    });
}

function createDataModel($withDatabase = true)
{
    $model = new class extends Model
    {
        use HasSlug;

        protected $table = 'fake_data_table';

        protected $fillable = ['name'];
    };

    if ($withDatabase) {
        createTable($model->getTable());
    }

    return $model;
}

test('findBySlug', function () {

    $model = createDataModel();

    $posts = $model->query()->create(['name' => 'Fake Name']);

    $found = $model->findBySlug('fake-name');

    expect($found)->not->toBeNull()->and($found->is($posts))->toBeTrue();
});

test('overrides - getSlugKeyName, getSlugSourceName, getSlugSource', function () {

    $model = new class extends Model
    {
        use HasSlug;

        public function getSlugKeyName()
        {
            return 'unique-slug';
        }

        public function getSlugSourceName()
        {
            return 'title';
        }

        public function getSlugSource()
        {
            return 'fake-slug';
        }
    };

    expect($model->getSlugKeyName())->toBe('unique-slug');
    expect($model->getSlugSourceName())->toBe('title');
    expect($model->getSlugSource())->toBe('fake-slug');
});

test('generateUniqueSlug', function () {

    $model = createDataModel();
    $name = 'Fake Name';

    expect($model->query()->create(['name' => $name])->slug)->toBe('fake-name');
    expect($model->query()->create(['name' => $name])->slug)->not->toBe('fake-name');
    expect($model->query()->create(['name' => $name])->slug)->not->toBe('fake-name');
});

test('generateUniqueSlug overrides', function () {

    $model = new class extends Model
    {
        use HasSlug;

        protected $table = 'HasSlug_generateUniqueSlug_overrides';

        protected $fillable = ['name'];

        public function generateUniqueSlug(): string
        {
            $slug = Str::slug((string) $this->getSlugSource());

            return $slug;
        }
    };

    createTable($model->getTable());

    $name = 'Fake Name';

    expect($model->query()->create(['name' => $name])->slug)->toBe('fake-name');
    expect($model->query()->create(['name' => $name])->slug)->toBe('fake-name');
    expect($model->query()->create(['name' => $name])->slug)->toBe('fake-name');
});

test('boot - generates a new slug', function () {

    $model = createDataModel();

    $model->create(['name' => 'My Test Name']);

    expect($model::query()->where('slug', 'my-test-name')->exists())->toBeTrue();
});

test('boot - generates a unique slug', function () {

    $model = createDataModel();

    $model->query()->create(['name' => 'Duplicate']);

    $data = $model->query()->create(['name' => 'Duplicate']);

    expect($data->slug)->toStartWith('duplicate')->not->toBe('duplicate');
});

test('boot - should regenerate slug on update', function () {

    $model = createDataModel();

    $post = $model->query()->create(['name' => 'Original Name']);

    $originalSlug = $post->slug;

    $post->update(['name' => 'Updated Name']);

    expect($post->slug)->not->toBe($originalSlug)->toBe('updated-name');
});
test('boot -  should not regenerate slug on update', function () {

    $model = new class extends Model
    {
        use HasSlug;

        protected $table = 'posts';

        protected $fillable = ['name'];

        protected function shouldRegenerateSlug(): bool
        {
            return false;
        }
    };

    createTable($model->getTable());

    $post = $model->query()->create(['name' => 'Original Name']);

    $originalSlug = $post->slug;

    $post->update(['name' => 'Updated Name']);

    $post->refresh();

    expect($post->slug)->toBe($originalSlug)->not->toBe('updated-name');
});
