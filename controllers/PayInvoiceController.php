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
			$bitcoinPay->newTicket('warning', 'payInvoiceController->user mischmasch violence', 'logged user: '.$_SESSION['id_user'].' is trying something with payment of user id: '.$paymentUserId);
			$this->messages[] = ['s' => 'error',
				'cs' => 'Bohužel nelze platit za jiného člověka',
				'en' => 'Sorry, we can\'t let you pay for another member'];
		} else {
			switch ($case) {
				case ('pay'): {
					$result = $bitcoinPay->requestPaymentStatus($paymentId, $this->language);
					
					switch ($result['paymentType']) {
						case ('new'):
							//get payment data, save it and redirect to payment
							$data = $result['data'];
							$bitcoinPay->updatePayment($paymentId, $data);
							$this->redirectOut($data['payment_url']);
							break;
						
						case ('old'):
						//redirect to old payment (pending, refund etc.)
							$data = $result['data'];
							$this->redirectOut($data['payment_url']);
							break;
						
						default:
							//invoice already payed or error
							$this->messages[] = $result;
							break;
					}
					break;
				}
				
				case ('return'): {
					//first via GET returning status about actual action of user (spoofable, info only for ordinary folks)
					switch ($_GET['bitcoinpay-status']) {
						case ('true'):
							$this->messages[] = ['s' => 'success',
								'cs' => 'Platbu jsme přijali v pořádku',
								'en' => 'Payment was successfully accepted'];
							break;
						
						case ('cancel'):
							$this->messages[] = ['s' => 'info',
								'cs' => 'Platba byla přerušena',
								'en' => 'Payment was interrupted'];
							break;
						
						case ('false'):
						default:
							$bitcoinPay->newTicket('error', 'controller $bitcoinPay->case return->case false', 'error with bitcoinpay payment - something wrong happend');
							$this->messages[] = ['s' => 'error',
								'cs' => 'S platbou se stalo něco zvláštního',
								'en' => 'It\'s something unusual with the payment'];
							break;
					}
					
					//second get status from bitcoinpay.com directly
					$data = $bitcoinPay->getTransactionDetails($bitcoinPay->getBitcoinpayId($paymentId));
					
					if (empty($data)) {
						$this->messages[] = ['s' => 'error',
							'cs' => 'Pardon, nepovedlo se spojení s platebním serverem bitcoinpay.com - zkuste to prosím za pár minut',
						'en' => 'Sorry, could not make the connection with the payment server bitcoinpay.com - please try again in a few minutes'];
					} else {
						//update payment info and show result message
						$bitcoinPay->updatePayment($paymentId, $data);
						$this->messages[] = $bitcoinPay->getStatusMessage($data['status']);
					}
					break;
				}
				
				case ('notify'): {
					//TODO make landing page for bitcoinpay notification status change
				}
				
				default:
					$this->redirect('error');
			}
		}
		//navigate to default view for this action
		$this->redirect('payments');
	}
}