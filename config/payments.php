<?php


return [
    'gateways' => [
        'stripe' => \App\Payments\Gateways\StripeGateway::class,
    ],
    'allow_live_keys' => env('ALLOW_LIVE_KEYS', false),
];
