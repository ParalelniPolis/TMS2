<?php

class LockAuthorizeController extends Controller {
	
	public function process($parameters) {
		$locks = new Locks();
		$key = $locks->sanitize($parameters[0]);
		$placeId = $locks->sanitize($parameters[1]);
		
		if (empty($key) || empty($placeId)) $result = false;
		else {
			$result = $locks->isKeyInDb($key, $placeId);
			//dont store info when empty 
			if ($result == false) $locks->storeKeyInDb($key);
		}
		
		$locks->sendResponse($result, $placeId);
		//stop rendering
		die();
	}
}