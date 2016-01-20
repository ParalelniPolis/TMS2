<?php

class Activation extends Model {
	public function checkKeyReturnEmail($key) {
		if ($key == null) {
			return ['s' => 'error',
				'cs' => 'Aktivační klíč je prázdný',
				'en' => 'Activation key is empty'];
		}
		$result = Db::queryOne('SELECT `validation_string`,`email` FROM `activation`
                                WHERE `validation_string` = ?', [$key]);
		if ($result[0] == null) {
			return ['s' => 'error',
				'cs' => 'Aktivační klíč není nalezen v databázi',
				'en' => 'Activation key is not in our database'];
		}
		return ['s' => 'success', 'email' => $result['email']];
	}

	public function activateUser($email) {
		if (!Db::queryModify('UPDATE `activation` SET `active` = ?
                              WHERE `email` = ?', [0, $email])
		) {
			return ['s' => 'error',
				'cs' => 'Nepovedlo se zapsat do databáze; zkuste to prosím za pár minut znovu',
				'en' => 'Can\'t access database right now; please try it again later'];
		}
		if (!Db::queryModify('UPDATE `users` SET `active` = ?
                              WHERE `email` = ?', [1, $email])
		) {
			return ['s' => 'error',
				'cs' => 'Nepovedlo se zapsat do databáze; zkuste to prosím za pár minut znovu',
				'en' => 'Can\'t access database right now; please try it again later'];
		}
		return ['s' => 'success',
			'cs' => 'Uživatel '.$email.' úspěšně aktivován',
			'en' => 'User '.$email.'is successfully activated'];
	}

	public function deactivateUser($email) {
		if (!Db::queryModify('UPDATE `users` SET `active` = ?
                              WHERE `email` = ?', [0, $email])
		) {
			return ['s' => 'error',
				'cs' => 'Nepovedlo se zapsat do databáze; zkuste to prosím za pár minut znovu',
				'en' => 'Can\'t access database right now; please try it again later'];
		}
		return ['s' => 'info',
			'cs' => 'Uživatel '.$email.' úspěšně deaktivován',
			'en' => 'User '.$email.'is successfully deactivated'];
	}
}