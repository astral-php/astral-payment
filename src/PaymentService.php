<?php

declare(strict_types=1);

namespace AstralPayment;

use Core\Logger;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Event;

/**
 * Couche d'abstraction autour de l'API Stripe.
 * Les contrôleurs n'importent jamais Stripe directement.
 */
final class PaymentService
{
    private StripeClient $stripe;

    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
        private readonly Logger $logger,
    ) {
        $this->stripe = new StripeClient($this->secretKey);
    }

    /**
     * @param  array<string, scalar|null> $metadata
     * @return array{clientSecret: string, intentId: string}
     */
    public function createPaymentIntent(
        int $amountCents,
        string $currency = 'eur',
        array $metadata = [],
    ): array {
        $intent = $this->stripe->paymentIntents->create([
            'amount'                    => $amountCents,
            'currency'                  => $currency,
            'metadata'                  => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $this->logger->info('PaymentIntent créé', [
            'intent_id' => $intent->id,
            'amount'    => $amountCents,
            'currency'  => $currency,
        ]);

        return [
            'clientSecret' => $intent->client_secret,
            'intentId'     => $intent->id,
        ];
    }

    public function retrievePaymentIntent(string $intentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($intentId);
    }

    public function refund(string $paymentIntentId, ?int $amountCents = null): Refund
    {
        $params = ['payment_intent' => $paymentIntentId];

        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }

        $refund = $this->stripe->refunds->create($params);

        $this->logger->info('Remboursement créé', [
            'refund_id' => $refund->id,
            'intent_id' => $paymentIntentId,
            'amount'    => $amountCents ?? 'total',
        ]);

        return $refund;
    }

    /**
     * @throws SignatureVerificationException
     */
    public function parseWebhook(string $payload, string $signature): Event
    {
        return Webhook::constructEvent($payload, $signature, $this->webhookSecret);
    }

    public static function toCents(float $euros): int
    {
        return (int) round($euros * 100);
    }

    public static function toEuros(int $cents): string
    {
        return number_format($cents / 100, 2, ',', ' ') . ' €';
    }
}
