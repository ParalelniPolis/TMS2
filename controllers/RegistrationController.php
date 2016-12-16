<?php

class RegistrationController extends Controller {
	
	function process($parameters) {
		$registration = new Registration();
		if ($registration->checkLogin())
			$this->redirect('error');
		
		//catch registration (button is pressed)
		if (isset($_POST['sent'])) {
			$data = $registration->sanitize([
				'email' => $_POST['email'],
				'tariff' => $_POST['tariff'],
				'firstname' => $_POST['firstname'],
				'surname' => $_POST['surname'],
				'telephone' => $_POST['telephone'],
				'address' => $_POST['address'],
				'startDate' => $_POST['startDate'],
				'ic' => $_POST['ic'],
				'company' => $_POST['company'],
				'p' => $_POST['p']
			]);
			$this->data = $data; //for autofilling from previous page
			
			$result = $registration->validateData($data);
			if ($result['s'] == 'success') {
				$fakturoid = new FakturoidWrapper();
				$newCustomer = $fakturoid->createCustomer($data);
				if ($newCustomer == false) {
					$result = [
						's' => 'error',
						'cs' => 'Bohužel se nepovedlo uložit data do Faktuoidu; zkus to prosím za pár minut',
						'en' => 'Sorry, we didn\'n safe your data into Fakturoid; try it again after a couple of minutes please'
					];
				} else {
					//add fakturoid_id into data structure
					$data['fakturoid_id'] = $newCustomer->id;
					$result = $registration->registerUser($data, $this->language);
				}
			}
			
			$this->messages[] = $result;
			//if register success, show registration form no more
			if ($result['s'] == 'success')
				$this->redirect('');
		}
		
		$this->header['title'] = [
			'cs' => 'Registrace nového uživatele',
			'en' => 'New user registration'
		];
		$this->data['tariffs'] = $registration->returnTariffsData($this->language);
		$this->view = 'registration';
	}
}