<?php

class ChangePersonalsController extends Controller {

	function process($parameters) {
		$changePersonals = new ChangePersonals();
		if (!$changePersonals->checkLogin()) $this->redirect('error');
		//if empty parameter, add there current user
		if (isset($parameters[0])) $userId = $parameters[0]; else $userId = $_SESSION['id_user'];

		//if not admin of the right place, throw error
		if ($userId != $_SESSION['id_user'] && !$changePersonals->checkIfIsAdminOfUser($_SESSION['id_user'], $userId)) 
			$this->redirect('error');
		
		//if form is sent
		if (isset($_POST['sent'])) {
			$data = $changePersonals->sanitize([
				'firstname' => $_POST['firstname'],
				'surname' => $_POST['surname'],
				'telephone' => $_POST['telephone'],
				'address' => $_POST['address'],
				'ic' => $_POST['ic'],
				'p' => $_POST['p'],
				'csrf' => $_POST['csrf']
			]);
			if (!Csrf::validateCsrfRequest($data['csrf'])) {
				$this->messages[] = ['s' => 'error',
					'cs' => 'Možný CSRF útok! Zkuste prosím změnit údaje znovu',
					'en' => 'Possible CSRF attack! Please try change your personals again'];
			} else {
				$result = $changePersonals->validateData($data);

				if ($result['s'] == 'success') {
					$result = $changePersonals->changePersonalData($data, $userId);
				}
				$this->messages[] = $result;
			}
		}

		//data for form
		$user = $changePersonals->getUserData($userId, $this->language);
		$this->data = $user['user'];
		$this->data['csrf'] = Csrf::getCsrfToken();
		$this->header['title'] = [
			'cs' => 'Změna osobních údajů',
			'en' => 'Change Personal info'];
		$this->view = 'changePersonals';
	}
}