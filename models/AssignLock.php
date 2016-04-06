<?php

class AssignLock extends Model {
	
	
	public function validateData($userId, $keyId) {
		$result = Db::querySingleOne('SELECT `id_user` FROM `users` WHERE `id_user` = ?', [$userId]);
		if (empty($result))
			return ['s' => 'error',
				'cs' => 'Neexistující ID uživatele',
				'en' => 'ID of member is not valid'];
		
		$result = Db::querySingleOne('SELECT `id` FROM `lock_attempts` WHERE id = ?', [$keyId]);
		if (empty($result))
			return ['s' => 'error',
				'cs' => 'Neexistující ID pokusu o vstup',
				'en' => 'ID of attempt of access is not valid'];
		
		return ['s' => 'success'];
	}
	
	public function assignKey($userId, $keyId) {
		if (!$uidKey = Db::querySingleOne('SELECT `uid_key` FROM `lock_attempts` WHERE `id` = ?', [$keyId])) {
			return ['s' => 'error', 
				'cs' => 'Nepovedlo se vybrat správný záznam přístupu', 
				'en' => 'Sorry, we were not able to take right access record'];
		}
		if (Db::queryModify('UPDATE `users` SET `uid_key` = ? WHERE id_user = ?', [$uidKey, $userId]))
			return ['s' => 'success',
				'cs' => 'Povedlo se přidat právo vstupu',
				'en' => 'Access was successfully assigned']; else return ['s' => 'error',
			'cs' => 'Nepovedlo se přidat práva ke vstupu k uživateli',
			'en' => 'Access was not assigned to a member'];
	}
}