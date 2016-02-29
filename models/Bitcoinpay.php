<?php

class Bitcoinpay extends Model {
	
	public function requestPaymentStatus($paymentId, $lang) {
		$paymentStatus = Db::querySingleOne('SELECT `status` FROM `payments` WHERE id_payment = ?', [$paymentId]);
		
		switch ($paymentStatus) {
			case ('confirmed'):
			case ('received'):
				//all is shiny
				$result = ['s' => 'info',
					'cs' => 'Faktura již byla zaplacena',
					'en' => 'Invoice has already payed'];
				break;
			
			case ('pending'):
			case ('insufficient_amount'):
			case ('payed_after_timeout'):
				//return stored payment url for payment/refund
				$url = $this->returnPaymentBitcoinPayUrl($paymentId);
				$result = ['s' => 'success',
					'paymentType' => 'old',
					'data' => ['payment_url' => $url]
				];
				break;
			
			case ('unpaid'):
			case ('refund'):
			case ('timeout'):
				//provide new payment
				$data = $this->createPayment($paymentId, $lang);
				if ($data == false) $result = ['s' => 'error',
					'cs' => 'Pardon, nepovedlo se spojení s platebním serverem; zkuste to prosím později',
					'en' => 'Sorry, connection to payment server failed'];
				else $result = ['s' => 'success',
					'paymentType' => 'new',
					'data' => $data];
				break;
			
			case ('invalid'):
				//invalid case better here for disabling silent errors witch generate new payment over the buggy one
			default:
				$this->newTicket('error', 'Payments->actualizePayments', 'unexpected return value (invalid or case missing');
				$result = ['s' => 'error',
					'cs' => 'Pardon, dostali jsme neočekávanou hodnotu z platebního serveru. Zkuste to prosím znovu za pár minut',
					'en' => 'Sorry, we got an unexpected value of the payment server . Please try again in a few minutes'];
				break;
		}
		return $result;
	}
	
