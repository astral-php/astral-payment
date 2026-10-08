<?php

declare(strict_types=1);

namespace AstralPayment\Controllers;

use AstralPayment\Dao\OrderDao;
use AstralPayment\Dao\PaymentDao;
use AstralPayment\Events\PaymentFailed;
use AstralPayment\Events\PaymentSucceeded;
use AstralPayment\PaymentService;
use Core\Events\EventDispatcher;
use Core\Http\JsonResponse;
use Core\Logger;
use Stripe\Exception\SignatureVerificationException;

/**
 * Reçoit et traite les webhooks Stripe.
 *
 * Route : POST /webhooks/stripe — sans CsrfMiddleware (signature HMAC).
 */
final class WebhookController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly OrderDao $orderDao,
        private readonly PaymentDao $paymentDao,
        private readonly EventDispatcher $dispatcher,
        private readonly Logger $logger,
    ) {
    }

    public function handle(): JsonResponse
    {
        $payload = (string) file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = $this->paymentService->parseWebhook($payload, $signature);
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Webhook Stripe : signature invalide', [
                'message' => $e->getMessage(),
            ]);

            return new JsonResponse(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            $this->logger->error('Webhook Stripe : erreur de parsing', [
                'message' => $e->getMessage(),
            ]);

            return new JsonResponse(['error' => 'Bad request'], 400);
        }

        $this->logger->info('Webhook Stripe reçu', ['type' => $event->type]);

        match ($event->type) {
            'payment_intent.succeeded' => $this->onPaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->onPaymentFailed($event->data->object),
            'charge.refunded' => $this->onChargeRefunded($event->data->object),
            default => null,
        };

        return new JsonResponse(['received' => true], 200);
    }

    private function onPaymentSucceeded(object $intent): void
    {
        $orderId = (int) ($intent->metadata->order_id ?? 0);

        if ($orderId > 0) {
            $this->orderDao->update($orderId, ['status' => 'paid']);

            $this->paymentDao->insert([
                'order_id'         => $orderId,
                'stripe_intent_id' => $intent->id,
                'amount_cents'     => $intent->amount_received,
                'currency'         => $intent->currency,
                'status'           => 'succeeded',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        $this->dispatcher->dispatch(new PaymentSucceeded($intent, $orderId));
    }

    private function onPaymentFailed(object $intent): void
    {
        $orderId = (int) ($intent->metadata->order_id ?? 0);

        if ($orderId > 0) {
            $this->orderDao->update($orderId, ['status' => 'failed']);

            $this->paymentDao->insert([
                'order_id'         => $orderId,
                'stripe_intent_id' => $intent->id,
                'amount_cents'     => $intent->amount,
                'currency'         => $intent->currency,
                'status'           => 'failed',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        $this->dispatcher->dispatch(new PaymentFailed($intent, $orderId));
    }

    private function onChargeRefunded(object $charge): void
    {
        $this->logger->info('Charge remboursée', ['charge_id' => $charge->id]);
    }
}
