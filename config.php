<?php

return [
    'mode' => 'utf-8',
    'format' => [107.5, 206],
    'default_font_size' => '12',
    'default_font' => 'nanumbarungothic',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_header' => 5,
    'margin_footer' => 0,
    'orientation' => 'L',
    'title' => 'AIPro Invoices',
    'subject' => '',
    'author' => '',
    'watermark' => '',
    'show_watermark' => false,
    'show_watermark_image' => false,
    'watermark_font' => 'nanumbarungothic',
    'display_mode' => 'fullpage',
    'watermark_text_alpha' => 0.1,
    'watermark_image_path' => '',
    'watermark_image_alpha' => 0.2,
    'watermark_image_size' => 'D',
    'watermark_image_position' => 'P',
    'custom_font_dir' => __DIR__.DIRECTORY_SEPARATOR.'fonts',
    'custom_font_data' => [
        'nanumbarungothic' => [ // // must be lowercase and snake_case
            'R' => 'NanumBarunGothic.ttf',  // regular font
            'B' => 'NanumBarunGothic.ttf',  // optional: bold font
            'I' => 'NanumBarunGothic.ttf',  // optional: italic font
            'BI' => 'NanumBarunGothic.ttf', // optional: bold-italic font
        ],
    ],
    'auto_language_detection' => false,
    'temp_dir' => __DIR__.DIRECTORY_SEPARATOR.'tmp',
    'pdfa' => false,
    'pdfaauto' => false,
    'use_active_forms' => false,
];
