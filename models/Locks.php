<?php

class Locks extends Model {
	public function isKeyValid($key, $lockId) {
		$result = Db::querySingleOne('SELECT `uid_key` FROM `users` 
			JOIN `tariffs` ON `tariffs`.`id_tariff` = `users`.`user_tariff`
			JOIN `places` ON `places`.`id` = `tariffs`.`place_id`
			JOIN `locks` ON `locks`.`id_place` = `places`.`id`
			WHERE `uid_key` = ? && `id_lock` = ?', 
			[$key, $lockId]);
		return (!empty($result));
	}
	
	public function storeKeyInDb($key, $lockId) {
		$lockName = $this->returnLockName($lockId);
		Db::queryModify('INSERT INTO `lock_attempts` (`uid_key`, `lock_name`) VALUES (?, ?)', [$key, $lockName]);
	}
	
	public function sendResponse($result, $lockId) {
		header('Content-Type: application/json');
		$data[$lockId] = $result;
		echo json_encode($data);
	}
	
	private function returnLockName($lockId) {
		return Db::querySingleOne('SELECT `lock_name` FROM `locks` 
			WHERE `id_lock` = ?', [$lockId]);
	}
}