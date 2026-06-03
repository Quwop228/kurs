<?php

return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'model' => env('OPENROUTER_MODEL', 'google/gemma-3-27b-it:free'),
    'base_url' => 'https://openrouter.ai/api/v1',
];
