<?php

class RestartPasswordByLink extends Model {
	public function isLinkValid($validationLink) {
		if (empty($validationLink)) return ['error', 'Aktivační klíč je prázdný'];
		$link = Db::querySingleOne('SELECT `validation_string` FROM `restart_password`
                                    JOIN `users` ON `users`.`email` = `restart_password`.`email`
                                    WHERE `validation_string` = ?', [$validationLink]);
		//link is not in database
		if ($link[0] == null) return ['s' => 'error',
			'cs' => 'Link pro validaci není v databázi',
			'en' => 'Link validation is not in the database'];

		$timeOfAttempt = date("Y-m-d H:i:s", time() - CHANGE_PASS_TIME_VALIDITY);
		$restart = Db::queryOne('SELECT `timestamp` FROM `restart_password`
                                 WHERE `validation_string` = ? && `active` = ?', [$validationLink, 1]);
		if ($restart[0] == null) return ['s' => 'error',
			'cs' => 'Link už byl použit. <a href"'.ROOT.'/cs/GetLinkForNewPassword">Získat nový link pro změnu hesla?</a>',
			'en' => 'Link has already been used. <a href"'.ROOT.'/en/GetLinkForNewPassword">Get a new restart password link?</a>'];
		if ($restart['timestamp'] < $timeOfAttempt) return ['s' => 'error',
			'cs' => 'Vypršela časová platnost linku. <a href"'.ROOT.'/cs/GetLinkForNewPassword">Získat nový link pro změnu hesla?</a>',
			'en' => 'Link is timed up. <a href"'.ROOT.'/en/GetLinkForNewPassword">Get a new restart password link?</a>'];
		return ['success'];
	}

	public function invalidateLink($link) {
		Db::queryModify('UPDATE `restart_password` SET `active` = 2
            WHERE `validation_string` = ?
            ORDER BY `timestamp` DESC LIMIT 1', [$link]);
	}

	private function invalidateAttemptsForMail($mail) {
		Db::queryModify('UPDATE `restart_password` SET `active` = 0
            WHERE `email` = ? && `active` = 1', [$mail]);
	}

	public function checkForm($link, $p) {
		$result = Db::queryOne('SELECT `validation_string`,`users`.`email` FROM `restart_password`
            JOIN `users` WHERE `users`.`email` = `restart_password`.`email` && `validation_string` = ?', [$link]);

		//password must be 128 chars long after user-side hashing
		if (strlen($p) != 128) {
			$this->newTicket('problem', $link, 'hash ve funkci zkontrolovatFormular nemá delku 128 znaků - link: '.$link.' a možná přihlášený uživatel: '.$_SESSION['username']);
			return ['s' => 'error',
				'cs' => 'Stalo se něco divného v hashování hesla. Prosím zkuste to znovu',
				'en' => 'An error has occurred in hashing passwords . Please try again'];
		}

		$randomSalt = $this->getRandomHash();
		$saltedPassword = hash('sha512', $p.$randomSalt);
		if (!Db::queryModify('UPDATE `users` SET `password` = ? , `salt` = ?
                              WHERE email = ?', [$saltedPassword, $randomSalt, $result['email']])
		) return ['s' => 'error',
			'cs' => 'Nepovedlo se uložení do databáze. Zkuste to prosím znovu',
			'en' => 'We failed at database save. Try it again please'];

		//success
		$this->invalidateAttemptsForMail($result['email']);
		return ['s' => 'success',
			'cs' => 'Heslo bylo úspěšně změněno',
			'en' => 'Password was changed successfully'];
	}
}