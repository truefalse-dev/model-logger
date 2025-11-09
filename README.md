## Eloquent model events Logger

### Main Idea
The log is created from Eloquent model events via Observers attached both to the models themselves and to their related models through relationships.

### Installation
```shell
composer require truefalse-dev/model-logger
```
### Implementation
Create a Logger configuration file in recommended folder `Loggers`
```
project-root/
├── app/
│   ├── Models/
│   │   └── Loggers/
```
Example:
```php
use ModelLogger\Logger;
use ModelLogger\Models\Attributes\StringType;
use ModelLogger\Models\Attributes\NumberType;

class ProductLogger extends Logger
{
    protected static string $loggerName = 'Product';

    public function config(): array
    {
        return [
            Product::class => [
                self::SECTION => 'Product',
                self::ATTRIBUTES => [
                    StringType::make('name'),
                    StringType::make('vendor.name'),
                    NumberType::make('price'),
                    NumberType::make('quantity'),
                ]
            ],
            CategoryProduct::class => [
                self::PARENT => 'product',
                self::SECTION => 'Category',
                self::ATTRIBUTES => [
                    StringType::make('category.name', 'Name'),
                ]
            ],
            ProductAttributeValue::class => [
                self::PARENT => 'product',
                self::SECTION => 'Attribute value',
                self::ATTRIBUTES => [
                    StringType::make('attribute_value.name')->markAs('attribute_value.attribute.name'),
                ]
            ]
        ];
    }
}
```
Publish the package configuration file
```shell
php artisan vendor:publish --provider="ModelLogger\ServiceProvider" --tag=config
```
This will create a file `config/model-logger.php`
```php
return [
    /**
     * Access to use the Model Logger feature
     */
    'enabled' => env('MODEL_LOGGER_ENABLED', true),

    /**
     * Model Logger table name
     */
    'table_name' => env('MODEL_LOGGER_TABLE_NAME', 'model_logs'),

    /**
     * The list of active Loggers
     */
    'loggers' => [
       // ...
    ]
];
```
Publish the package migration and migrate
```shell
php artisan vendor:publish --provider="ModelLogger\ServiceProvider" --tag=migrations
php artisan migrate
```
This should return the existing log
```php
$logger = modelLog()->limit(10)->get();
```
### Testing
Before testing, create `.env` in package folder
```shell
cp .env.testing .env
```
Run test
```shell
composer test
```
### License
The MIT License (MIT). Please see [License File](https://github.com/truefalse-dev/model-logger/blob/master/LICENSE.md) for more information.
