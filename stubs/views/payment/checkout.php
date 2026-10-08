<?php
/**
 * views/payment/checkout.php
 *
 * Page de paiement avec Stripe Elements.
 *
 * Variables disponibles :
 *   $order           — AstralPayment\Models\Order
 *   $stripePublicKey — string (pk_test_… ou pk_live_…)
 *   $clientSecret    — string (pi_…_secret_…)
 *   $returnUrl       — string (URL de redirection après paiement)
 *   $amountFormatted — string (ex: "29,99 €")
 */
?>

<div class="max-w-lg mx-auto py-10 px-4">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Finaliser votre paiement</h1>
    <p class="text-gray-500 mb-6">Commande #<?= $order->id ?> — <strong><?= htmlspecialchars($amountFormatted) ?></strong></p>

    <?= $viewEngine->partial('partials/flash') ?>

    {{-- Montant récapitulatif ──────────────────────────────────────────────── --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between text-sm text-gray-600">
            <span>Total à régler</span>
            <span class="font-semibold text-gray-800"><?= htmlspecialchars($amountFormatted) ?></span>
        </div>
    </div>

    {{-- Formulaire Stripe Elements ─────────────────────────────────────────── --}}
    <div id="stripe-checkout"
         data-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
         data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
         data-return-url="<?= htmlspecialchars($returnUrl) ?>">

        {{-- Stripe Elements monte ici ──────────────────────────────────────── --}}
        <div id="payment-element" class="mb-4"></div>

        <div id="payment-message" class="hidden text-red-600 text-sm mb-4"></div>

        <button id="submit-payment"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold
                       py-3 px-6 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="btn-text">Payer <?= htmlspecialchars($amountFormatted) ?></span>
            <span id="btn-spinner" class="hidden">Traitement en cours…</span>
        </button>

    </div>

    <p class="text-xs text-gray-400 text-center mt-4">
        Paiement sécurisé via Stripe. Vos données de carte ne transitent jamais par notre serveur.
    </p>

    <div class="text-center mt-4">
        <a href="/cart" class="text-sm text-indigo-600 hover:underline">← Annuler et revenir au panier</a>
    </div>

</div>

<?php
/**
 * ─────────────────────────────────────────────────────────────────────────────
 * JS Stripe Elements
 *
 * Option A : Vanilla JS (si pas d'astral-vite)
 *   Copier le bloc <script> ci-dessous dans votre layout ou directement ici.
 *
 * Option B : Avec astral-vite (recommandé)
 *   npm install @stripe/stripe-js
 *   Créer resources/js/stripe-checkout.js (fourni dans stubs/)
 *   Le composant lit les data-* du div#stripe-checkout.
 * ─────────────────────────────────────────────────────────────────────────────
 */
?>

{{-- ── Option A : script inline (sans bundler) ─────────────────────────── --}}
<script src="https://js.stripe.com/v3/"></script>
<script>
(async () => {
    const el            = document.getElementById('stripe-checkout');
    const publicKey     = el.dataset.publicKey;
    const clientSecret  = el.dataset.clientSecret;
    const returnUrl     = el.dataset.returnUrl;

    const stripe   = Stripe(publicKey);
    const elements = stripe.elements({ clientSecret });

    const paymentElement = elements.create('payment', {
        layout: 'tabs',
    });
    paymentElement.mount('#payment-element');

    const form      = document.getElementById('submit-payment');
    const btnText   = document.getElementById('btn-text');
    const btnSpinner= document.getElementById('btn-spinner');
    const msgDiv    = document.getElementById('payment-message');

    form.addEventListener('click', async (e) => {
        e.preventDefault();

        form.disabled    = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: returnUrl },
        });

        if (error) {
            msgDiv.textContent = error.message;
            msgDiv.classList.remove('hidden');
            form.disabled = false;
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');
        }
        // Stripe redirige automatiquement vers returnUrl si succès
    });
})();
</script>
