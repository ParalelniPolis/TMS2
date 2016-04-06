<?php

class GetLinkForNewPasswordController extends Controller {
	
	public function process($parameters) {
		$getLinkForNewPassword = new GetLinkForNewPassword();
		
		if (isset($_POST['sent'])) {
			$result = $getLinkForNewPassword->trySendLink($_POST['email'], $_POST["year"], $this->language);
			$this->messages[] = $result;
			$this->view = 'intro';
		} else {
			$this->view = 'getLinkForNewPassword';
		}
		$this->header['title'] = [
			'cs' => 'Restart hesla',
			'en' => 'Restart password'
		];
	}
}