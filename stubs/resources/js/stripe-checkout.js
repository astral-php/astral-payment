/**
 * resources/js/stripe-checkout.js
 *
 * Island Vue / Vanilla JS pour Stripe Elements avec astral-vite.
 *
 * Installation :
 *   npm install @stripe/stripe-js
 *
 * Dans vite.config.js, ajouter ce fichier comme entry point si nécessaire,
 * ou l'importer depuis app.js si vous préférez un bundle unique.
 *
 * Dans la vue PHP :
 *   <div id="stripe-checkout"
 *        data-public-key="<?= $stripePublicKey ?>"
 *        data-client-secret="<?= $clientSecret ?>"
 *        data-return-url="<?= $returnUrl ?>">
 *     <div id="payment-element"></div>
 *     <button id="submit-payment">Payer</button>
 *     <div id="payment-message" class="hidden"></div>
 *   </div>
 */

import { loadStripe } from '@stripe/stripe-js';

const mount = async () => {
    const el = document.getElementById('stripe-checkout');
    if (!el) return; // page sans checkout → on ne fait rien

    const publicKey    = el.dataset.publicKey;
    const clientSecret = el.dataset.clientSecret;
    const returnUrl    = el.dataset.returnUrl;

    if (!publicKey || !clientSecret) {
        console.error('[astral-payment] Données Stripe manquantes (data-public-key, data-client-secret)');
        return;
    }

    const stripe   = await loadStripe(publicKey);
    const elements = stripe.elements({ clientSecret });

    // Monter Stripe Payment Element
    const paymentEl = elements.create('payment', { layout: 'tabs' });
    paymentEl.mount('#payment-element');

    // Bouton de soumission
    const btn      = document.getElementById('submit-payment');
    const btnText  = btn?.querySelector('[data-btn-text]')   ?? btn;
    const spinner  = btn?.querySelector('[data-btn-spinner]');
    const msgDiv   = document.getElementById('payment-message');

    const setLoading = (loading) => {
        if (btn)     btn.disabled = loading;
        if (spinner) spinner.classList.toggle('hidden', !loading);
        if (btnText && btnText !== btn) btnText.classList.toggle('hidden', loading);
        if (msgDiv)  msgDiv.classList.add('hidden');
    };

    btn?.addEventListener('click', async (e) => {
        e.preventDefault();
        setLoading(true);

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: returnUrl },
        });

        if (error) {
            if (msgDiv) {
                msgDiv.textContent = error.message ?? 'Une erreur est survenue.';
                msgDiv.classList.remove('hidden');
            }
            setLoading(false);
        }
        // En cas de succès, Stripe redirige automatiquement vers returnUrl
    });
};

// Auto-mount au DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
} else {
    mount();
}
