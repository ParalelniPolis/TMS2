<?php

class Payments extends Model {
	public function getUserData($userId, $lang) {
		$user = Db::queryOne('SELECT `id_user`,`first_name`,`last_name`,`telephone`,`address`,`ic`,`active`,`email`,`name`,`tariffCZE`,`tariffENG`,`invoicing_start_date`
                              FROM `users`
                              JOIN `tariffs` ON `id_tariff` = `user_tariff`
                              JOIN `places` ON `place_id` = `places`.`id`
                              WHERE `id_user` = ?', [$userId]);
		$tariff = Db::queryOne('SELECT `id_tariff`, `priceCZK`,`tariffCZE`,`tariffENG`
                                FROM `users`
                                JOIN `tariffs` ON `users`.`user_tariff` = `tariffs`.`id_tariff`
                                JOIN `places` ON `place_id` = `places`.`id`
                                WHERE  `id_user` = ?', [$userId]);
		$payments = Db::queryAll('SELECT `id_payment`,`bitcoinpay_payment_id`,`id_payer`,`payed_price_BTC`,`payment_first_date`,`status`,`price_CZK`,`invoice_fakturoid_id`
                                  FROM `payments` WHERE `id_payer` = ?
                                  ORDER BY `payment_first_date` DESC', [$userId]);
		//translation for messages
		foreach ($payments as &$p) {
			$p['status'] = $this->translatePaymentStatus($p['status'], $lang);
			if (empty($p['payed_price_BTC'])) $p['payed_price_BTC'] = round($tariff['priceCZK'] / $this->getExchangeRate(), 5);
		}

		return ['user' => $user,
			'tariff' => $tariff,
			'payments' => $payments];
	}

	public function actualizePayments($payments) {
		$bitcoinPay = new Bitcoinpay();
		$fakturoid = new FakturoidWrapper();
		$messages = [];

		foreach ($payments as $payment) {
			$paymentId = $payment['id_payment'];
			$bitcoinpayId = $payment['bitcoinpay_payment_id'];
			$fakturoidId = $payment['invoice_fakturoid_id'];

			if (empty($result['status'])) $result['status'] = 'unpaid';
			else {
				$result = $bitcoinPay->getTransactionDetails($bitcoinpayId);
				//catch invalid response
				if (empty($result)) {
					$messages[] = ['s' => 'info',
						'cs' => 'Nepovedlo se nám spojit se se serverem bitcoinpay.com - některé platby můžou být neaktualizované',
						'en' => 'We failed at connection with bitcoinpay.com - some payments can be outdated'];
				} else {
					$newStatus = $result['status'];
					//when status is different (new), inform user
					if ($newStatus != $payment['status']) {
						Db::queryModify('UPDATE `payments` SET `status` = ? WHERE `id_payment` = ?', [$newStatus, $paymentId]);
						$messages[] = $bitcoinPay->getStatusMessage($newStatus);
						//and when receive money, make invoice payed
						if ($newStatus == 'received' || 'confirmed') {
							$fakturoid->setInvoicePayed($fakturoidId);
							Db::queryModify('UPDATE `payments`
								SET `payed_price_BTC` = ?
								WHERE `id_payment` = ?',
								[$result['price'], $paymentId]);
						}
					}
				}
			}
		}
		return $messages;
	}

	public function makeNewPayments($user, $tariff, $lang) {
		$userId = $user['id_user'];
		$active = $user['active'];
		$currentDate = date('Y-m-d');
		if ($active) {
			$startOfLastGeneratedMonth = Db::querySingleOne('
                SELECT `payment_first_date` FROM `payments`
                WHERE `id_payer` = ?
                ORDER BY `payment_first_date` DESC', [$userId]
			);
			if (empty($startOfLastGeneratedMonth)) {
				//new user
				$startDate = $user['invoicing_start_date'];
				$this->createPayment($user, $tariff, $startDate, $lang);
				return true;
			} else {
				//old user
				$new = false;
				//TODO redesign - generate only payments from today, for example when he came back
				$endOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month - 1 day'));
				while (strtotime($endOfLastGeneratedMonth) < strtotime($currentDate)) {
					$startOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month'));
					$endOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month - 1 day'));
					$this->createPayment($user, $tariff, $startOfLastGeneratedMonth, $lang);
					$new = true;
				}
			}
			if ($new == true) return true; else return false;
		} else return false;
	}

	private function createPayment($user, $tariff, $beginningDate, $lang) {
		$userId = $user['id_user'];
		$tariffId = $tariff['id_tariff'];
		$tariffName = $this->getTariffName($tariffId, $lang);
		$priceCZK = $tariff['priceCZK'];
		$fakturoid = new FakturoidWrapper();
		$fakturoidInvoice = $fakturoid->createInvoice($user, $tariff['priceCZK'], $tariffName, $beginningDate);
		$fakturoidInvoiceId = $fakturoidInvoice->id;
		$fakturoidInvoiceNumber = $fakturoidInvoice->number;
		Db::queryModify('INSERT INTO `payments` (`id_payer`, `payment_first_date`, `status`, `time_generated`, `price_CZK`, `invoice_fakturoid_id`, `invoice_fakturoid_number`)
                         VALUES (?, ?, ?, NOW(), ?, ?, ?)', [$userId, $beginningDate, 'unpaid', $priceCZK, $fakturoidInvoiceId, $fakturoidInvoiceNumber]);
	}

	private function getExchangeRate() {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://bitcoinpay.com/api/v1/rates/btc");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);

		$response = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($response, true);
		foreach ($result as $r) {
			if (array_key_exists('CZK', $r)) return $r['CZK'];
		}
		return false;
	}

	private function translatePaymentStatus($status, $lang) {
		$a = ['pending' => [
			'cs' => 'čekající',
			'en' => 'pending'
		],
			'confirmed' => [
				'cs' => 'potvrzená',
				'en' => 'confirmed'
			],
			'received' => [
				'cs' => 'přijato',
				'en' => 'received'
			],
			'insufficient_amount' => [
				'cs' => 'nedostatečná částka',
				'en' => 'insufficient amount'
			],
			'timeout' => [
				'cs' => 'platnost vypršela',
				'en' => 'timed out'
			],
			'paid_after_timeout' => [
				'cs' => 'zaplaceno pozdě',
				'en' => 'payed after payout'
			],
			'invalid' => [
				'cs' => 'invalid',
				'en' => 'invalid'
			],
			'unpaid' => [
				'cs' => 'nezaplaceno',
				'en' => 'unpaid'
			],
			'refund' => [
				'cs' => 'vráceno',
				'en' => 'refund'
			],
		];
		return $a[$status][$lang];
	}
}