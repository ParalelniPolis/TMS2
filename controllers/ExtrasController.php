<?php

class ExtrasController extends Controller {
	
	function process($parameters) {
		$extras = new Extras();
		$fakturoid = new FakturoidWrapper();
		$action = $extras->sanitize($parameters[0]);
		
		switch ($action) {
			case 'add':
				$paymentId = $extras->sanitize($_POST['paymentId']);
				$price = $extras->sanitize($_POST['price']);
				$description = $extras->sanitize($_POST['description']);
				
				$result = $extras->checkAddValues($paymentId, $price, $description);
				if ($result['s'] == 'success') {
					$status = $extras->getStatusOfPayment($paymentId);
					
					//allow add extra only when new payment will be generated
					if (!in_array($status, ['unpaid', 'refund', 'timeout'])) {
						$this->messages[] = [
							's' => 'error',
							'cs' => 'Bohužel, položka nebyla přidána; platba se právě platí nebo je již zaplacená',
							'en' => 'Sorry, we cannot add an extra; payment is processing'
						];
					}
					else {
						$invoiceFakturoidId = $fakturoid->getFakturoidInvoiceIdFromPaymentId($paymentId);
						$extraFakturoidId = $fakturoid->addExtra($invoiceFakturoidId, $price, $description);
						$result = $extras->addExtra($paymentId, $price, $description, $extraFakturoidId);
						$this->messages[] = $result;
					}
				}
				$this->redirect('checkUsers');
				break;
			
			case 'addBlank':
				$userId = $extras->sanitize($_POST['userId']);
				$price = $extras->sanitize($_POST['price']);
				$description = $extras->sanitize($_POST['description']);
				
				$result = $extras->checkAddBlankValues($userId, $price, $description);
				if ($result['s'] == 'success')
					$this->messages[] = $extras->addBlankExtra($userId, $price, $description);
				else
					$this->messages[] = $result;
				
				$this->redirect('checkUsers');
				break;
			
			case 'delete':
				$extraId = $parameters[1];
				$status = $extras->getStatusOfPaymentFromExtraId($extraId);
				
				//allow add extra only when new payment will be generated or is blank
				if (!in_array($status, ['unpaid', 'refund', 'timeout', null])) {
					$this->messages[] = [
						's' => 'error',
						'cs' => 'Bohužel, položka nebyla zrušena; platba se právě platí nebo je již zaplacená',
						'en' => 'Sorry, we cannot cancel an extra; payment is processing'
					];
				}
				else {
					$extraFakturoidId = $fakturoid->getExtraFakturoidId($extraId);
					$invoiceFakturoidId = $fakturoid->getInvoiceFakturoidIdFromExtraId($extraId);
					$fakturoid->deleteExtra($invoiceFakturoidId, $extraFakturoidId);
					$result = $extras->deleteExtra($extraId);
					$this->messages[] = $result;
				}
				$this->redirect('checkUsers');
				break;
			
			default:
				$this->redirect('error');
		}
	}
}