<?php

/**
 * Routes paiement — à fusionner dans config/routes.php
 *
 * Imports :
 *   use AstralPayment\Controllers\PaymentController;
 *   use AstralPayment\Controllers\WebhookController;
 *   use AstralPayment\Middleware\StripeWebhookMiddleware;
 *   use Core\Auth\Middleware\AuthMiddleware;
 */

use AstralPayment\Controllers\PaymentController;
use AstralPayment\Controllers\WebhookController;
use AstralPayment\Middleware\StripeWebhookMiddleware;
use Core\Auth\Middleware\AuthMiddleware;

$router->group('/payment', function (\Core\Router $r): void {
    $r->get('/checkout', PaymentController::class, 'checkout');
    $r->get('/success', PaymentController::class, 'success');
    $r->get('/cancel', PaymentController::class, 'cancel');
}, [AuthMiddleware::class]);

// Pas de CsrfMiddleware — sécurisé par signature HMAC Stripe
$router->post('/webhooks/stripe', WebhookController::class, 'handle')
    ->middleware(StripeWebhookMiddleware::class);
