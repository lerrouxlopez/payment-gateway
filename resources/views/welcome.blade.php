<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
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
                    <form action="/api/stripe-payment" method="POST">
                        <input type="hidden" name="amount" id="amount" value="10000">
                        <script
                            src="https://checkout.stripe.com/checkout.js"
                            class="stripe-button"
                            data-key="pk_test_KbjXHeFVISEE9CJz4zvOO80e"
                            data-amount= "10000"
                            data-name="Eduardo Arboleda"
                            data-description="SAMPLE CHARGE"
                            data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                            data-locale="auto"
                            data-currency="USD"
                        >

                        </script>
                    </form>
                </div>

            </div>

        </div>
    </body>
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
