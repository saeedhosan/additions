<?php

declare(strict_types=1);

use Additions\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

// Boot Laravel + refresh database
uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->uuid()->unique()->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    });
});

test('findByUuid', function () {

    $model = new class extends Model
    {
        use HasUuid;

        protected $table = 'test_models';

        protected $fillable = ['name'];
    };

    $post = $model->query()->create(['name' => 'Cyber']);

    $found = $model->findByUuid($post->uuid);

    expect($found)->not->toBeNull()->and($found->is($post))->toBeTrue();
});

test('getUuidKeyName', function () {
    $model = new class extends Model
    {
        use HasUuid;

        public function getUuidKeyName(): string
        {
            return 'uuid-overrides';
        }
    };

    expect($model->getUuidKeyName())->toBe('uuid-overrides');
});
test('getUuidKey', function () {
    $model = new class extends Model
    {
        use HasUuid;

        protected $table = 'test_models';

        protected $fillable = ['name'];
    };

    expect($model->getUuidKey())->toBeNull();

    expect($model->query()->create(['name' => 'fake-test'])->getUuidKey())->toBeString();
});

test('newUniqueUuid', function () {
    $model = new class extends Model
    {
        use HasUuid;

        protected $table = 'test_models';

        protected $fillable = ['name'];

        public $timestamps = false;
    };

    // Create first record
    $first = $model->query()->create(['name' => 'fake-test']);

    // Create second record
    $second = $model->query()->create(['name' => 'fake-test']);

    // UUID exists
    expect($first->uuid)->not->toBeNull();
    expect($second->uuid)->not->toBeNull();

    // UUIDs are unique per record
    expect($first->uuid)->not->toBe($second->uuid);

    // Can find model by UUID
    $found = $model->findByUuid($first->uuid);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($first->id);
    expect($found->uuid)->toBe($first->uuid);
});

test('boot - generate uuid on creating event', function () {
    $model = new class extends Model
    {
        use HasUuid;

        protected $table = 'test_models';

        protected $fillable = ['name'];

        public $timestamps = false;
    };

    // Create model WITHOUT manually setting uuid
    $record = $model->query()->create([
        'name' => 'fake-test',
    ]);

    // Assert uuid was generated during creating event
    expect($record->uuid)->not->toBeNull()->toBeString()->toHaveLength(26);
});

test('boot - does not override existing uuid', function () {
    $model = new class extends Model
    {
        use HasUuid;

        protected $table = 'test_models';

        protected $fillable = ['name', 'uuid'];

        public $timestamps = false;
    };

    $record = $model->query()->create([
        'name' => 'fake-test',
        'uuid' => 'custom-uuid',
    ]);

    expect($record->uuid)->toBe('custom-uuid');
});
