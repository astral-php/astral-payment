<?php

declare(strict_types=1);

namespace AstralPayment;

use AstralPayment\Controllers\PaymentController;
use AstralPayment\Controllers\WebhookController;
use AstralPayment\Dao\OrderDao;
use AstralPayment\Dao\PaymentDao;
use AstralPayment\Events\PaymentFailed;
use AstralPayment\Events\PaymentSucceeded;
use AstralPayment\Listeners\LogPaymentActivity;
use AstralPayment\Listeners\SendOrderConfirmation;
use Core\Container;
use Core\Events\EventDispatcher;
use Core\Logger;
use Core\Mailer\Mailer;
use Core\Request;
use Core\ServiceProviderInterface;
use Core\Session;
use Core\View;
use PDO;

/**
 * Enregistre les services paiement dans le container Astral.
 *
 * Dans config/dependencies.php :
 *   AstralPayment\PaymentServiceProvider::class,
 */
final class PaymentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container, array $appConfig, array $dbConfig): void
    {
        $container->singleton(PaymentService::class, static fn ($c) => new PaymentService(
            secretKey: $_ENV['STRIPE_SECRET_KEY'] ?? '',
            webhookSecret: $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
            logger: $c->make(Logger::class),
        ));

        $container->singleton(OrderDao::class, static fn ($c) => new OrderDao($c->make(PDO::class)));
        $container->singleton(PaymentDao::class, static fn ($c) => new PaymentDao($c->make(PDO::class)));

        $container->bind(PaymentController::class, static fn ($c) => new PaymentController(
            view: $c->make(View::class),
            session: $c->make(Session::class),
            request: $c->make(Request::class),
            paymentService: $c->make(PaymentService::class),
            orderDao: $c->make(OrderDao::class),
        ));

        $container->bind(WebhookController::class, static fn ($c) => new WebhookController(
            paymentService: $c->make(PaymentService::class),
            orderDao: $c->make(OrderDao::class),
            paymentDao: $c->make(PaymentDao::class),
            dispatcher: $c->make(EventDispatcher::class),
            logger: $c->make(Logger::class),
        ));

        $container->bind(SendOrderConfirmation::class, static fn ($c) => new SendOrderConfirmation(
            mailer: $c->make(Mailer::class),
            orderDao: $c->make(OrderDao::class),
        ));

        $container->bind(LogPaymentActivity::class, static fn ($c) => new LogPaymentActivity(
            logger: $c->make(Logger::class),
        ));

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->make(EventDispatcher::class);

        $dispatcher->listen(PaymentSucceeded::class, SendOrderConfirmation::class);
        $dispatcher->listen(PaymentSucceeded::class, LogPaymentActivity::class);
        $dispatcher->listen(PaymentFailed::class, LogPaymentActivity::class);
    }
}
