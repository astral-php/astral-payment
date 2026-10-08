<?php

declare(strict_types=1);

namespace AstralPayment\Tests\Middleware;

use AstralPayment\Middleware\StripeWebhookMiddleware;
use Core\Request;
use PHPUnit\Framework\TestCase;

final class StripeWebhookMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_STRIPE_SIGNATURE'], $_SERVER['REQUEST_METHOD']);
    }

    public function test_calls_next_when_signature_and_post_present(): void
    {
        $_SERVER['HTTP_STRIPE_SIGNATURE'] = 't=1,v1=abc';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $called = false;
        $middleware = new StripeWebhookMiddleware();
        $result = $middleware->handle(new Request(), static function () use (&$called) {
            $called = true;

            return 'ok';
        });

        $this->assertTrue($called);
        $this->assertSame('ok', $result);
    }
}
