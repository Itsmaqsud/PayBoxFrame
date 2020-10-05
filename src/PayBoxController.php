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
