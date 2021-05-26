<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class Paypal extends Model
{
  public $mode = 'live'; // sandbox | live

  protected $sb_clientId = 'SANDBOX_CLIENT_ID';
  protected $sb_clientSecret = 'SANDBOX_SECRET';

  protected $lv_clientId = 'LIVE_CLIENT_ID';
  protected $lv_clientSecret = 'SANDBOX_SECRET';

  public function __construct()
  {
    if ($this->mode == 'sandbox') {
      $this->environment = new SandboxEnvironment($this->sb_clientId, $this->sb_clientSecret);
    }
    if ($this->mode == 'live') {
      $this->environment = new ProductionEnvironment($this->lv_clientId, $this->lv_clientSecret);
    }
  }

  public function makePayment($orderId, $amount)
  {
      
    $client = new PayPalHttpClient($this->environment);

    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
      "intent" => "CAPTURE",
      "purchase_units" => [[
         "reference_id" => $orderId,
         "amount" => [
             "value" => $amount,
             "currency_code" => "EUR"
         ]
      ]],
      "application_context" => [
          "cancel_url" => route('paypal.err'),
          "return_url" => route('paypal.ok')
      ]
    ];

     try {
        
        $response = $client->execute($request);

        if ($response->statusCode == 201) {
          foreach ($response->result->links as $link) {
            if ($link->rel == 'approve') {
              echo '<a href="'.$link->href.'">PayNow</a>';
              header("Refresh: 0;url=" . $link->href);
            }
          }
        }
     } catch (HttpException $ex) {
        die($ex->getMessage());
        // return [
        //   'status' => 'error',
        //   'message' => $ex->getMessage(),
        // ];
     }
  }

  public function executePayment($token) {
    $client = new PayPalHttpClient($this->environment);

    $request = new OrdersCaptureRequest($token);
    $request->prefer('return=representation');
    try {
        // Call API with your client and get a response for your call
        $response = $client->execute($request);

        // If call returns body in response, you can get the deserialized version from the result attribute of the response
        if ($response->statusCode == 201) {
          return $response->result->purchase_units[0]->reference_id;
        }

    } catch (HttpException $ex) {
      die($ex->getMessage());
    }
  }
}
