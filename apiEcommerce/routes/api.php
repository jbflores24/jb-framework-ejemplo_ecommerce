<?php

declare(strict_types=1);

use Jb\Core\Request;
use Jb\Core\Response;
use Jb\Security\SecurityRoutes;

/** @var \Jb\Core\Router $router */
$router->get('/health', function (Request $request): Response {
    return Response::success([
        'service' => 'jb',
        'path' => $request->path(),
    ], 'API disponible.');
});

SecurityRoutes::register($router);

// Categories
$router->get('/categories', [App\Controllers\CategoryController::class, 'index']);
$router->get('/categories/{id}', [App\Controllers\CategoryController::class, 'show']);
$router->post('/categories', [App\Controllers\CategoryController::class, 'store']);
$router->put('/categories/{id}', [App\Controllers\CategoryController::class, 'update']);
$router->delete('/categories/{id}', [App\Controllers\CategoryController::class, 'destroy']);

// Products
$router->get('/products', [App\Controllers\ProductController::class, 'index']);
$router->get('/products/{id}', [App\Controllers\ProductController::class, 'show']);
$router->post('/products', [App\Controllers\ProductController::class, 'store']);
$router->put('/products/{id}', [App\Controllers\ProductController::class, 'update']);
$router->delete('/products/{id}', [App\Controllers\ProductController::class, 'destroy']);

// Customers
$router->get('/customers', [App\Controllers\CustomerController::class, 'index']);
$router->get('/customers/{id}', [App\Controllers\CustomerController::class, 'show']);
$router->post('/customers', [App\Controllers\CustomerController::class, 'store']);
$router->put('/customers/{id}', [App\Controllers\CustomerController::class, 'update']);
$router->delete('/customers/{id}', [App\Controllers\CustomerController::class, 'destroy']);

// Orders
$router->get('/orders', [App\Controllers\OrderController::class, 'index']);
$router->get('/orders/{id}', [App\Controllers\OrderController::class, 'show']);
$router->post('/orders', [App\Controllers\OrderController::class, 'store']);
$router->put('/orders/{id}', [App\Controllers\OrderController::class, 'update']);
$router->delete('/orders/{id}', [App\Controllers\OrderController::class, 'destroy']);
$router->post('/checkout', [App\Controllers\OrderController::class, 'store']);

// Order items
$router->get('/order_items', [App\Controllers\OrderItemController::class, 'index']);
$router->get('/order_items/{id}', [App\Controllers\OrderItemController::class, 'show']);
$router->post('/order_items', [App\Controllers\OrderItemController::class, 'store']);
$router->put('/order_items/{id}', [App\Controllers\OrderItemController::class, 'update']);
$router->delete('/order_items/{id}', [App\Controllers\OrderItemController::class, 'destroy']);
