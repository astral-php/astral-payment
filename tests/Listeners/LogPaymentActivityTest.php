<?php

declare(strict_types=1);

namespace AstralPayment\Tests\Listeners;

use AstralPayment\Events\PaymentFailed;
use AstralPayment\Events\PaymentSucceeded;
use AstralPayment\Listeners\LogPaymentActivity;
use Core\Logger;
use PHPUnit\Framework\TestCase;
use stdClass;

final class LogPaymentActivityTest extends TestCase
{
    private string $logDir;

    protected function setUp(): void
    {
        $this->logDir = sys_get_temp_dir() . '/astral_payment_log_' . uniqid('', true);
        mkdir($this->logDir, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->logDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->logDir);
    }

    public function test_logs_success(): void
    {
        $intent = new stdClass();
        $intent->id = 'pi_ok';
        $intent->amount = 1999;
        $intent->currency = 'eur';

        $listener = new LogPaymentActivity(new Logger($this->logDir));
        $listener->handle(new PaymentSucceeded($intent, 9));

        $files = glob($this->logDir . '/*.log');
        $this->assertNotFalse($files);
        $this->assertNotEmpty($files);

        $content = (string) file_get_contents($files[0]);
        $this->assertStringContainsString('Paiement réussi', $content);
        $this->assertStringContainsString('pi_ok', $content);
    }

    public function test_logs_failure_with_message(): void
    {
        $error = new stdClass();
        $error->message = 'Card declined';

        $intent = new stdClass();
        $intent->id = 'pi_fail';
        $intent->amount = 500;
        $intent->currency = 'eur';
        $intent->last_payment_error = $error;

        $listener = new LogPaymentActivity(new Logger($this->logDir));
        $listener->handle(new PaymentFailed($intent, 3));

        $files = glob($this->logDir . '/*.log');
        $content = (string) file_get_contents($files[0]);
        $this->assertStringContainsString('Paiement échoué', $content);
        $this->assertStringContainsString('Card declined', $content);
    }
}
