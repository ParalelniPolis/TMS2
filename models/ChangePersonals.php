<?php

class ChangePersonals extends Payments {

	public function validateData($data) {
		if (!is_numeric($data['ic'])) return ['s' => 'error',
			'cs' => 'IČ musí být číslo',
			'en' => 'VAT number must be a number'];
		$attempt = Db::queryOne('SELECT `password`,`salt` FROM `users`
                                 WHERE `email` = ?', [$_SESSION['username']]);
		$userPassword = hash('sha512', $data['p'].$attempt['salt']);
		if ($userPassword != $attempt['password'])
			return ['s' => 'error',
				'cs' => 'Současné heslo bylo zadáno nesprávně',
				'en' => 'Incorrect password'];
		return ['s' => 'success'];
	}

	public function changePersonalData($data, $id) {
		$databaseData = [
			$data['firstname'],
			$data['surname'],
			$data['telephone'],
			$data['ic'],
			$id];
		if (!Db::queryModify('
            UPDATE users
            SET `first_name` = ?, `last_name` = ?, `telephone`= ?, `ic`= ?
            WHERE `id_user` = ?', $databaseData)
		)
			return ['s' => 'error',
				'cs' => 'Nepovedlo se zapsat do databáze; zkuste to prosím za pár minut znovu',
				'en' => 'Can\'t access database right now; please try it again later'];
		return ['s' => 'success',
			'cs' => 'Osobní údaje byly úspěšně změněny',
			'en' => 'Personal data was successfully changed'];
	}
}