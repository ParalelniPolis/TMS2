<?php

class LockAuthorizeController extends Controller {
	public function process ($parameters) {
		
		if (empty($parameters[0])) $result['access'] = false;
		else $result['access'] = $this->isKeyInDb($parameters[0]);
		
		//send response
		header('Content-Type: application/json');
		echo json_encode($result);
		die();
	}
	
	private function isKeyInDb($key) {
		return ($key == 'true');
	}
}