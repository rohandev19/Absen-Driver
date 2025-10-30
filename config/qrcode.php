<?php

return [
    'default' => 'simple',
    'generators' => [
        'simple' => [
            'size' => 120,
            'color' => '#000000',
            'background_color' => '#FFFFFF',
            'margin' => 0,
            'error_correction' => 'L',
            'style' => 'square',
            'eye' => 'square',
            'encoding' => 'UTF-8',
        ],
    ],
    'renderer' => 'gd', // Akan kita ubah di langkah berikutnya
    'round_block_size' => true,
    'logo' => [
        'path' => null,
        'size' => 0.2,
        'fill_color' => '#FFFFFF',
    ],
    'color' => [
        'gradient' => [
            'start_color' => null,
            'end_color' => null,
            'type' => 'vertical',
        ],
        'center_color' => null,
    ],
];