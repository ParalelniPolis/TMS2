<?php

class ForceRegistrationController extends Controller {

	function process($parameters) {
		$registration = new Registration();
		if (empty($registration->returnAdminPlacesIds())) $this->redirect('error');

		//catch registration (button is pressed)
		if (isset($_POST['sent'])) {
			$data = $registration->sanitize([
				"email" => $_POST['email'],
				"tariff" => $_POST['tariff'],
				"firstname" => $_POST['firstname'],
				"surname" => $_POST['surname'],
				"telephone" => $_POST['telephone'],
				"startDate" => $_POST['startDate'],
				"ic" => $_POST['ic'],
				//make up password (especially its hash)
				"p" => $registration->getRandomHash()
			]);
			$this->data = $data; //for autofilling from previous page

			$result = $registration->validateData($data);
			if ($result['s'] == 'success') {
				$result = $registration->registerUser($data, $this->language);
			}

			$this->messages[] = $result;
		}

		$this->header['title'] = [
			'cs' => 'Registrace nového uživatele',
			'en' => 'Registration of new user'];
		$this->data['tariffs'] = $registration->returnMenuTariffs($this->language);
		$this->view = 'forceRegistration';
	}
}