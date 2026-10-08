<?php

declare(strict_types=1);

namespace AstralPayment\Dao;

use AstralPayment\Models\Payment;
use Database\AbstractDao;

/**
 * Accès aux données de la table `payments`.
 */
final class PaymentDao extends AbstractDao
{
    protected function getTable(): string
    {
        return 'payments';
    }

    protected function getModelClass(): string
    {
        return Payment::class;
    }

    /**
     * @return list<Payment>
     */
    public function findByOrder(int $orderId): array
    {
        return $this->hasMany(
            relatedClass: Payment::class,
            table: 'payments',
            foreignKey: 'order_id',
            localId: $orderId,
            orderBy: 'created_at',
            direction: 'DESC',
        );
    }

    public function findSucceededByOrder(int $orderId): ?Payment
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM payments WHERE order_id = :order_id AND status = 'succeeded' LIMIT 1"
        );
        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $payment = new Payment();
        foreach ($row as $key => $value) {
            if (property_exists($payment, $key)) {
                $payment->$key = $value;
            }
        }

        return $payment;
    }
}
