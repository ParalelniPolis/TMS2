<?php

class ForceActivationController extends Controller {

	public function process($parameters) {
		$activation = new Activation();
		$userId = $parameters[0];
		if (!$activation->checkIfIsAdminOfUser($_SESSION['id_user'], $userId)) $this->redirect('error');

		$csrfToken = $parameters[1];
		if (!Csrf::validateCsrfRequest($csrfToken)) {
			$this->messages[] = ['s' => 'error',
				'cs' => 'Možný CSRF útok! Zkuste prosím aktivaci znovu',
				'en' => 'Possible CSRF attack! Please try activation again'];
		} else {
			//TODO add place choice for acivated member
			$email = $activation->getUserEmailFromId($userId);
			$result = $activation->activateUser($email);
			$this->messages[] = $result;
		}

		$this->redirect('checkUsers');
	}
}