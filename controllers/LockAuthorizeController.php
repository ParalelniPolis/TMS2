<?php

class LockAuthorizeController extends Controller {
	
	public function process($parameters) {
		$locks = new Locks();
		
		$lockId = $locks->sanitize($parameters[0]);
		$key = $locks->sanitize($parameters[1]);
		
		if (empty($key) || empty($lockId)) $result = false;
		else {
			$result = $locks->isKeyValid($key, $lockId);
			//store only unsuccessfull attempts for later assigmnents
			if ($result == false) $locks->storeKeyInDb($key, $lockId);
		}
		
		$locks->sendResponse($result, $lockId);
		//stop rendering
		die();
	}
}