<?php

class PaymentsController extends Controller {

	function process($parameters) {
		$user = new Payments();
		if (!$user->checkLogin()) $this->redirect('error');
		//if empty parameter, add there current user
		if (isset($parameters[0])) $id = $parameters[0]; else $id = $_SESSION['id_user'];

		if ($id != $_SESSION['id_user']) {
			//if not admin of the right place, throw error
			$placesIds = $user->returnAdminPlacesIds();
			$userPlace = $user->getUserPlaceFromId($id);
			if (!in_array($userPlace, $placesIds)) $this->redirect('error');
		}

		$data = $user->getUserData($id);

		//TODO shift this two jobs into cron
		//actualize old payments
		$resultMessages = $user->actualizePayments($data['payments']);
		$this->messages = array_merge($this->messages, $resultMessages);
		//create new payments
		$user->makeNewPayments($data['user'], $data['tariff'], $this->language);

		//get new data for user view
		$data = $user->getUserData($id);
		$data['payments'] = $user->cleanupUserPayments($data['payments'], $data['tariff'], $this->language);
		
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