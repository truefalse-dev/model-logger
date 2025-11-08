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

test('create product with vendor', function () {
    $name = $this->faker->name;

    $vendor = Vendor::find(10);

    $product = Product::query()->create([
        'name' => $name,
        'vendor_id' => $vendor->id,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('create');
    expect($element['changes']['name']['old'])->toBe(null);
    expect($element['changes']['name']['new'])->toBe($name);
    expect($element['changes']['vendor.name']['old'])->toBe(null);
    expect($element['changes']['vendor.name']['new'])->toBe($vendor->name);
});

test('update product, 3 fields', function () {
    $product = Product::find(100);

    $oldName = $product->name;
    $newName = 'Samsung Galaxy S23';

    $oldPrice = $product->price;
    $newPrice = 999;

    $oldQuantity = $product->quantity;
    $newQuantity = 99;

    $product->update([
        'name' => $newName,
        'price' => $newPrice,
        'quantity' => $newQuantity,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('update');

    expect(count($element['changes']))->toEqual(3);

    expect($element['changes']['name']['old'])->toBe($oldName);
    expect($element['changes']['name']['new'])->toBe($newName);

    expect($element['changes']['price']['old'])->toBe($oldPrice);
    expect($element['changes']['price']['new'])->toBe($newPrice);

    expect($element['changes']['quantity']['old'])->toBe($oldQuantity);
    expect($element['changes']['quantity']['new'])->toBe($newQuantity);
});

test('update vendor in product', function () {
    $product = Product::find(101);

    $oldVendor = $product->vendor;
    $newVendor = Vendor::find(11);

    $product->vendor()->associate($newVendor);
    $product->save();

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('update');

    expect($element['changes']['vendor.name']['old'])->toBe($oldVendor->name);
    expect($element['changes']['vendor.name']['new'])->toBe($newVendor->name);
});

test('add product to new category', function () {
    $product = Product::find(100);
    $currentCategory = Category::find(11);
    $additionalCategory = Category::find(12);

    $product->categories()->sync([
        $currentCategory,
        $additionalCategory,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $element = $items->get(0);

    expect($items->count())->toEqual(1);
    expect($element['action'])->toBe('create');
    expect($element['changes']['category.name']['old'])->toBe(null);
    expect($element['changes']['category.name']['new'])->toBe($additionalCategory->name);
});

test('change product category', function () {
    $product = Product::find(100);
    $currentCategory = Category::find(11);
    $additionalCategory = Category::find(12);

    $product->categories()->sync([
        $additionalCategory,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $firstElement = $items->get(0);
    $secondElement = $items->get(1);

    expect($items->count())->toEqual(2);
    expect($firstElement['action'])->toBe('delete');
    expect($firstElement['changes']['category.name']['old'])->toBe($currentCategory->name);
    expect($firstElement['changes']['category.name']['new'])->toBe(null);

    expect($secondElement['action'])->toBe('create');
    expect($secondElement['changes']['category.name']['old'])->toBe(null);
    expect($secondElement['changes']['category.name']['new'])->toBe($additionalCategory->name);
});

test('change product attributes', function () {
    $product = Product::find(100);

    $oldAttributeValue1 = AttributeValue::find(10000);
    $oldAttributeValue2 = AttributeValue::find(10001);
    $newAttributeValue = AttributeValue::find(10002);

    $product->attribute_values()->sync([
        $newAttributeValue,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));
    $firstElement = $items->get(0);
    $secondElement = $items->get(1);
    $thirdElement = $items->get(2);

    expect($items->count())->toEqual(3);
    expect($firstElement['action'])->toBe('delete');
    expect($firstElement['changes']['attribute_value.name']['old'])->toBe($oldAttributeValue1->name);
    expect($firstElement['changes']['attribute_value.name']['new'])->toBe(null);
    expect($firstElement['changes']['attribute_value.name']['title'])->toBe($newAttributeValue->attribute->name);

    expect($secondElement['action'])->toBe('delete');
    expect($secondElement['changes']['attribute_value.name']['old'])->toBe($oldAttributeValue2->name);
    expect($secondElement['changes']['attribute_value.name']['new'])->toBe(null);
    expect($secondElement['changes']['attribute_value.name']['title'])->toBe($newAttributeValue->attribute->name);

    expect($thirdElement['action'])->toBe('create');
    expect($thirdElement['changes']['attribute_value.name']['old'])->toBe(null);
    expect($thirdElement['changes']['attribute_value.name']['new'])->toBe($newAttributeValue->name);
    expect($thirdElement['changes']['attribute_value.name']['title'])->toBe($newAttributeValue->attribute->name);
});

test('delete product', function () {
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
