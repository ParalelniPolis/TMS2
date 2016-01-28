<?php

class CheckUsersController extends Controller {

	function process($parameters) {
		$checkUsers = new CheckUsers();
		$userId = $_SESSION['id_user'];
		if (!$checkUsers->checkIfAdmin($userId)) $this->redirect('error');

		$members = $checkUsers->getMembers($userId, $this->language);

		$this->data['csrf'] = Csrf::getCsrfToken();
		$this->data['activeMemberMailList'] = $checkUsers->getActiveMemberMailList($members);
		$this->data['members'] = $members;
		$this->header['title'] = [
			'cs' => 'Ostatní členové',
			'en' => 'Other members'];
		$this->view = 'checkUsers';
	}
}