<?php

return [
    'feeds' => [
        'main' => [
            'items' => ['App\Models\Article', 'getFeedItems'],
            'url' => '/feed',
            'title' => 'Онлайн-энциклопедия — Новые статьи',
            'description' => 'Последние статьи онлайн-энциклопедии',
            'language' => 'ru-RU',
            'image' => '',
            'format' => 'rss',
            'view' => 'feed::rss',
            'type' => '',
            'contentType' => '',
        ],
    ],
];
