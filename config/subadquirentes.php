<?php

return [
    'subadq_a' => [
        'base_url' => env('SUBADQ_A_BASE_URL', 'https://0acdeaee-1729-4d55-80eb-d54a125e5e18.mock.pstmn.io'),
        'default_headers' => [
            'Accept' => 'application/json',
        ],
        'responses' => [
            'pix_success' => env('SUBADQ_A_PIX_SUCCESS_HEADER', 'SUCESSO_PIX'),
            'pix_error' => env('SUBADQ_A_PIX_ERROR_HEADER', 'ERRO_PIX'),
            'withdraw_success' => env('SUBADQ_A_WITHDRAW_SUCCESS_HEADER', 'SUCESSO_WD'),
            'withdraw_error' => env('SUBADQ_A_WITHDRAW_ERROR_HEADER', 'ERRO_WD'),
        ],
    ],
    'subadq_b' => [
        'base_url' => env('SUBADQ_B_BASE_URL', 'https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io'),
        'default_headers' => [
            'Accept' => 'application/json',
        ],
        'responses' => [
            'pix_success' => env('SUBADQ_B_PIX_SUCCESS_HEADER', 'SUCESSO_PIX'),
            'pix_error' => env('SUBADQ_B_PIX_ERROR_HEADER', 'ERRO_PIX'),
            'withdraw_success' => env('SUBADQ_B_WITHDRAW_SUCCESS_HEADER', 'SUCESSO_WD'),
            'withdraw_error' => env('SUBADQ_B_WITHDRAW_ERROR_HEADER', 'ERRO_WD'),
        ],
    ],
    'webhook_delay_seconds' => (int) env('SUBADQ_WEBHOOK_DELAY', 3),
];

