<?php

class MakeAdmin extends Model {

	public function checkInputs($newAdminId, $newAdminPlacesId) {
		if (!Db::querySingleOne('SELECT id_user FROM users WHERE id_user = ?', [$newAdminId]))
			return ['s' => 'error',
				'cs' => 'Uživatel nenalezen',
				'en' => 'User not found'];

		foreach ($newAdminPlacesId as $a) {
			if (!Db::querySingleOne('SELECT id FROM places WHERE id = ?', [$a]))
				return ['s' => 'error',
					'cs' => 'Place id '.$a.' nenalezeno',
					'en' => 'Place with id '.$a.' is not found'];
		}
		return ['s' => 'success'];
	}

	public function makeNewAdmin($newAdminId, $newAdminPlacesId) {
		$count = 0;
		foreach ($newAdminPlacesId as $a) {
			if (!Db::queryModify('INSERT INTO admins (user_id, place_id) VALUES (?, ?)',
				[$newAdminId, $a])
			) {
				return ['error', 'částečná chyba - error u admin id: '.$a];
			}
			$count++;
		}
		return ['success', 'Vloženo '.$count.' nových záznamů'];
	}
}