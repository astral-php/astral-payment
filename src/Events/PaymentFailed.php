<?php

declare(strict_types=1);

namespace AstralPayment\Events;

use Core\Events\EventInterface;

/**
 * Dispatché lorsque Stripe signale un échec de paiement.
 */
final class PaymentFailed implements EventInterface
{
    public function __construct(
        public readonly object $paymentIntent,
        public readonly int $orderId,
    ) {
    }
}
