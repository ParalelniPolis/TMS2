<?php

class Locks extends Model {
	public function isKeyInDb($key, $placeId) {
		$result = Db::querySingleOne('SELECT `uid_key` FROM `users` 
			JOIN `tariffs` ON `tariffs`.`id_tariff` = `users`.`user_tariff`
			WHERE `uid_key` = ? && `place_id` = ?', 
			[$key, $placeId]);
		return (!$result == false);
		//return (($key == 'true') && ($placeId == 'hub'));
	}
	
	public function storeKeyInDb($key, $placeId) {
		Db::queryModify('INSERT INTO `lock_attempts` (`uid_key`, `place_id`) VALUES (?, ?)', [$key, $placeId]);
	}
	
	public function sendResponse($result, $placeId) {
		header('Content-Type: application/json');
		$data[$placeId] = $result;
		echo json_encode($data);
	}
}