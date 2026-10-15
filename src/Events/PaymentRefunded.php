<?php

declare(strict_types=1);

namespace AstralPayment\Events;

use Core\Events\EventInterface;

/**
 * Dispatché lorsque Stripe confirme un remboursement (charge.refunded)
 * et que la commande passe pour la première fois en status refunded.
 */
final class PaymentRefunded implements EventInterface
{
    public function __construct(
        public readonly object $chargeOrRefund,
        public readonly int $orderId,
    ) {
    }
}
