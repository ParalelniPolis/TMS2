<?php

class PythonLockAuthorizeController extends Controller {
	
	public function process($parameters) {
		$locks = new Locks();
		
		$lockName = $locks->sanitize($parameters[0]);
		$key = $locks->sanitize($parameters[1]);
		
		if (empty($key) || empty($lockName)) {
			$result = false;
		} else {
			$result = $locks->isKeyValid($key, $lockName);
			//store only unsuccessfull attempts for later assigmnents
			if ($result == false)
				$locks->storeKeyInDb($key, $lockName);
		}
		
		//munch data into numbers
		if ($result == true)
			$result = 1; else $result = 0;
		$timestamp = time();
		$signature = $locks->generateSignature($key, $lockName, $result, $timestamp, MASTER_LOCK_PASS);
		
		$locks->sendPythonResponse($key, $lockName, $result, $signature, $timestamp);
		//stop rendering
		die();
	}
}