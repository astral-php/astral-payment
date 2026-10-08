<?php

declare(strict_types=1);

namespace AstralPayment\Controllers;

use AstralPayment\Dao\OrderDao;
use AstralPayment\PaymentService;
use Controller\AbstractController;
use Core\Http\Response;
use Core\Request;
use Core\Session;
use Core\View;

/**
 * Flux checkout utilisateur (Stripe Elements).
 */
final class PaymentController extends AbstractController
{
    public function __construct(
        View $view,
        private readonly Session $session,
        private readonly Request $request,
        private readonly PaymentService $paymentService,
        private readonly OrderDao $orderDao,
    ) {
        parent::__construct($view);
    }

    public function checkout(): Response
    {
        $orderId = (int) $this->request->query('order_id', 0);
        $order = $this->orderDao->findById($orderId);

        if ($order === null) {
            $this->session->flash('error', 'Commande introuvable.');

            return $this->redirect('/');
        }

        if ($order->status === 'paid') {
            $this->session->flash('info', 'Cette commande est déjà payée.');

            return $this->redirect('/orders/' . $order->id);
        }

        if ($order->stripe_intent_id) {
            $intent = $this->paymentService->retrievePaymentIntent($order->stripe_intent_id);
            $clientSecret = $intent->client_secret;
        } else {
            $data = $this->paymentService->createPaymentIntent(
                amountCents: $order->total_cents,
                currency: $order->currency,
                metadata: ['order_id' => $order->id],
            );
            $clientSecret = $data['clientSecret'];

            $this->orderDao->update($order->id, [
                'stripe_intent_id' => $data['intentId'],
            ]);
        }

        return $this->render('payment/checkout', [
            'order'           => $order,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
            'clientSecret'    => $clientSecret,
            'returnUrl'       => rtrim($_ENV['APP_URL'] ?? '', '/') . '/payment/success',
            'amountFormatted' => PaymentService::toEuros($order->total_cents),
        ]);
    }

    public function success(): Response
    {
        $this->session->flash('success', 'Paiement reçu ! Votre commande est en cours de traitement.');

        return $this->redirect('/orders');
    }

    public function cancel(): Response
    {
        $this->session->flash('error', 'Paiement annulé. Vous pouvez réessayer.');

        return $this->redirect('/cart');
    }
}
