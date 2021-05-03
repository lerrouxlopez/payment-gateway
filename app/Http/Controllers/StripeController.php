<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function payment(Request $request){
        $key = "sk_live_h97wOiqhmU87ThLGU6q5WJu2";
        if($request->env == "Sandbox"){
            $key = "sk_test_6dZt5wrzA69cSimJFfFWoEEe";
        }

        \Stripe\Stripe::setApiKey($key);
        $token = $request->stripeToken;
        $charge = \Stripe\Charge::create([
            "amount" => $request->amount,
            "currency"  => "USD",
            "description"   => $request->invoice,
            "source"    => $token
        ]);

        echo json_encode($charge);

    }
}