	private function createPayment($paymentId, $lang) {
		$payment = $this->getPaymentData($paymentId);
		$idPayer = $payment['id_payer'];
		$priceCZK = $payment['priceCZK'];
		$email = $payment['email'];
		$fakturoidNumber = $payment['invoice_fakturoid_number'];
		
		//make warning ticket if paying user is different from the owner
		if ($email != $_SESSION['username'])
			$this->newTicket('warning', 'function BitcoinPay->TryPayInvoice',
				'users '.$_SESSION['username'].' invoice with id:'.$paymentId.' is payed by:'.$email);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc"); //production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		
		//TODO make valid address /PayInvoice/notify (about change of payment)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $x = '{
            "settled_currency": "BTC",
            "return_url": "'.ROOT.'/'.$lang.'/PayInvoice/return/'.$paymentId.'",
            "notify_url": "'.ROOT.'/'.$lang.'/PayInvoice/notify/'.$paymentId.'",
            "notify_email": "'.EMAIL.'",
            "price": "'.$priceCZK.'",
            "currency": "CZK",
            "reference": {
                "customer_id": "'.$idPayer.'",
                "customer_email": "'.$email.'",
                "payment_id": "'.$paymentId.'",
                "fakturoid_number": "'.$fakturoidNumber.'"
            },
            "item": "Invoice from '.NAME.' in PP",
            "lang": "'.$lang.'"
        }');
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Authorization: Token ".BITCOINPAY_TOKEN
		]);
		
		$response = curl_exec($ch);
		if ($response == false) {
			$this->newTicket('error', 'BitcoinPay curl', 'Error Number:'.curl_errno($ch)."Error String:".curl_error($ch));
			return false;
		}
		
		curl_close($ch);
		$data = json_decode($response, true);
		
		return $data['data'];
	}
	
	public function getTransactionDetails($bitcoinpayId) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/transaction-history/".$bitcoinpayId);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Authorization: Token ".BITCOINPAY_TOKEN
		]);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response, true);
		
		return $data['data'];
	}
	
	public function updatePayment($id, $data) {
		$bitcoinpayId = $data['payment_id'];
		$time = $data['time'];
		$status = $data['status'];
		Db::queryModify('UPDATE `payments` SET `bitcoinpay_payment_id` = ?, `status` = ?, `time_generated` = ?
                         WHERE `id_payment` = ?', [$bitcoinpayId, $status, $time, $id]);
		if (!empty($price = $data['denominated_amount']))
			Db::queryModify('UPDATE `payments` SET `payed_price_BTC` = ?
                WHERE `id_payment` = ?', [$price, $id]);
	}
	
	public function deletePayment($id) {
		$resultExtras = Db::queryModify('DELETE FROM `extras` WHERE `payment_id` = ?', [$id]);
		$resultPayment = Db::queryModify('DELETE FROM `payments` WHERE `id_payment` = ?', [$id]);
		
		if ($resultPayment && $resultExtras) return ['s' => 'success', 
			'cs' => 'Faktura úspěšně smazána', 
			'en' => 'Invoice is successfully deleted'];
		else {
			$this->newTicket('error', 'deletePayment from BitcoinPay.php', 'error in deleting payment with extras');
			return ['s' => 'error', 
				'cs' => 'Neco se stalo špatně - zkus to prosím znovu za pár minut', 
				'en' => 'Something went wrong - try it again in a few minutes please'];
		}
	}
	
	public function getStatusMessage($case) {
		switch ($case) {
			case 'pending': {
				$r = ['s' => 'info',
					'cs' => 'Čekáme na zaplacení',
					'en' => 'Waiting for payment'];
				break;
			}
			case 'confirmed':
			case 'received': {
				$r = ['s' => 'success',
					'cs' => 'Úspěšně zaplaceno. Děkujeme!',
					'en' => 'Successfully payed. Thanks!'];
				break;
			}
			case 'insufficient_amount': {
				$r = ['s' => 'error',
					'cs' => 'Poslána menší částka než je vyžadováno',
					'en' => 'Sent a smaller amount than required'];
				break;
			}
			case 'invalid': {
				$this->newTicket('error', 'BitcoinPay->getStatusMessage', 'returned "invalid" value');
				$r = ['s' => 'error',
					'cs' => 'Bohužel se něco po cestě pokazilo. Ozvěte se nám a dáme to do pořádku',
					'en' => 'Sorry, something wrong on the way. Let us know and we will fix it'];
				break;
			}
			case 'timeout': {
				$r = ['s' => 'info',
					'cs' => 'Platba nebyla zaplacena v daném čase a tak vypršela její platnost',
					'en' => 'Payment was not payed in time and it\'s no longer valid'];
				break;
			}
			case 'paid_after_timeout': {
				$r = ['s' => 'error',
					'cs' => 'Platba byla odeslána po splatnosti',
					'en' => 'Payment was send after timeout'];
				break;
			}
			case 'refund': {
				$r = ['s' => 'info',
					'cs' => 'Platba Vám byla vrácena',
					'en' => 'Payment was refunded'];
				break;
			}
			//internal status (not from BitcoinPay)
			case 'unpaid': {
				$r = ['s' => 'info',
					'cs' => 'Nová nezaplacená faktura',
					'en' => 'New unpaid invoice'];
				break;
			}
			default: {
				$this->newTicket('error', 'BitcoinPay->getStatusMessage', 'unexpected return value: '.$case);
				$r = ['s' => 'error',
					'cs' => 'Nečekaná návratová hodnota z bitcoinpay.com. Víme o tom a fičíme to spravit!',
					'en' => 'Unexpected return value from bitcoinpay.com. We know about it and already on it!'];
			}
		}
		return $r;
	}
	
	private function getPaymentData($paymentId) {
		$payment = Db::queryOne('SELECT `id_payer`,`email`,`priceCZK`,`invoice_fakturoid_number` FROM `payments`
			JOIN `users` ON `users`.`id_user` = `payments`.`id_payer`
			JOIN `tariffs` ON `users`.`user_tariff` = `tariffs`.`id_tariff`
			WHERE `id_payment` = ?', [$paymentId]);
		
		//add sum of all extras
		$extras = Db::queryAll('SELECT `priceCZK` FROM `extras` WHERE `payment_id` = ?', [$paymentId]);
		$extrasSum = 0;
		foreach($extras as $e) $extrasSum += $e['priceCZK'];
		$payment['priceCZK'] += $extrasSum;
		
		return $payment;
	}
	
	public function getPaymentUserId($paymentId) {
		return Db::querySingleOne('SELECT `id_payer` FROM `payments`
            WHERE `id_payment` = ?', [$paymentId]);
	}
	
	private function returnPaymentBitcoinPayUrl($paymentId) {
		return 'https://bitcoinpay.com/cs/sci/invoice/btc/'.$this->getBitcoinpayId($paymentId);
		
	}
	
	public function getBitcoinpayId($paymentId) {
		return Db::querySingleOne('SELECT `bitcoinpay_payment_id` FROM `payments`
            WHERE `id_payment` = ?', [$paymentId]);
	}
}