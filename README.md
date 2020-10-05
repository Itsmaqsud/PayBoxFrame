
Небольшой пример подключения [PayBox](https://github.com/PayBox/PayboxPay) через iframe.

## Демонстрация

Проверить работу можно перейдя по ссылке
https://demo.evocode.pw/

## Использование

0. Установка пакета

```
$ composer require payboxmoney/pay "^1.2"
```


1. PayBoxController

```php
<?php

namespace App\Http\Controllers\PayBox;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paybox\Pay\Facade as Paybox;

class PayBoxController extends Controller
{
  # Объявляем Merchanr ID и Secret key (выдается в ЛК)
  public $merchantId 	= 123456;
  public $secretKey 	= 'wqyroiNsdaq';

  # Настраиваем заказ на оплату и создаем ссылку для iframe
  public function pay(Paybox $paybox) {
    $paybox->merchant->id = $this->merchantId;
    $paybox->merchant->secretKey = $this->secretKey;

  # Текст описания внутри iframe
    $paybox->order->description = 'Paybox test order';
    $paybox->order->amount = 100;
    $paybox->config->paymentRoute = 'frame';

    if($paybox->init()) {
      return [
        'url' => $paybox->redirectUrl,
        'pay_id' =>	$paybox->getServerAnswer()['pg_payment_id']
      ];
    }
  }

  # Получаем статус транзакции
  public function payStatus(Paybox $paybox, Request $request) {
    $paybox->merchant->id = $this->merchantId;
    $paybox->merchant->secretKey = $this->secretKey;

    # Передаем ID транзакции
    $paybox->payment->id = $request->PayId;

    # Полчаем статус
    $paymentStatus = $paybox->getStatus();

    # Обрабатываем статус / https://paybox.money/kz_ru/dev/directory-statuses
    switch ($paymentStatus) {
      case 'partial':
      $paymentStatus = "Вы не оплатили за услугу";
      break;
      case 'pending':
      $paymentStatus = "Платежная транзакция создана и ждет оплаты";
      break;
      case 'ok':
      $paymentStatus = "Платеж завершился успешно";
      break;
      case 'failed':
      $paymentStatus = "Платеж не прошел";
      break;
      case 'revoked':
      $paymentStatus = "Платеж прошел успешно, но затем был отозван";
      break;
      case 'refunded':
      $paymentStatus = "Платеж прошел успешно, но затем был возвращен";
      break;
      case 'incomplete':
      $paymentStatus = "Платеж не был завершен";
      break;
    }

    return [
      'message'	=> $paymentStatus,
      'pay_id'	=> $request->PayId,
    ];
  }

}

```


2. Routes

```php
# For PayBox testing
Route::get('/pay', [PayBoxController::class, 'pay'])->name('paybox');
Route::get('/pay/status', [PayBoxController::class, 'payStatus'])->name('paybox.status');
```

3. JS

```javascript
let PayBoxInit 		= "{{route('paybox')}}";
let PayBoxStatus 	= "{{route('paybox.status')}}";
let PayBoxStatusT 	= $('#getStatus');
var PayBoxPaymentId = null;

// После открытия модального окна, обращаемся к PayBoxInit
$('.modal').on('shown.bs.modal',function(){
	$.get(PayBoxInit, function(data) {
		// Получаем идентификатор транзакции для дальнейшего получения статуса операции
		PayBoxPaymentId = data.pay_id;

		// Вставляем iframe в тело модального окна уже с ссылкой для iframe [pg_payment_route = frame]
		$('.modal-body').html('<iframe src="'+data.url+'" frameborder="0" style="width: 100%; min-height: 680px; height: 100%"></iframe>'); 
	});
});

// После закрытия модального окна проверяем статус платежа
$('.modal').on('hidden.bs.modal',function() {
	// Даем знать о загрузке
	PayBoxStatusT.text('Загрузка...');

	// Обращаемся к PayBoxStatus
	$.get(PayBoxStatus, { PayId: PayBoxPaymentId}).done(function(data) {
		PayBoxStatusT.text(data.message +' №'+ data.pay_id);
	});
});
```
