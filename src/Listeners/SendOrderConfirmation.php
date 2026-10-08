<?php

declare(strict_types=1);

namespace AstralPayment\Listeners;

use AstralPayment\Dao\OrderDao;
use AstralPayment\Events\PaymentSucceeded;
use AstralPayment\Models\Order;
use Core\Events\EventInterface;
use Core\Events\ListenerInterface;
use Core\Mailer\Mailer;

/**
 * E-mail de confirmation après paiement réussi.
 */
final class SendOrderConfirmation implements ListenerInterface
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly OrderDao $orderDao,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        assert($event instanceof PaymentSucceeded);

        if ($event->orderId === 0) {
            return;
        }

        $order = $this->orderDao->findById($event->orderId);

        if ($order === null) {
            return;
        }

        $customerEmail = $event->paymentIntent->receipt_email
            ?? $event->paymentIntent->metadata->customer_email
            ?? null;

        if ($customerEmail === null) {
            return;
        }

        $subject = 'Confirmation de votre commande #' . $order->id;
        $body = $this->buildEmailBody($order);

        $this->mailer->send($customerEmail, $subject, $body);
    }

    private function buildEmailBody(Order $order): string
    {
        return implode("\n", [
            '<h2>Merci pour votre commande !</h2>',
            '<p>Votre paiement de <strong>' . $order->formattedTotal() . '</strong> a bien été reçu.</p>',
            '<p>Numéro de commande : <strong>#' . $order->id . '</strong></p>',
            '<p>Nous traitons votre commande et vous tiendrons informé par e-mail.</p>',
        ]);
    }
}
