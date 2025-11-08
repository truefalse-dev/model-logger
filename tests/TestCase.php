<?php

namespace ModelLogger\Test;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ModelLogger\ServiceProvider as ModelLoggerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

// Loggers
use ModelLogger\Test\Loggers\ProductLogger;
use ModelLogger\Test\Loggers\CategoryLogger;

// Models
use ModelLogger\Test\Models\User;
use ModelLogger\Test\Models\Vendor;
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\Category;
use ModelLogger\Test\Models\Attribute;
use ModelLogger\Test\Models\AttributeValue;
use ModelLogger\Test\Models\CategoryProduct;
use ModelLogger\Test\Models\ProductAttributeValue;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected $faker;

    protected function getPackageProviders($app)
    {
        return [
            ModelLoggerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.host', 'db');
        config()->set('database.connections.mysql.database', 'test');
        config()->set('database.connections.mysql.password', 'root');

        config()->set('model-logger.database_connection', config('database.default'));
        config()->set('model-logger.table_name', 'model_logs');

        config()->set('auth.providers.users.model', User::class);
        config()->set('app.key', 'base64:'.base64_encode(
                Encrypter::generateKey(config()['app.cipher'])
            ));

        config()->set('model-logger.loggers', [
            ProductLogger::class,
            CategoryLogger::class,
        ]);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->migrateModelLogTable();

        $this->createTables('categories', 'vendors', 'products', 'users', 'attributes', 'attribute_values');
        $this->createRelationTables('category_product', 'product_attribute_value');
        $this->seedModels();
    }

    protected function migrateModelLogTable(): void
    {
        require_once dirname(__DIR__) . '/database/migrations/create_model_log_table.php.stub';

        (new \CreateActivityLogTable())->up();
    }

    private function createTables(...$tableNames): void
    {
        collect($tableNames)->each(function (string $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->increments('id');
                $table->timestamps();

                if ($tableName === 'users') {
                    $table->string('name')->nullable();
                }

                if ($tableName === 'attributes') {
                    $table->string('name')->nullable();
                    $table->string('type')->nullable();
                }

                if ($tableName === 'attribute_values') {
                    $table->integer('attribute_id')->nullable();
                    $table->string('name')->nullable();
                }

                if ($tableName === 'categories') {
                    $table->string('name')->nullable();
                    $table->string('status')->default(1);
                }

                if ($tableName === 'vendors') {
                    $table->string('name')->nullable();
                    $table->string('slug')->nullable();
                }

                if ($tableName === 'products') {
                    $table->string('name')->nullable();
                    $table->integer('vendor_id')->unsigned()->nullable();
//                    $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
//                    $table->integer('user_id')->unsigned()->nullable();
//                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->text('description')->nullable();
                    $table->decimal('price')->nullable();
                    $table->integer('quantity')->default(0);
                    $table->string('status')->default(1);
                }
            });
        });
    }

    private function createRelationTables(...$tableNames): void
    {
        collect($tableNames)->each(function (string $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                if ($tableName === 'category_product') {
                    $table->integer('product_id')->unsigned()->nullable();
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                    $table->integer('category_id')->unsigned()->nullable();
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                }

                if ($tableName === 'product_attribute_value') {
                    $table->integer('attribute_value_id')->unsigned()->nullable();
                    $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
                    $table->integer('product_id')->unsigned()->nullable();
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        });
    }

    private function seedModels(): void
    {
        collect([
            User::class,
            Product::class,
            Category::class,
            CategoryProduct::class,
            Vendor::class,
            Attribute::class,
            AttributeValue::class,
            ProductAttributeValue::class,
        ])->each(function (string $modelClass) {
            DB::table((new $modelClass)->getTable())->insert(require dirname(__DIR__) . sprintf('/database/structure/%s.php.stub', (new $modelClass)->getTable()));
        });
    }
}
