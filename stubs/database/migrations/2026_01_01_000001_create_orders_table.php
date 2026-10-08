<?php
declare(strict_types=1);

use Database\Migration\Migration;

/**
 * Migration : create_orders_table
 *
 * Génère : php bin/console make:migration create_orders_table
 * Applique : php bin/console migrate
 */
class CreateOrdersTable extends Migration
{
    public function up(\PDO $pdo): void
    {
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS orders (
                    id                INTEGER  PRIMARY KEY AUTOINCREMENT,
                    user_id           INTEGER  NOT NULL,
                    total_cents       INTEGER  NOT NULL DEFAULT 0,
                    currency          TEXT     NOT NULL DEFAULT 'eur',
                    status            TEXT     NOT NULL DEFAULT 'pending',
                    stripe_intent_id  TEXT,
                    created_at        TEXT     NOT NULL DEFAULT (datetime('now'))
                )
            SQL);
        } else {
            $pdo->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS `orders` (
                    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
                    `user_id`           INT UNSIGNED   NOT NULL,
                    `total_cents`       INT UNSIGNED   NOT NULL DEFAULT 0,
                    `currency`          VARCHAR(3)     NOT NULL DEFAULT 'eur',
                    `status`            VARCHAR(20)    NOT NULL DEFAULT 'pending'
                                        COMMENT 'pending|paid|failed|refunded',
                    `stripe_intent_id`  VARCHAR(255)   DEFAULT NULL,
                    `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_orders_user_id` (`user_id`),
                    KEY `idx_orders_status`  (`status`),
                    UNIQUE KEY `uk_orders_intent` (`stripe_intent_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS orders');
    }
}
