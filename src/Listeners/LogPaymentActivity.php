<?php

declare(strict_types=1);

namespace AstralPayment\Listeners;

use AstralPayment\Events\PaymentFailed;
use AstralPayment\Events\PaymentSucceeded;
use Core\Events\EventInterface;
use Core\Events\ListenerInterface;
use Core\Logger;

/**
 * Journalise les événements de paiement.
 */
final class LogPaymentActivity implements ListenerInterface
{
    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        $context = [
            'intent_id' => $event->paymentIntent->id ?? 'unknown',
            'order_id'  => $event->orderId,
            'amount'    => $event->paymentIntent->amount ?? 0,
            'currency'  => $event->paymentIntent->currency ?? 'eur',
        ];

        match (true) {
            $event instanceof PaymentSucceeded => $this->logger->info('Paiement réussi', $context),
            $event instanceof PaymentFailed => $this->logger->warning(
                'Paiement échoué',
                array_merge($context, [
                    'failure_message' => $event->paymentIntent->last_payment_error?->message ?? 'N/A',
                ])
            ),
            default => null,
        };
    }
}
