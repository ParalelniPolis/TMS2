<?php

class PayInvoiceController extends Controller {
	function process($parameters) {
		$bitcoinPay = new Bitcoinpay();
		if (!$bitcoinPay->checkLogin()) $this->redirect('error');
		$case = $parameters[0];
		$paymentId = false;
		if (is_numeric($parameters[1])) $paymentId = $parameters[1]; else $this->redirect('error');

		//finds out if that payment belongs to logged user. If not, redirect to error
		$paymentUserId = $bitcoinPay->getPaymentUserId($paymentId);
		if ($paymentUserId != $_SESSION['id_user']) {
			$bitcoinPay->newTicket('warning', 'payInvoiceController->user michmach violence', 'logged user: '.$_SESSION['id_user'].' is trying something with payment of user id: '.$paymentUserId);
			$this->redirect('error');
		}

		switch ($case) {
			case ('pay'): {
				$data = $bitcoinPay->createPayment($paymentId, $this->language);

				if ($data == false) {
					$this->messages[] = ['s' => 'error',
						'cs' => 'Pardon, nepovedlo se spojení s platebním serverem',
						'en' => 'Sorry, connection to payment server failed'];
					//check if user is trying pay this invoice twice
				} else if ($data['status'] == 'confirmed' || $data['status'] == 'received') {
					$this->messages[] = ['s' => 'info',
						'cs' => 'Faktura již byla zaplacena',
						'en' => 'Invoice is already payed'];
					$this->redirect('payments');
				} else {
					//get payment, save it and redirect to payment
					$bitcoinPay->updatePayment($paymentId, $data);
					$this->redirectOut($data['payment_url']);
				}
				break;
			}

			case ('return'): {
				//first via GET returning status about actual action of user (spoofable, only info for common folks)
				switch ($_GET['bitcoinpay-status']) {
					case ('true'): {
						$this->messages[] = ['s' => 'success',
							'cs' => 'Platbu jsme přijali v pořádku',
							'en' => 'Payment was successfully accepted'];
						break;
					}
					case ('cancel'): {
						$this->messages[] = ['s' => 'info',
							'cs' => 'Platba byla zrušena',
							'en' => 'Payment was canceled'];
						break;
					}
					case ('false'):
					default: {
						$bitcoinPay->newTicket('error', 'controller $bitcoinPay->case return->case false', 'error with bitcoinpay payment - something wrong happend');
						$this->messages[] = ['error',
							'cs' => 'S platbou se stalo něco špatně. Zkuste to prosím znovu za pár minut',
							'en' => 'It\'s something wrong with the payment. Please try it again after couple of minutes'];
						break;
					}
				}

				//second get status from bitcoinpay.com directly
				$data = $bitcoinPay->getTransactionDetails($paymentId);

				if (empty($data)) {
					$this->messages[] = ['s' => 'error',
						'cs' => 'Pardon, nepovedlo se spojení s platebním serverem bitcoinpay.com - zkuste to prosím za pár minut',
						'en' => 'Sorry, we cannot connect payment server bitcoinpay.com - try it again after couple of minutes'];
				} else {
					//update payment info and show result message
					$bitcoinPay->updatePayment($paymentId, $data);
					$this->messages[] = $bitcoinPay->getStatusMessage($data['status']);
				}
				break;
			}

			default:
				$this->redirect('error');
		}
		$this->redirect('payments');
	}
}