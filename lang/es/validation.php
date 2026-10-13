<?php

return [
    'accepted' => 'Debe aceptar :attribute.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_with' => 'El campo :attribute es obligatorio cuando se proporciona :values.',
    'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'string' => 'El campo :attribute debe ser texto.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'gt' => ['numeric' => 'El campo :attribute debe ser mayor que :value.'],
    'max' => [
        'string' => 'El campo :attribute no debe superar :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
    ],
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'attributes' => [
        'type' => 'tipo de consulta', 'name' => 'nombre completo', 'company' => 'empresa',
        'email' => 'correo electrónico profesional', 'country' => 'país', 'category' => 'categoría',
        'product' => 'producto / especificaciones', 'quantity' => 'cantidad', 'quantityUnit' => 'unidad de cantidad',
        'packDetails' => 'detalles del envase', 'origin' => 'origen', 'destination' => 'destino',
        'message' => 'mensaje', 'consent' => 'el reconocimiento de privacidad',
    ],
];
