<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('docs.swagger');
});

Route::get('/docs/swagger', function () {
    return view('docs.swagger');
});

Route::get('/docs/openapi.yaml', function () {
    $path = base_path('docs/openapi.yaml');

    abort_unless(file_exists($path), 404, 'OpenAPI spec no encontrada.');

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/yaml; charset=UTF-8',
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
});
