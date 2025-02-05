<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
  public function createPaymentIntent(Request $request)
  {
    Stripe::setApiKey(config('services.stripe.secret'));
    
    try {
      $paymentIntent = PaymentIntent::create([
          'amount' => $request->amount * 100, // Convert to cents
          'currency' => 'usd',
          'payment_method_types' => ['card'],
      ]);

      return response()->json([
          'clientSecret' => $paymentIntent->client_secret
      ]);
    } catch (ApiErrorException $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function processPayment(Request $request)
  {
    $validated = $request->validate([
      'payment_method_id' => 'required',
      'amount' => 'required|numeric',
      'currency' => 'required'
    ]);

    Stripe::setApiKey(config('services.stripe.secret'));

    try {
      $paymentIntent = PaymentIntent::create([
        'amount' => $validated['amount'] * 100,
        'currency' => $validated['currency'],
        'payment_method' => $validated['payment_method_id'],
        'confirmation_method' => 'manual',
        'confirm' => true,
      ]);

      if ($paymentIntent->status === 'succeeded') {
        // Save payment details to your database
        return response()->json([
          'success' => true,
          'payment_intent' => $paymentIntent->id
        ]);
      }

      return response()->json(['error' => 'Payment failed'], 400);

    } catch (ApiErrorException $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}