# astral-php/astral-payment

Intégration Stripe pour [Astral](https://github.com/astral-php) — PaymentIntent, webhooks, events, remboursements.

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Stripe](https://img.shields.io/badge/Stripe-SDK-635BFF?logo=stripe&logoColor=white)](https://stripe.com)
[![Tests](https://img.shields.io/badge/tests-PHPUnit%2010%2B-9933CC)](./phpunit.xml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Prérequis

| Dépendance | Version |
|---|---|
| PHP | ^8.1 |
| astral-php/astral-core | ^1.2 |
| stripe/stripe-php | ^13+ |
| Compte Stripe | [stripe.com](https://stripe.com) |

---

## Installation

```bash
composer require astral-php/astral-payment
```

### 1. Variables d'environnement

Copier `stubs/.env.payment.example` dans votre `.env` :

```env
STRIPE_PUBLIC_KEY=pk_test_…
STRIPE_SECRET_KEY=sk_test_…
STRIPE_WEBHOOK_SECRET=whsec_…
APP_URL=http://localhost
```

### 2. Service provider

Dans `config/dependencies.php` :

```php
AstralPayment\PaymentServiceProvider::class,
```

### 3. Migrations

Copier `stubs/database/migrations/*` vers `database/migrations/`, puis :

```bash
php bin/console migrate
```

### 4. Routes

Fusionner `stubs/config/routes.payment.php` dans `config/routes.php`
(checkout sous `AuthMiddleware`, webhook **sans** CSRF).

### 5. Vue + JS

- `stubs/views/payment/checkout.php` → `views/payment/`
- `stubs/resources/js/stripe-checkout.js` → `resources/js/` (si astral-vite)
- `npm install @stripe/stripe-js`

---

## Usage rapide

```php
use AstralPayment\PaymentService;

$data = $paymentService->createPaymentIntent(
    amountCents: 2999,
    currency: 'eur',
    metadata: ['order_id' => $orderId],
);
// $data['clientSecret'], $data['intentId']
```

Flux : checkout → Stripe Elements → webhook `payment_intent.succeeded` →
events `PaymentSucceeded` / `PaymentFailed`.

---

## Structure

```
src/
  PaymentService.php
  PaymentServiceProvider.php
  Controllers/
  Dao/
  Events/
  Listeners/
  Middleware/
  Models/
stubs/
  .env.payment.example
  config/routes.payment.php
  database/migrations/
  views/payment/
  resources/js/
```

---

## Tests

```bash
composer test
# ou
vendor/bin/phpunit
```

Suite obligatoire avant publication (helpers, models, events, listeners, middleware).

---

## Compatibilité

| astral-core | PHP | astral-payment |
|---|---|---|
| ^1.2 | 8.1 → 8.4 | 0.x |

---

## Licence

MIT — voir [LICENSE](./LICENSE)
