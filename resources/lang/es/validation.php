<?php

return [
    'unique' => [
        'ci' => 'Ya existe un cliente con ese número de C.I.',
        // Puedes agregar otros campos si quieres
        'email' => 'Ya existe un usuario con ese correo.',
    ],

    'attributes' => [
        'ci' => 'C.I.',
        'email' => 'correo electrónico',
    ],
];