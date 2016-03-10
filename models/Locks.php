<?php

class Locks extends Model {
	public function isKeyValid($key, $lockName) {
		$result = Db::querySingleOne('SELECT `uid_key` FROM `users`
			JOIN `tariffs` ON `tariffs`.`id_tariff` = `users`.`user_tariff`
			JOIN `places` ON `places`.`id` = `tariffs`.`place_id`
			JOIN `locks` ON `locks`.`id_place` = `places`.`id`
			WHERE `uid_key` = ? && `lock_name` = ?',
			[$key, $lockName]);
		if ($result) return true;
		else return false;
	}
	
	public function storeKeyInDb($key, $lockName) {
		Db::queryModify('INSERT INTO `lock_attempts` (`uid_key`, `lock_name`) VALUES (?, ?)', [$key, $lockName]);
	}
	
	public function sendResponse($result, $lockName) {
		header('Content-Type: application/json');
		$data[ $lockName ] = $result;
		echo json_encode($data);
	}
}