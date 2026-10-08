# Changelog — astral-php/astral-payment

## [0.1.1] — 2026-10-08

### Correctif

- `OrderDao` : suppression du `update(): bool` incompatible avec `AbstractDao::update(): int` (fatal PHP au chargement).

## [0.1.0] — 2026-10-04

### Packaging (hub 1.2.5)

- Librairie Composer `astral-php/astral-payment` (namespace `AstralPayment\`).
- PHP **^8.1**, dépendance **astral-core ^1.2** + `stripe/stripe-php`.
- Correctifs FQCN core : `Controller\AbstractController`, `Database\AbstractDao`, `Core\Events\*`.
- Middleware conforme à `MiddlewareInterface::handle(): mixed`.
- Scaffold `App\` retiré ; migrations / routes / vues / JS restent dans `stubs/`.
- Suite PHPUnit (helpers, models, events, listeners, middleware).
