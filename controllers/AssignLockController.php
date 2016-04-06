<?php

class AssignLockController extends Controller {
	
	function process($parameters) {
		$assignLock = new AssignLock();
		if (empty($parameters[0] || $parameters[1])) {
			//values is not set
			$this->redirect('error');
		} else {
			//values are set
			$userId = $assignLock->sanitize($parameters[0]);
			$keyId = $assignLock->sanitize($parameters[1]);
			if (!$assignLock->checkIfIsAdminOfUser($_SESSION['id_user'], $userId))
				$this->redirect('error');
			
			$result = $assignLock->validateData($userId, $keyId);
			if ($result['s'] == 'success') {
				$result = $assignLock->assignKey($userId, $keyId);
			}
			$this->messages[] = $result;
			$this->header['title'] = [
				'cs' => 'Přiřadit přístup',
				'en' => 'Assign an access'
			];
			$this->redirect('checkUsers');
		}
	}
}