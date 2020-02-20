<?php

    return [
        'distribution' => [
            'validation' => [
                'percent' => env('VALIDATION_PERCENT', 10),
            ],
            'path' => env('DISTRIBUTION_PATH', storage_path('app/distributions')),
        ],
        'image' => [
            'cell_size' => env('IMAGE_CELL_SIZE', 5),
            'row_grid' => env('IMAGE_ROW_GRID', 10),
        ],
        'lottery' => [
            'key' => env('LOTTERY_KEY', 'lottery'),
            'latest_date' => env('OPTION_LATEST_DATE_CRAWLED', 'options:latest_date_crawled'),
            'oldest_date' => env('LOTTERY_OLDEST_DATE', '2006-04-19'),
        ],
    ];
