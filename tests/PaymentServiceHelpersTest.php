<?php

declare(strict_types=1);

namespace AstralPayment\Tests;

use AstralPayment\PaymentService;
use Core\Logger;
use PHPUnit\Framework\TestCase;

final class PaymentServiceHelpersTest extends TestCase
{
    public function test_to_cents_rounds_half_up(): void
    {
        $this->assertSame(2999, PaymentService::toCents(29.99));
        $this->assertSame(1000, PaymentService::toCents(10.0));
        $this->assertSame(1, PaymentService::toCents(0.005));
    }

    public function test_to_euros_formats_french(): void
    {
        $this->assertSame('29,99 €', PaymentService::toEuros(2999));
        $this->assertSame('10,00 €', PaymentService::toEuros(1000));
    }

    public function test_parse_webhook_rejects_invalid_signature(): void
    {
        $logDir = sys_get_temp_dir() . '/astral_payment_test_' . uniqid('', true);
        mkdir($logDir, 0755, true);

        $service = new PaymentService(
            secretKey: 'sk_test_dummy',
            webhookSecret: 'whsec_test_dummy',
            logger: new Logger($logDir),
        );

        $this->expectException(\Stripe\Exception\SignatureVerificationException::class);
        $service->parseWebhook('{"id":"evt_test"}', 't=1,v1=invalid');
    }
}
