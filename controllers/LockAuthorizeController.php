<?php

class LockAuthorizeController extends Controller {
	
	public function process($parameters) {
		$locks = new Locks();
		
		$lockName = $locks->sanitize($parameters[0]);
		$key = $locks->sanitize($parameters[1]);
		
		if (empty($key) || empty($lockName)) $result = false;
		else {
			$result = $locks->isKeyValid($key, $lockName);
			//store only unsuccessfull attempts for later assigmnents
			if ($result == false) $locks->storeKeyInDb($key, $lockName);
		}
		
		$locks->sendResponse($result, $lockName);
		//stop rendering
		die();
	}
}