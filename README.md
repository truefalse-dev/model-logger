## Eloquent model events Logger

### Main Idea
The log is created from Eloquent model events via Observers attached both to the models themselves and to their related models through relationships.

### Implementation
Create a Logger configuration file in recommended folder ```Loggers```
```
project-root/
├── app/
│   ├── Models/
│   │   └── Loggers/
```

Example:
```
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
                'parent' => 'product',
                self::SECTION => 'Category',
                self::ATTRIBUTES => [
                    StringType::make('category.name', 'Name'),
                ]
            ],
            ProductAttributeValue::class => [
                'parent' => 'product',
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
```
php artisan vendor:publish --provider="ModelLogger\ServiceProvider" --tag=config
```
This will create a file ```model-logger.php```
```
return [
    'loggers' => [
        \App\Models\Loggers\ProductLogger::class,
        ...
    ]
];
```
Publish the package migration
```
php artisan vendor:publish --provider="ModelLogger\ServiceProvider" --tag=migrations
```
The function should return the existing log
```
$logger = modelLog()
    ->limit(10)
    ->get();
```
