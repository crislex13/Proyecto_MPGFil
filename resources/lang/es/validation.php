<?php

return [
    'unique' => [
        'ci' => 'Ya existe un registro con ese número de C.I.',
        'telefono' => 'Este número de WhatsApp ya está registrado.',
        'cargo.required' => 'Debe seleccionar un cargo.',
    ],

    'mimetypes' => 'El archivo debe ser una imagen válida (JPG, PNG, JPEG).',
    'before_or_equal' => 'La :attribute no es una fecha válida.',
    'after_or_equal' => 'La :attribute no es una fecha válida.',
    'date' => 'La :attribute no es una fecha válida.',

    'attributes' => [
        'ci' => 'C.I.',
        'correo' => 'correo electrónico',
        'telefono' => 'teléfono',
        'nombre' => 'nombre',
        'apellido_paterno' => 'apellido paterno',
        'apellido_materno' => 'apellido materno',
        'fecha_de_nacimiento' => 'fecha de nacimiento',
        'sexo' => 'sexo',
        'foto' => 'foto',
        'biometrico_id' => 'ID biométrico',
        'contacto_emergencia_nombre' => 'nombre del contacto de emergencia',
        'contacto_emergencia_parentesco' => 'parentesco del contacto',
        'contacto_emergencia_celular' => 'celular del contacto de emergencia',
        'antecedentes_medicos' => 'antecedentes médicos',
        'password' => 'contraseña',
        'username' => 'usuario',
        'cargo' => 'cargo',
    ],

    'required' => [
        'cargo' => 'Debe seleccionar un cargo.',
    ],
];
