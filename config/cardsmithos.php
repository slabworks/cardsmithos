<?php

return [
    'name' => 'Cardsmith OS',
    'url' => 'https://cardsmithos.test',

    /*
    |--------------------------------------------------------------------------
    | Default hourly rate (fallback for card estimated_fee)
    |--------------------------------------------------------------------------
    */
    'hourly_rate' => (float) env('CARDSMITHOS_HOURLY_RATE', 100),
];
