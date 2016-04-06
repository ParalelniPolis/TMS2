<?php

class ForceRegistrationController extends Controller {
	
	function process($parameters) {
		$registration = new Registration();
		if (!$registration->checkIfAdmin($_SESSION['id_user']))
			$this->redirect('error');
		
		//catch registration (button is pressed)
		if (isset($_POST['sent'])) {
			$data = $registration->sanitize([
				"email" => $_POST['email'],
				"tariff" => $_POST['tariff'],
				"firstname" => $_POST['firstname'],
				"surname" => $_POST['surname'],
				"telephone" => $_POST['telephone'],
				'address' => $_POST['address'],
				"startDate" => $_POST['startDate'],
				"ic" => $_POST['ic'],
				//make up password (particulary its hash)
				"p" => $registration->getRandomHash()
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
			//change success message for admin
			if ($result['s'] == 'success')
				$result = [
					's' => 'success',
					'cs' => 'Nový uživatel je úspěšně zaregistrován',
					'en' => 'New member is successfully registred'
				];
			$this->messages[] = $result;
		}
		
		$this->header['title'] = [
			'cs' => 'Registrace nového uživatele',
			'en' => 'Registration of new user'
		];
		$this->data['tariffs'] = $registration->returnTariffsData($this->language);
		$this->view = 'forceRegistration';
	}
}