<?php

class ActualizePaymentsController extends Controller {

	function process($parameters) {
		$payments = new Payments();

		$userIds = $payments->getUsersIds();
		
		foreach ($userIds as $uId) {
			$data = $payments->getUserData($uId);
			//create new payments
			$payments->makeNewPayments($data['user'], $data['tariff'], $data['fakturoid_id'], $this->language);
			//actualize old payments
			$payments->actualizePayments($data['payments']);
			
			//check for expired invoices
			$expiredPayments = $payments->getExpiredPayments(TOLERANCE_TIME_ON_SENDING_REMINDING_EMAILS);
			
			
		}
		header("HTTP/1.0 204 No Content");
		die();
	}
}