<?php

namespace ModelLogger\Test;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use ModelLogger\Test\Models\User;
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\Category;
use ModelLogger\Test\Loggers\ProductLogger;
use ModelLogger\Test\Loggers\CategoryLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ModelLogger\ServiceProvider as ModelLoggerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

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
            //CategoryLogger::class,
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

        $this->createTables('categories', 'products', 'users');
        $this->createRelationTables('category_product');
        $this->seedModels(User::class, Category::class, Product::class);
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

                if ($tableName === 'products') {
                    $table->string('name')->nullable();
//                    $table->integer('user_id')->unsigned()->nullable();
//                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->text('description')->nullable();
                    $table->string('interval')->nullable();
                    $table->decimal('price')->nullable();
                    $table->string('status')->nullable();
                }

                if ($tableName === 'categories') {
                    $table->string('name')->nullable();
                    $table->text('description')->nullable();
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
            });
        });
    }

    private function seedModels(...$modelClasses): void
    {
        collect($modelClasses)->each(function (string $modelClass) {
            for ($i = 0; $i < rand(3, 12); $i++) {
                $modelClass::query()->insert([
                    'name' => $this->faker->name,
                ]);
            }
        });
    }
}
