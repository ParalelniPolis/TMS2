<?php

class ForceActivationController extends Controller {
	
	public function process($parameters) {
		$activation = new Activation();
		$csfr = new Csrf();
		$userId = $parameters[0];
		if (!$activation->checkIfIsAdminOfUser($_SESSION['id_user'], $userId)) $this->redirect('error');
		
		if (isset($_POST['sent'])) {
			if (!Csrf::validateCsrfRequest($_POST['csrf'])) {
				$this->messages[] = ['s' => 'error',
					'cs' => 'Možný CSRF útok! Zkuste prosím aktivaci znovu',
					'en' => 'Possible CSRF attack! Please try activation again'];
				$this->redirect('error');
			}
			
			$tariffId = $_POST['tariff'];
			$result = $activation->validateTariffId($tariffId);
			if ($result['s'] == 'success') {
				$result = $activation->forceActivateUser($activation->getUserEmailFromId($userId), $tariffId);
			}
			$this->messages[] = $result;
			$this->redirect('checkUsers');
		}
		
		$this->data['csrf'] = $csfr->getCsrfToken();
		$this->data['tariffs'] = $activation->returnTariffsData($this->language);
		$this->header['title'] = [
			'cs' => 'Aktivace uživatele',
			'en' => 'User activation'];
		$this->view = 'forceActivation';
	}
}