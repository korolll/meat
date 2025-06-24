<?php

return [
    'model_defaults' => [
        'namespace'       => 'App\\Models',
        'base_class_name' => \Illuminate\Database\Eloquent\Model::class,
        'output_path'     => app_path('Models'),
        'no_timestamps'   => false,
        'date_format'     => null,
        'connection'      => null,
        'backup'          => true,
    ],
];
