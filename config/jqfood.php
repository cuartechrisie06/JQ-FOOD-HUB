<?php

return [
    'default_max_bookings' => 2,
    'owner_email' => env('OWNER_EMAIL', env('JQ_OWNER_EMAIL', 'owner@jqfoodhub.local')),
    'owner_password' => env('OWNER_PASSWORD', env('JQ_OWNER_PASSWORD')),
    'owner_name' => env('OWNER_NAME', env('JQ_OWNER_NAME', 'JQ Food Hub Owner')),
    'owner_phone' => env('OWNER_PHONE', env('JQ_OWNER_PHONE', '')),
    'owner_address' => env('OWNER_ADDRESS', env('JQ_OWNER_ADDRESS', 'Villa Patricia Subd., Mankilam, Tagum City')),

    // Shown on the checkout page when the customer picks E-Wallet / Bank Transfer.
    // Leave blank to show a generic "we will message you" note instead.
    'payments' => [
        'ewallet_name' => env('JQ_EWALLET_NAME', 'GCash'),
        'ewallet_number' => env('JQ_EWALLET_NUMBER'),
        'ewallet_account_name' => env('JQ_EWALLET_ACCOUNT_NAME'),
        'bank_name' => env('JQ_BANK_NAME'),
        'bank_account_number' => env('JQ_BANK_ACCOUNT_NUMBER'),
        'bank_account_name' => env('JQ_BANK_ACCOUNT_NAME'),
    ],
];
