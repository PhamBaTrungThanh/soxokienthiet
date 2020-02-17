<?php

    return [
        'distribution' => [
            'validation' => [
                'percent' => env('VALIDATION_PERCENT', 10),
            ],
        ],
        'image' => [
            'cell_size' => env('IMAGE_CELL_SIZE', 5),
            'row_grid' => env('IMAGE_ROW_GRID', 10),
        ],
        'lottery' => [
            'key' => env('LOTTERY_KEY'),
            'latest_date' => env('OPTION_LATEST_DATE_CRAWLED'),
            'oldest_date' => env('LOTTERY_OLDEST_DATE', '2006-04-19'),
        ],
    ];
