<?php

use Faker\Factory as FakerFactory;
use ModelLogger\Observer;
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

    expect(existsElement($items->get('Product'), null, $name, Observer::CREATE, 'Name'))->toBeTrue();
});

test('create product with vendor', function () {
    $name = $this->faker->name;
    $vendor = Vendor::find(10);

    $product = Product::query()->create([
        'name' => $name,
        'vendor_id' => $vendor->id,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Product'), null, $name, Observer::CREATE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Product'), null, $vendor->name, Observer::CREATE, 'Vendor name'))->toBeTrue();
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

    expect(existsElement($items->get('Product'), $oldName, $newName, Observer::UPDATE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Product'), $oldPrice, $newPrice, Observer::UPDATE, 'Price'))->toBeTrue();
    expect(existsElement($items->get('Product'), $oldQuantity, $newQuantity, Observer::UPDATE, 'Quantity'))->toBeTrue();
});

test('update vendor in product', function () {
    $product = Product::find(101);

    $oldVendor = $product->vendor;
    $newVendor = Vendor::find(11);

    $product->vendor()->associate($newVendor);
    $product->save();

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Product'), $oldVendor->name, $newVendor->name, Observer::UPDATE, 'Vendor name'))->toBeTrue();
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

    expect(existsElement($items->get('Categories'), null, $additionalCategory->name, Observer::CREATE, 'Name'))->toBeTrue();
});

test('change product category', function () {
    $product = Product::find(100);
    $currentCategory = Category::find(11);
    $additionalCategory = Category::find(12);

    $product->categories()->sync([
        $additionalCategory,
    ]);

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Categories'), $currentCategory->name, null, Observer::DELETE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Categories'), null, $additionalCategory->name, Observer::CREATE, 'Name'))->toBeTrue();
});

test('change product attributes', function () {
    $product = Product::find(100);

    $oldAttributeValue1 = AttributeValue::find(10000);
    $oldAttributeValue2 = AttributeValue::find(10001);
    $newAttributeValue = AttributeValue::find(10002);

    $product->attribute_values()->sync([
        $newAttributeValue,
    ]);

//    $product->reviews()->create([
//        'description' => $this->faker->sentence(),
//    ]);

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Attributes'), $oldAttributeValue1->name, null, Observer::DELETE, 'Color'))->toBeTrue();
    expect(existsElement($items->get('Attributes'), $oldAttributeValue2->name, null, Observer::DELETE, 'Color'))->toBeTrue();
    expect(existsElement($items->get('Attributes'), null, $newAttributeValue->name, Observer::CREATE, 'Color'))->toBeTrue();
});

test('delete product', function () {
    $product = Product::find(101);

    $oldName = $product->name;
    $oldPrice = $product->price;
    $oldQuantity = $product->quantity;

    $product->delete();

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Product'), $oldName, null, Observer::DELETE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Product'), $oldPrice, null, Observer::DELETE, 'Price'))->toBeTrue();
    expect(existsElement($items->get('Product'), $oldQuantity, null, Observer::DELETE, 'Quantity'))->toBeTrue();
});
