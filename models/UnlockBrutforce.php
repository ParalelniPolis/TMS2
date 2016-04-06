<?php

class UnlockBrutforce extends Model {
	
	public function checkKeyReturnEmail($key) {
		if ($key == null)
			return [
				's' => 'error',
				'cs' => 'Brutforce klíč je prázdný',
				'en' => 'Brutforce key is blank'
			];
		$result = Db::queryOne('SELECT `validation_string`,`email` FROM `restart_brutforce`
            WHERE `active` = 1 && `validation_string` = ?', [$key]);
		if ($result[0] == null)
			return [
				's' => 'error',
				'cs' => 'Bohužel nesouhlasí aktivační klíč',
				'en' => 'Activation key is not valid'
			]; else return $result['email'];
	}
	
	public function unlockFiveAttempts($email) {
		//unlock last five attempts
		if (!Db::queryModify('UPDATE `login_attempts` SET `success` = ?
                             WHERE `login` = ? ORDER BY `timestamp` DESC LIMIT 5', [2, $email])
		)
			return [
				's' => 'error',
				'cs' => 'Bohužel se nepovedlo odblokování brutforce systému. Zkus to prosím znovu',
				'en' => 'Unfortunately, we failed to unblock brutforce system. Please try again'
			];
		//nvalidate all others brutforce links
		if (!Db::queryModify('UPDATE `restart_brutforce` SET `active` = ?
                              WHERE `email` = ?', [0, $email])
		) {
			$this->newTicket('problem', $email, 'nepovedlo se invalidovat platné linky po úspěšném brutforcu ve funkci unlockFiveAttempts');
			
			return [
				's' => 'info',
				'cs' => 'Odblokováno, nicméně bohužel ne všechno proběholo korektně',
				'en' => 'Unblocked, but not all tasks were completly correct'
			];
		}
		
		return [
			's' => 'success',
			'cs' => 'Brutforce systém úspěšně odblokován',
			'en' => 'Brutforce system was successfully unblocked'
		];
	}
}