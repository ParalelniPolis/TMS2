<?php

class PaymentsController extends Controller {

	function process($parameters) {
		$payments = new Payments();
		if (!$payments->checkLogin()) $this->redirect('error');
		//if empty parameter, add there current user
		if (isset($parameters[0])) $userId = $parameters[0]; else $userId = $_SESSION['id_user'];

		if ($userId != $_SESSION['id_user'] && !$payments->checkIfIsAdminOfUser($_SESSION['id_user'], $userId)) 
			$this->redirect('error');

		$data = $payments->getUserData($userId);

		//TODO shift this two jobs into cron
		//create new payments
		if ($payments->makeNewPayments($data['user'], $data['tariff'], $this->language))
			//actualize data if invoice was generated
			$data = $payments->getUserData($userId);
		//actualize old payments
		$resultMessages = $payments->actualizePayments($data['payments']);
		$this->messages = array_merge($this->messages, $resultMessages);
		

		//get new data for user view
		$data = $payments->getUserData($userId);
		$data['payments'] = $payments->cleanupUserPayments($data['payments'], $data['tariff'], $this->language);
		
		//display non-active user
		if (!$data['user']['active']) $this->messages[] = ['s' => 'info',
			'cs' => 'Neaktivní uživatel - nové faktury se negenerují',
			'en' => 'Inactive user - new invoices are not generated'];

		$this->data['tariff'] = $data['tariff'];
		$this->data['user'] = $data['user'];
		$this->data['payments'] = $data['payments'];
		$this->header['title'] = [
			'cs' => 'Přehled plateb',
			'en' => 'Payments overview'];
		//TODO add nice sliding JS invoice detail directly into view
		$this->view = 'payments';
	}
}