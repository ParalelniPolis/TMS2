<?php

class ForceActivationController extends Controller {
	
	public function process($parameters) {
		$activation = new Activation();
		$csfr = new Csrf();
		$userId = $parameters[0];
		if (!$activation->checkIfIsAdminOfUser($_SESSION['id_user'], $userId))
			$this->redirect('error');
		
		if (isset($_POST['sent'])) {
			if (!Csrf::validateCsrfRequest($_POST['csrf'])) {
				$this->messages[] = [
					's' => 'error',
					'cs' => 'Možný CSRF útok! Zkuste prosím aktivaci znovu',
					'en' => 'Possible CSRF attack! Please try activation again'
				];
				$this->redirect('error');
			}
			
			$tariffId = $activation->sanitize($_POST['tariff']);
			$startDate = $activation->sanitize($_POST['startDate']);
			$result = $activation->validateForceActivationData($tariffId, $startDate);
			if ($result['s'] == 'success') {
				//TODO resolve invoice total sum conflict (different total when change tariff in the middle)
				$result = $activation->forceActivateUser($activation->getUserEmailFromId($userId), $tariffId, $startDate);
			}
			$this->messages[] = $result;
			
			if ($result['s'] == 'success')
				$this->redirect('payments/'.$userId);
		}
		
		$this->data['csrf'] = $csfr->getCsrfToken();
		$this->data['tariffs'] = $activation->returnTariffsData($this->language);
		$this->header['title'] = [
			'cs' => 'Aktivace uživatele',
			'en' => 'User activation'
		];
		$this->view = 'forceActivation';
	}
}