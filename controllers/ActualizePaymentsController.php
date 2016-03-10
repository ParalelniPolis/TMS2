<?php

class ActualizePaymentsController extends Controller {

	function process($parameters) {
		$payments = new Payments();

		$userIds = $payments->getUsersIds();
		
		foreach ($userIds as $uId) {
			$data = $payments->getUserData($uId);
			
			//actualize old payments
			$payments->actualizePayments($data['payments']);
			//create new payments
			$payments->makeNewPayments($data['user'], $data['tariff'], $this->language);
			
			//check for expired invoices
			$expiredPayments = $payments->getExpiredPayments(TOLERANCE_TIME_ON_SENDING_REMINDING_EMAILS);
			//TODO resolve expired invoices
			
		}
		header("HTTP/1.0 204 No Content");
		die();
	}
}