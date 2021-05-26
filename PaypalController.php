<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Paypal;
use App\Order;
use Cookie;

class PaypalController extends Controller
{
    public function create()
    {
      $order = Order::where('userid', Cookie::get('userid'))->first();
 
      $paypal = new Paypal;
      $res = $paypal->makePayment($order->id, $order->estimated_price);

      # if error show message, eleif - redirect to PayPal

    }

    public function paymentError(Request $req)
    {
      # if error show message, eleif - redirect to PayPal
      
        return view('payments.error');
      
    }

    public function paymentSuccess(Request $req)
    {
       
      $paypal = new Paypal;

      # Get response
      $res = $paypal->executePayment($req->token);
  
      # if error show message, eleif - redirect to PayPal
      // dd($res);
      // if ($res['status'] == 'error') {
      //   return view('payments.error')->with('message', $req);
      // }

      # Find Order
      if ($res) {
        $order = Order::find($res);
        $order->payment = 'payed';
        $order->payment_system = 'paypal';
        $order->save();

        return view('payments.ok');
      }
      
      
    }
}
