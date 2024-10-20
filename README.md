```php
    'modules' => [
        ...
        'stocks' => [
            'class' => \mamadali\hesabroStocks\StocksModule::class,
        ],
        ...
    ]
```

you can add language
```php
    'components' => [
        ...
        'i18n' => [
            'translations' => [
                ...
                'stocks' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@backend/modules/stocks/src/messages',
                    'sourceLanguage' => 'en',
                    //'sourceLanguage' => 'fa',
                    'fileMap' => [
                        'stocks' => 'stocks.php',
                    ],
                ],
                ...
            ],
        ],
        ...
    ]
```