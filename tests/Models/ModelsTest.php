<?php

declare(strict_types=1);

namespace AstralPayment\Tests\Models;

use AstralPayment\Models\Order;
use AstralPayment\Models\Payment;
use PHPUnit\Framework\TestCase;

final class ModelsTest extends TestCase
{
    public function test_order_formatted_total_and_paid(): void
    {
        $order = new Order();
        $order->total_cents = 1500;
        $order->status = 'pending';

        $this->assertSame('15,00 €', $order->formattedTotal());
        $this->assertFalse($order->isPaid());

        $order->status = 'paid';
        $this->assertTrue($order->isPaid());
    }

    public function test_order_to_array(): void
    {
        $order = new Order();
        $order->id = 7;
        $order->user_id = 3;
        $order->total_cents = 500;
        $order->currency = 'eur';
        $order->status = 'pending';
        $order->stripe_intent_id = 'pi_abc';
        $order->created_at = '2026-10-04 12:00:00';

        $this->assertSame([
            'id'               => 7,
            'user_id'          => 3,
            'total_cents'      => 500,
            'currency'         => 'eur',
            'status'           => 'pending',
            'stripe_intent_id' => 'pi_abc',
            'created_at'       => '2026-10-04 12:00:00',
        ], $order->toArray());
    }

    public function test_payment_formatted_amount_and_to_array(): void
    {
        $payment = new Payment();
        $payment->id = 1;
        $payment->order_id = 7;
        $payment->stripe_intent_id = 'pi_abc';
        $payment->amount_cents = 500;
        $payment->currency = 'eur';
        $payment->status = 'succeeded';
        $payment->created_at = '2026-10-04 12:00:00';

        $this->assertSame('5,00 €', $payment->formattedAmount());
        $this->assertSame('succeeded', $payment->toArray()['status']);
    }
}
