<?php

use Faker\Factory as FakerFactory;
use ModelLogger\Test\Models\User;
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\Category;
use ModelLogger\Services\SessionService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->faker = FakerFactory::create();
    $this->user = User::query()->inRandomOrder()->first();
    $this->sessionService = app(SessionService::class);
    $this->sessionService->setUser($this->user)->setHash(Str::uuid());
});

test('create product', function () {

    $name = $this->faker->name;

    $product = Product::query()->create([
        'name' => $name,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->first();

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('create');
    expect($element['changes']['name']['old'])->toBe(null);
    expect($element['changes']['name']['new'])->toBe($name);
});

test('update product', function () {
    $product = Product::query()->inRandomOrder()->first();

    $oldName = $product->name;
    $newName = $this->faker->name;

    $product->update([
        'name' => $newName,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->first();

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('update');
    expect($element['changes']['name']['old'])->toBe($oldName);
    expect($element['changes']['name']['new'])->toBe($newName);
});

test('delete product', function () {
    $product = Product::query()->inRandomOrder()->first();
    $oldName = $product->name;
    $product->delete();

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->first();

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('delete');
    expect($element['changes']['name']['old'])->toBe($oldName);
    expect($element['changes']['name']['new'])->toBe(null);
});

test('add category to product', function () {
    $product = Product::query()->inRandomOrder()->first();
    $category1 = Category::query()->inRandomOrder()->first();
    $category2 = Category::query()->whereNotIn('id', [$category1->id])->inRandomOrder()->first();
    $category3 = Category::query()->whereNotIn('id', [$category1->id, $category2])->inRandomOrder()->first();

    $product->categories()->sync([$category1, $category2, $category3]);
    $product->categories()->detach([$category3]);

    $items = collect(modelLog()->get()->first()->get('items'));

    // dump($items);
    // dump(modelLog()->get());
});
