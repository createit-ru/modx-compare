<?php

return [
    'compare' => [
        'file' => 'compare',
        'description' => 'Show compare table',
        'properties' => [
            'tpl' => [
                'type' => 'textfield',
                'value' => 'compare.Page',
            ],
            'list' => [
                'type' => 'textfield',
                'value' => 'default',
            ],
            'fields' => [
                'type' => 'textfield',
                'value' => 'size,color,weight,made_in,vendor',
            ],
            'priceFields' => [
                'type' => 'textfield',
                'value' => 'price,old_price',
            ],
            'weightFields' => [
                'type' => 'textfield',
                'value' => 'weight',
            ],
            'best' => [
                'type' => 'textfield',
                'value' => 'price:min',
            ],
        ],
    ],
];