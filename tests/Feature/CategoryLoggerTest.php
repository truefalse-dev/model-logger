<?php

use ModelLogger\Observer;
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
    expect(existsElement($items->get('Category'), null, $name, Observer::CREATE, 'Name'))->toBeTrue();
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

    expect(existsElement($items->get('Category'), $oldName, $newName, Observer::UPDATE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Category'), $oldStatus ? 'On' : 'Off', $newStatus ? 'On' : 'Off', Observer::UPDATE, 'Status'))->toBeTrue();
});

test('delete category', function () {
    $category = Category::find(10);

    $oldName = $category->name;
    $oldStatus = $category->status;

    $category->delete();

    $items = collect(modelLog()->get()->first()->get('items'));

    expect(existsElement($items->get('Category'), $oldName, null, Observer::DELETE, 'Name'))->toBeTrue();
    expect(existsElement($items->get('Category'), $oldStatus ? 'On' : 'Off', null, Observer::DELETE, 'Status'))->toBeTrue();
});
