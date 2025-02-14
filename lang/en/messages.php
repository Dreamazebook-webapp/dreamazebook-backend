<?php

return [
    'language_switched' => 'Language switched successfully',
    'validation_error' => 'Validation error',
    'route_not_found' => 'Route not found.',
    'method_not_allowed' => 'The :method method is not supported for route :route. Supported methods: :allowed_methods.',
    'server_error' => 'Internal Server Error',
    'unauthorized' => 'Unauthorized',
    'forbidden' => 'Forbidden',
    'bad_request' => 'Bad Request',
    'unknown_error' => 'An unknown error occurred',
    'model_not_found' => 'The specified :model was not found',
    'too_many_requests' => 'Too many requests. Please try again in :seconds seconds',
    'http_error' => 'HTTP Error',
    'validation' => [
        'required' => 'The :attribute field is required',
        'email' => 'The :attribute must be a valid email address',
        'min' => [
            'string' => 'The :attribute must be at least :min characters',
        ],
        'max' => [
            'string' => 'The :attribute may not be greater than :max characters',
        ],
        'unique' => 'The :attribute has already been taken',
        'confirmed' => 'The :attribute confirmation does not match',
    ],
];
