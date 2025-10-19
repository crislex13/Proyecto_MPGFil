<?php

return [
    'disable' => env('CAPTCHA_DISABLE', false),

    // Sin caracteres confusos: 0 O I l 1
    'characters' => 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789',

    // Si no usas fuentes/fondos propios, comenta estas dos líneas:
    // 'fontsDirectory' => dirname(__DIR__) . '/assets/fonts',
    // 'bgsDirectory' => dirname(__DIR__) . '/assets/backgrounds',

    'default' => [
        'length' => 5,
        'width' => 300,
        'height' => 70,
        'quality' => 90,
        'math' => false,
        'expire' => 120,
        'encrypt' => false,
        'sensitive' => false, // no distingue mayúsculas/minúsculas
    ],

    // Preset que usarás en la vista: captcha_src('flat')
    'flat' => [
        'length' => 5,
        'fontColors' => ['#151515', '#222222', '#333333', '#444444'],
        'width' => 300,
        'height' => 70,
        'math' => false,
        'quality' => 90,
        'lines' => 4,
        'bgImage' => false,      // más limpio
        'bgColor' => '#ffffff',  // fondo claro = mejor lectura
        'contrast' => -8,
        'sensitive' => false,
    ],

    'mini' => [
        'length' => 3,
        'width' => 60,
        'height' => 32,
    ],

    'inverse' => [
        'length' => 5,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'sensitive' => true,
        'angle' => 12,
        'sharpen' => 10,
        'blur' => 2,
        'invert' => false,
        'contrast' => -5,
    ],
];
