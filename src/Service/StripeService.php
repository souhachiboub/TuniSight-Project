<?php



namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


class StripeService
{
    private string $secretKey;

    public function __construct(
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] string $secretKey
    ) {
        $this->secretKey = $secretKey;
    }

    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl): Session
    {
        Stripe::setApiKey($this->secretKey);
    
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems, // On passe le tableau directement ici, pas un tableau imbriqué
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
    
}
