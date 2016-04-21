<?php

class PayInvoiceController extends Controller {
	function process($parameters) {
		$bitcoinPay = new Bitcoinpay();
		
		$case = $parameters[0];
		//exception for bitcoinpay notices
		if (!$bitcoinPay->checkLogin() && $case != 'notify')
			$this->redirect('error');
		
		$paymentId = false;
		if (is_numeric($parameters[1]))
			$paymentId = $parameters[1]; else $this->redirect('error');
		
		switch ($case) {
			case ('pay'): {
				//finds out if that payment belongs to logged user. If not, redirect to error
				$paymentUserId = $bitcoinPay->getPaymentUserId($paymentId);
				if ($paymentUserId != $_SESSION['id_user']) {
					$bitcoinPay->newTicket('warning', 'payInvoiceController->user mischmasch violence', 'logged user: '.$_SESSION['id_user'].' is trying something with payment of user id: '.$paymentUserId);
					$this->messages[] = [
						's' => 'error',
						'cs' => 'Bohužel nelze platit za jiného člověka',
						'en' => 'Sorry, we can\'t let you pay for another member'
					];
					$this->redirect('error');
				} else {
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
				}
				break;
			}
			
			case ('return'): {
				//get status from bitcoinpay.com
				$data = $bitcoinPay->getTransactionDetails($bitcoinPay->getBitcoinpayId($paymentId));
				
				if (empty($data)) {
					$this->messages[] = [
						's' => 'error',
						'cs' => 'Pardon, nepovedlo se spojení s platebním serverem bitcoinpay.com - zkuste to prosím za pár minut',
						'en' => 'Sorry, could not make the connection with the payment server bitcoinpay.com - please try again in a few minutes'
					];
				} else {
					//update payment info and show result message
					$bitcoinPay->updatePayment($paymentId, $data);
					$this->messages[] = $bitcoinPay->getStatusMessage($data['status']);
				}
				break;
			}
			
			case ('notify'): {
				$databaseStatus = $bitcoinPay->getPaymentStatus($paymentId);
				
				$rawData = file_get_contents('php://input');
				$rawDataWithPass = $rawData.BITOINPAY_CALLBACK_PASS;
				$dataHash = hash('sha256', $rawDataWithPass);
				
				$headers = apache_request_headers();
				$BPSignature = $headers['BPSignature'];
				
				/*wrong signature
				if ($dataHash != $BPSignature) {
					//$this->redirect('error');
					$this->messages[] = [
						's' => 'info',
						'cs' => 'chyba zabezpečení webu bitcoinpay.com',
						'en' => 'error in security from web bitcoinpay.com'
					];
				}
				/**/
				
				$json = json_decode($rawData);
				$notifedStatus = $bitcoinPay->sanitize($json->status);
				
				/*
				if ($databaseStatus != $notifedStatus)
					$bitcoinPay->updatePaymentStatus($paymentId, $notifedStatus);
				*/
				//TODO debugging bitcoinpay.com
				$bitcoinPay->newTicket('debug', 'bitcoinpayRawData', ($rawDataWithPass));
				$bitcoinPay->newTicket('debug', 'received new status', ($notifedStatus));
				$bitcoinPay->newTicket('debug', 'bitcoinpayDataHash', ($dataHash));
				
				break;
			}
			
			default:
				$this->redirect('error');
		}
		//navigate to default view for this action
		$this->redirect('payments');
	}
}