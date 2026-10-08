<?php

declare(strict_types=1);

namespace AstralPayment\Dao;

use AstralPayment\Models\Order;
use Database\AbstractDao;

/**
 * Accès aux données de la table `orders`.
 */
final class OrderDao extends AbstractDao
{
    protected function getTable(): string
    {
        return 'orders';
    }

    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function findByIntentId(string $intentId): ?Order
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM orders WHERE stripe_intent_id = :intent_id LIMIT 1'
        );
        $stmt->execute(['intent_id' => $intentId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $order = new Order();
        foreach ($row as $key => $value) {
            if (property_exists($order, $key)) {
                $order->$key = $value;
            }
        }

        return $order;
    }

    /**
     * @return list<Order>
     */
    public function findByUser(int $userId): array
    {
        return $this->hasMany(
            relatedClass: Order::class,
            table: 'orders',
            foreignKey: 'user_id',
            localId: $userId,
            orderBy: 'created_at',
            direction: 'DESC',
        );
    }

}
