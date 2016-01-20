<?php

class ActivationController extends Controller {

	public function process($parameters) {
		$activation = new Activation();
		$key = $parameters[0];

		$result = $activation->checkKeyReturnEmail($key);
		if ($result['s'] == 'success') {
			$result = $activation->activateUser($result['email']);
		}

		$this->messages[] = $result;
		$this->header['title'] = [
			'cs' => 'Aktivace účtu',
			'en' => 'Account activation'];
		$this->view = 'activation';
	}
}