<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Api\WebProfile;
use PayPal\Api\InputFields;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;

class PayPalController extends Controller
{
    public function createPayment(Request $request){
        $products = json_decode($request->products,1);

        if($request->env == "sandbox"){
            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    'ATMWXycDhqex03Q5ugXB2ZNOelJzEj4-U5VZeWn0Edqp-a-FZdV3BfxjIzsZZb5M9mRxazxRRlzLnfM8',     // ClientID
                    'EHvVI4LV0oWYKjxqur9n60gWbjQdu0ZLDZyHD9rgzsXsLlW-zZjINXFX3NvD_GZOu6ZWoZ2_331q6Euf'      // ClientSecret
                )
            );
        }else{
            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    'AQ7v-KloOTY3jvQVG9A-s3RDNQOJrjPGcBUWvzI1XZtnStmGgMisCdOa_vxCbQvUxBNqWiwszRwDxmKO',     // ClientID
                    'EI5S6kTxJQir4JfXrouYX2QGzIwm-qvqJlrCHXRs91OBMVtoXoESI_9ICH2fCZaF9YLBILspNrwNCOuM'      // ClientSecret
                )
            );
            $apiContext->setConfig(
                array(
                    'log.LogEnabled' => true,
                    'log.FileName' => 'PayPal.log',
                    'log.LogLevel' => 'DEBUG',
                    'mode' => 'live'
                )
            );

        }

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        $total = 0;
        $items = array();
        foreach ($products as $key => $product){
            ${"item".$key} = new Item();
            ${"item".$key}->setName($product['name'])
                ->setCurrency($product['currency'])
                ->setQuantity($product['quantity'])
                ->setSku($product['sku']) // Similar to `item_number` in Classic API
                ->setPrice($product['price']);
            $total += ($product['quantity'] * $product['price']);
            $items[]=${"item".$key};
        }

        $itemList = new ItemList();
        $itemList->setItems($items);

        $details = new Details();
        $details->setShipping(0)
            ->setTax(0)
            ->setSubtotal($total);

        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber("IBL".uniqid());
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($request->return_url)
            ->setCancelUrl($request->cancel_url);

        // Add NO SHIPPING OPTION
        $inputFields = new InputFields();
        $inputFields->setNoShipping(1);

        $webProfile = new WebProfile();
        $webProfile->setName('IBL' . uniqid())->setInputFields($inputFields);

        $webProfileId = $webProfile->create($apiContext)->getId();

        $payment = new Payment();
        $payment->setExperienceProfileId($webProfileId); // no shipping
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {
            echo $ex;
            exit(1);
        }

        return $payment;
    }

    public function executePayment(Request $request){
        if($request->env == "sandbox"){
            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    'ATMWXycDhqex03Q5ugXB2ZNOelJzEj4-U5VZeWn0Edqp-a-FZdV3BfxjIzsZZb5M9mRxazxRRlzLnfM8',     // ClientID
                    'EHvVI4LV0oWYKjxqur9n60gWbjQdu0ZLDZyHD9rgzsXsLlW-zZjINXFX3NvD_GZOu6ZWoZ2_331q6Euf'      // ClientSecret
                )
            );
        }else{

            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    'AQ7v-KloOTY3jvQVG9A-s3RDNQOJrjPGcBUWvzI1XZtnStmGgMisCdOa_vxCbQvUxBNqWiwszRwDxmKO',     // ClientID
                    'EI5S6kTxJQir4JfXrouYX2QGzIwm-qvqJlrCHXRs91OBMVtoXoESI_9ICH2fCZaF9YLBILspNrwNCOuM'      // ClientSecret
                )
            );
            $apiContext->setConfig(
                array(
                    'log.LogEnabled' => true,
                    'log.FileName' => 'PayPal.log',
                    'log.LogLevel' => 'DEBUG',
                    'mode' => 'live'
                )
            );

        }


        $paymentId = $request->paymentID;
        $payment = Payment::get($paymentId, $apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($request->payerID);

        try {
            $result = $payment->execute($execution, $apiContext);
        } catch (Exception $ex) {
            echo $ex;
            exit(1);
        }

        return $result;
    }
}
