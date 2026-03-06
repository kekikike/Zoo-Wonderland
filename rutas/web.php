<?php
// routes/web.php
return [
    '/'              => 'App\Controllers\HomeController@index',     // crea este controlador si quieres separar
    '/login'         => 'App\Controllers\AuthController@showLogin',
    '/login'         => 'App\Controllers\AuthController@login',     // POST implícito por método
    '/logout'        => 'App\Controllers\AuthController@logout',
    '/compras/historial' => 'App\Controllers\CompraController@historial',
    // más rutas...
];