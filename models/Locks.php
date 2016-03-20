<?php

class Locks extends Model {
	public function isKeyValid($key, $lockName) {
		$result = Db::querySingleOne('SELECT `uid_key` FROM `users`
			JOIN `tariffs` ON `tariffs`.`id_tariff` = `users`.`user_tariff`
			JOIN `places` ON `places`.`id` = `tariffs`.`place_id`
			JOIN `locks` ON `locks`.`id_place` = `places`.`id`
			WHERE `uid_key` = ? && `lock_name` = ?', [$key, $lockName]);
		if ($result)
			return true; else return false;
	}
	
	public function storeKeyInDb($key, $lockName) {
		Db::queryModify('INSERT INTO `lock_attempts` (`uid_key`, `lock_name`) VALUES (?, ?)', [$key, $lockName]);
	}
	
	public function sendResponse($result, $lockName) {
		header('Content-Type: application/json');
		$data[$lockName] = $result;
		echo json_encode($data);
	}
	
	public function sendPythonResponse($hashUid, $lockName, $result, $signature, $timestamp) {
		header('Content-Type: application/json');
		$data = ["hash_uid" => $hashUid, 
			"lock_name" => $lockName,
			"result" => $result,
			"timestamp" => $timestamp,
			"sig" => $signature];
		echo json_encode($data);
	}
	
	public function generateSignature($hashUid, $lockName, $result, $timestamp, $masterLockPassword) {
		$data = implode(';', [$hashUid, $lockName, $result, $timestamp, $masterLockPassword]);
		
		return hash('sha256', $data);
	}
}