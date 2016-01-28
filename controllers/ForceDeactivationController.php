<?php

class ForceDeactivationController extends Controller {

	public function process($parameters) {
		$deactivation = new Activation();
		$userId = $parameters[0];
		if (!$deactivation->checkIfIsAdminOfUser($_SESSION['id_user'], $userId)) $this->redirect('error');

		$csrfToken = $parameters[1];
		if (!Csrf::validateCsrfRequest($csrfToken)) {
			$this->messages[] = ['s' => 'error',
				'cs' => 'Možný CSRF útok! Zkuste prosím deaktivaci znovu',
				'en' => 'Possible CSRF attack! Please try deactivation again'];
		} else {
			$email = $deactivation->getUserEmailFromId($userId);
			$result = $deactivation->deactivateUser($email);
			$this->messages[] = $result;
		}

		$this->redirect('checkUsers');
	}
}