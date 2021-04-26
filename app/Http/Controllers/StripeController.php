<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function payment(Request $request){

        \Stripe\Stripe::setApiKey("sk_test_6dZt5wrzA69cSimJFfFWoEEe");
        $token = $request->stripeToken;
        $charge = \Stripe\Charge::create([
            "amount" => $request->amount,
            "currency"  => "USD",
            "description"   => "Example Charge",
            "source"    => $token
        ]);

        dd($charge);
    }
}
