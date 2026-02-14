<?php

use Additions\Models\Concerns\HasStaticAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Fake Model (Package Scoped)
|--------------------------------------------------------------------------
*/
class FakeModelStatic extends Model
{
    use HasStaticAccess;

    protected $fillable = ['name', 'email'];

    public $timestamps = false;
}

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
beforeEach(function () {
    Schema::create(FakeModelStatic::tableName(), function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists(FakeModelStatic::tableName());
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/
it('creates a static model instance', function () {

    $instance = FakeModelStatic::instance();

    expect($instance)->toBeInstanceOf(FakeModelStatic::class);

});

it('returns the table name statically', function () {

    expect(FakeModelStatic::tableName())->toBe((new FakeModelStatic)->getTable());

});

it('returns the route key name statically', function () {
    expect(FakeModelStatic::routeKeyName())
        ->toBe('id');
});

it('returns fillable fields statically', function () {
    expect(FakeModelStatic::fields())
        ->toBe((new FakeModelStatic)->getFillable())
        ->toBeArray()
        ->not->toBeEmpty();
});

it('finds a model by route key statically', function () {
    $model = FakeModelStatic::create([
        'name' => 'Test',
        'email' => 'test@example.com',
    ]);

    $found = FakeModelStatic::findByKey($model->getRouteKey());

    expect($found)
        ->not->toBeNull()
        ->and($found->is($model))->toBeTrue();
});
