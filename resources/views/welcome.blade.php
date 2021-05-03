<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <style type="text/css">
            .StripeElement {
                box-sizing: border-box;

                height: 40px;

                padding: 10px 12px;
                width: 25%;
                border: 1px solid transparent;
                border-radius: 4px;
                background-color: white;

                box-shadow: 0 1px 3px 0 #e6ebf1;
                -webkit-transition: box-shadow 150ms ease;
                transition: box-shadow 150ms ease;
            }

            .StripeElement--focus {
                box-shadow: 0 1px 3px 0 #cfd7df;
            }

            .StripeElement--invalid {
                border-color: #fa755a;
            }

            .StripeElement--webkit-autofill {
                background-color: #fefde5 !important;
            }

        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div id="paypal-button"></div>
                <br />
                <span>Pay With Stripe</span>
                <div>
                    <form method="post" id="payment-form">
                        <div class="form-row">
                            <label for="card-element">
                                Credit or debit card
                            </label>
                            <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>

                            <!-- Used to display form errors. -->
                            <div id="card-errors" role="alert"></div>
                        </div>
                        <button class="btn btn-primary">Submit Payment</button>
                    </form>
                </div>

            </div>

        </div>
    </body>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript">

        //required variables
        var api_url = "http://127.0.0.1:8000/api/stripe-payment";
        var amount = 60000; //pass amount in centavos example if 100 then pass 10000
        var invoice = "IBL0000"; //in creating invoice prepend IBL to the order_id
        var env = "Sandbox";
        //==================== IMPORTANT PLEASE HIDE THESE DETAILS ====================//
        //
        var publishable_key = "pk_live_L9jA032oYWBP6ErThwxk3xwW";
        if (env == "Sandbox"){
            publishable_key = "pk_test_KbjXHeFVISEE9CJz4zvOO80e";
        }
        //
        //==================== IMPORTANT PLEASE HIDE THESE DETAILS ====================//

        // Create a Stripe client.
        var stripe = Stripe(publishable_key);

        // Create an instance of Elements.
        var elements = stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create an instance of the card Element.
        var card = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>.
        card.mount('#card-element');

        // Handle real-time validation errors from the card Element.
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission.
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Inform the user if there was an error.
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(result.token);
                }
            });
        });

        // Submit the form with the token ID.
        function stripeTokenHandler(token) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');

            //CALL PAYMENT API

            $.ajax({
                type: "POST",
                url: api_url,
                data: {
                    stripeToken:token.id,
                    amount:amount,
                    invoice:invoice,
                    env:env
                },
                dataType: "json",
                encode: true,
            }).done(function (data) {

                //check for data.status == "succeeded" for successful transaction
                //save data.id as our reference to stripe API. This will be used to retrieve orders or refund orders
                console.log(data);
            });



        }
    </script>
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    <script>
        paypal.Button.render({
            env: 'sandbox', // Or 'production'
            style: {
                size: 'large',
                color: 'gold',
                shape: 'pill',
            },
            // Set up the payment:
            // 1. Add a payment callback
            payment: function(data, actions) {
                // 2. Make a request to your server
                return actions.request.post('/api/paypal/create-payment', {

                })
                    .then(function(res) {
                        // 3. Return res.id from the response
                        // console.log(res);
                        return res.id;
                    });
            },
            // Execute the payment:
            // 1. Add an onAuthorize callback
            onAuthorize: function(data, actions) {
                // 2. Make a request to your server
                return actions.request.post('/api/paypal/execute-payment', {

                    paymentID: data.paymentID,
                    payerID:   data.payerID
                })
                    .then(function(res) {
                        console.log(res);

                    });
            }
        }, '#paypal-button');
    </script>
</html>
