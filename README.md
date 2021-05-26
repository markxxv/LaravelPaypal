# Laravel Paypal

It's a simple Model + controller to use official PayPal PHP SDK

# First Install PayPal PHP SDK

Add to your composer.json
```"paypal/paypal-checkout-sdk": "^1.0"```

Ther run `composer update`

Now you can grab my PayPal Model, PayPall Controller, and dont forget to add routes:

```
Route::get('/paypal/create', 'PaypalController@create')->name('paypal.create');
Route::any('/paypal/res', 'PaypalController@paymentError')->name('paypal.err');
Route::any('/paypal/ok', 'PaypalController@paymentSuccess')->name('paypal.ok');
```
