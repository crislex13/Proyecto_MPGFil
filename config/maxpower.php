<?php

return [
    'asistencia_debounce_min' => 2,   // 2–5 en prod

    // clientes: plan
    'plan_tolerancia_antes' => 0,
    'plan_tolerancia_despues' => 0,
    'plan_duracion_defecto_min' => 90,  // si el plan no tiene hora_fin

    // clientes: sesión
    'sesion_tolerancia_antes' => 10,
    'sesion_tolerancia_despues' => 5,

    // personal
    'personal_tolerancia_antes' => 60,
    'personal_tolerancia_despues' => 0,

    // autocierre
    'autocierre' => [
        'habilitado' => true,
        'gracia_min' => 5,   // espera 5 min tras el fin programado
        'intervalo_cron_min' => 5,   // corre cada 5 min
    ],

    'kiosk' => [
        'poll_seconds'          => 3,  // refresco en vivo
        'welcome_window_seconds'=> 25, // cuánto tiempo mostrar el último evento
        'warn_minutes'          => 5,  // umbral para alertas
    ],
];