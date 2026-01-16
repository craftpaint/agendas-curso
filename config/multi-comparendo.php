<?php

return [
    'max_comparendos' => env('MAX_COMPARENDOS', 3),
    'habilitado' => env('MULTI_COMPARENDO_HABILITADO', true),
    'dias_disponibles' => [1, 2, 3, 4, 5], // Lunes a Viernes
    'horas_limite' => [
        'min' => '08:00',
        'max' => '18:00'
    ],
    'mensajes' => [
        'limite_alcanzado' => 'Solo puede agendar máximo :max comparendos',
        'campos_requeridos' => 'Complete todos los campos requeridos',
        'exito' => ':count citas agendadas correctamente'
    ]
];