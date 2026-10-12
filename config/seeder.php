<?php

// Akun tes untuk DatabaseSeeder
return [

    'admin' => [
        'name' => env('ADMIN_NAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'member' => [
        'name' => env('MEMBER_NAME'),
        'email' => env('MEMBER_EMAIL'),
        'password' => env('MEMBER_PASSWORD'),
    ],

];
