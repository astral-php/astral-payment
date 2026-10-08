<?php

declare(strict_types=1);

namespace AstralPayment\Middleware;

use Core\Middleware\MiddlewareInterface;
use Core\Request;

/**
 * Vérifie la présence de Stripe-Signature avant le contrôleur.
 * La vérif HMAC complète est dans PaymentService::parseWebhook().
 */
final class StripeWebhookMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (empty($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing Stripe-Signature header']);
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        return $next();
    }
}
