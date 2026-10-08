<?php

declare(strict_types=1);

namespace AstralPayment\Tests\Events;

use AstralPayment\Events\PaymentFailed;
use AstralPayment\Events\PaymentSucceeded;
use Core\Events\EventInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EventsTest extends TestCase
{
    public function test_payment_succeeded_implements_event_interface(): void
    {
        $intent = new stdClass();
        $intent->id = 'pi_ok';

        $event = new PaymentSucceeded($intent, 42);

        $this->assertInstanceOf(EventInterface::class, $event);
        $this->assertSame($intent, $event->paymentIntent);
        $this->assertSame(42, $event->orderId);
    }

    public function test_payment_failed_implements_event_interface(): void
    {
        $intent = new stdClass();
        $intent->id = 'pi_fail';

        $event = new PaymentFailed($intent, 0);

        $this->assertInstanceOf(EventInterface::class, $event);
        $this->assertSame(0, $event->orderId);
    }
}
