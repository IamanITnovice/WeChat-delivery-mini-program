<?php

return [
    'request_header' => 'Authori-Zation',
    'response_header' => 'Authori-Zation',
    'bearer_prefix' => 'Bearer ',
    'jwt_leeway' => 60,
    'tokens' => [
        'admin' => [
            'driver' => 'jwt',
            'ttl' => 7200,
            'renew_mode' => 'manual',
            'renew_window' => 0,
            'description' => 'Admin：JWT，2 小时有效，仅重新登录续期',
        ],
        'shop' => [
            'driver' => 'jwt',
            'ttl' => 43200,
            'renew_mode' => 'sliding',
            'renew_window' => 7200,
            'description' => 'Shop：JWT，12 小时有效，剩余 2 小时内滑动续期并回写响应头',
        ],
        'user' => [
            'driver' => 'jwt',
            'ttl' => 2592000,
            'renew_mode' => 'sliding',
            'renew_window' => 604800,
            'description' => 'User：JWT token，30 天有效，剩余 7 天内滑动续期',
        ],
    ],
];
