<?php

class Locks extends Model {
	public function isKeyInDb($key, $placeId) {
		return (($key == 'true') && ($placeId == 'hub'));
	}
	
	public function storeKeyInDb($key) {
		Db::queryModify('INSERT INTO `lock_attempts` (`uid_key`) VALUES (?)', [$key]);
	}
	
	public function sendResponse($result, $placeId) {
		//TODO add header 204
		header('Content-Type: application/json');
		$data[$placeId] = $result;
		echo json_encode($data);
	}
}