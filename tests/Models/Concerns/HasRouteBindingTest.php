<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Additions\Models\Concerns\HasRouteBinding;
use Additions\Models\Concerns\RouteKeyBuilder;

test('HasRouteBinding is applied', function () {
    Schema::create('route_key_models', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->string('name');
    });

    $model = new class extends Model
    {
        use HasRouteBinding;

        protected $table = 'route_key_models';

        protected $fillable = ['name', 'slug'];

        public $timestamps = false;
    };

    $builder = $model->query();

    expect($builder)->toBeInstanceOf(RouteKeyBuilder::class);
});

test('find() uses the route key, instead of the primary key', function () {
    Schema::create('route_key_models', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->string('name');
    });

    $model = new class extends Model
    {
        use HasRouteBinding;

        protected $table = 'route_key_models';

        protected $fillable = ['name', 'slug'];

        public $timestamps = false;

        public function getRouteKeyName(): string
        {
            return 'slug';
        }
    };

    $record = $model->query()->create([
        'name' => 'Test Model',
        'slug' => 'test-model',
    ]);

    $found = $model->query()->find('test-model');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($record->id);
});

test('find() ignores with the primary key', function () {
    Schema::create('route_key_models', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->string('name');
    });

    $model = new class extends Model
    {
        use HasRouteBinding;

        protected $table = 'route_key_models';

        protected $fillable = ['name', 'slug'];

        public $timestamps = false;

        public function getRouteKeyName(): string
        {
            return 'slug';
        }
    };

    $record = $model->query()->create([
        'name' => 'Test Model',
        'slug' => 'test-model',
    ]);

    expect(
        $model->query()->find($record->id)
    )->toBeNull();
});
