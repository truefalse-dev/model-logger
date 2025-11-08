<?php

use Faker\Factory as FakerFactory;
use ModelLogger\Services\SessionService;
use Illuminate\Support\Facades\DB;

// Models
use ModelLogger\Test\Models\User;
use ModelLogger\Test\Models\Vendor;
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\Category;
use ModelLogger\Test\Models\AttributeValue;
use ModelLogger\Test\Models\ProductAttributeValue;

beforeEach(function () {
    $user_id = 1;
    $this->faker = FakerFactory::create();
    $this->user = User::find(1);
    $this->sessionService = app(SessionService::class);
    $this->sessionService->setUser($this->user)->setHash(Str::uuid());
});

test('create category', function () {
    $name = $this->faker->name;

    $category = Category::query()->create([
        'name' => $name,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->first();

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('create');
    expect($element['changes']['name']['old'])->toBe(null);
    expect($element['changes']['name']['new'])->toBe($name);
});

test('update category', function () {
    $category = Category::find(11);

    $oldName = $category->name;
    $newName = 'TV & Audio';

    $oldStatus = $category->status;
    $newStatus = !$oldStatus;

    $category->update([
        'name' => $newName,
        'status' => $newStatus,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('update');

    expect(count($element['changes']))->toEqual(2);

    expect($element['changes']['name']['old'])->toBe($oldName);
    expect($element['changes']['name']['new'])->toBe($newName);

    expect($element['changes']['status']['old'])->toBe($oldStatus ? 'On' : 'Off');
    expect($element['changes']['status']['new'])->toBe($newStatus ? 'On' : 'Off');
});

test('delete category', function () {
    $product = Product::find(101);

    $oldName = $product->name;
    $oldPrice = $product->price;
    $oldQuantity = $product->quantity;

    $product->delete();

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('delete');

    expect($element['changes']['name']['old'])->toBe($oldName);
    expect($element['changes']['name']['new'])->toBe(null);

    expect($element['changes']['price']['old'])->toEqual($oldPrice);
    expect($element['changes']['price']['new'])->toEqual(null);

    expect($element['changes']['quantity']['old'])->toEqual($oldQuantity);
    expect($element['changes']['quantity']['new'])->toEqual(null);
});
