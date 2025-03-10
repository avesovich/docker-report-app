<?php

return [
    'feeds' => [
        'news' => [
            'items' => [\App\Models\NewsRSS::class, 'getFeedItems'],
            'url' => '/news/rss',
            'title' => 'Cybersecurity News Feed',
            'description' => 'Latest cybersecurity news from various sources.',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
    ],
];




