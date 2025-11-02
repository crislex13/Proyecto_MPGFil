<?php

return [
    // Anti-rebote (general)
    'asistencia_debounce_min' => 5,   // en dev puedes 2; en prod 5

    // Clientes: plan
    'plan_tolerancia_antes' => 0,
    'plan_tolerancia_despues' => 0,
    'plan_duracion_defecto_min' => 90,  // si el plan no tiene hora_fin

    // Clientes: sesión
    'sesion_tolerancia_antes' => 10,
    'sesion_tolerancia_despues' => 5,

    // Personal (solo UNA vez estas claves)
    'personal_tolerancia_antes' => 60,
    'personal_tolerancia_despues' => 0,
    'personal_min_salida_min' => 15,

    // Autocierre
    'autocierre' => [
        'habilitado' => true,
        'gracia_min' => 5,   // espera 5 min tras el fin programado
        'intervalo_cron_min' => 5,   // corre cada 5 min
    ],

    // Kiosco
    'kiosk' => [
        'poll_seconds' => 3,   // refresco en vivo
        'welcome_window_seconds' => 25,  // ventana para mostrar último evento
        'warn_minutes' => 5,   // umbral de alerta “faltan N min”
    ],

    // Reglas de cliente
    'cliente_gracia_deuda_dias' => 5,      // acceso aunque deudor, solo 5 días desde inicio
    'plan_ingresos_ilimitados' => false,  // false = 1 ingreso/día; true = ilimitado
    'permiso_cliente_permite_ingreso' => false,  // ⬅️ permiso aprobado HOY BLOQUEA ingreso
    'cliente_min_salida_min' => 15,     // tiempo mínimo para permitir marcar salida
    'plan_max_ingresos_diarios_por_defecto' => 1,
];