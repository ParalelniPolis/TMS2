<?php

class Login extends Model {

	public function tryLogin($data, $language) {
		$attempt = Db::queryOne('SELECT `id_user`,`email`,`password`,`salt` FROM `users`
                                 WHERE `email` = ?', [$data['login']]);
		$userPassword = hash('sha512', $data['p'].$attempt['salt']);

		//if user doesn't exists
		if ($attempt == null) return ['s' => 'error',
			'cs' => 'Bohužel, uživatel není v databázi. <br/><a href="'.ROOT.'/cs/registration">Nechceš se registrovat?</a>',
			'en' => 'Sorry, this user does not exist in our database. <br/><a href="'.ROOT.'/en/registration">Maybe you want to register instead?</a>'];

		//account is not locked
		if ($this->checkBrute($data['login']) == false) {
			//password is different!
			if ($userPassword != $attempt['password']) {
				//write it into brutcheck
				Db::queryModify('INSERT INTO `login_attempts` (`login`,`success`,`timestamp`)
                                 VALUES (?, 0, NOW())', [$data['login']]);
				return ['s' => 'error',
					'cs' => 'Bohužel, heslo není správně. <br/><a href="'.ROOT.'/cs/GetLinkForNewPassword">Nepotřebuješ si nechat zaslat nové?</a>',
					'en' => 'Sorry, Incorrect password. <br/><a href="'.ROOT.'/en/GetLinkForNewPassword">Don\'t you need a new one?</a>'];
				//corrent both login and password - success!
			} else {
				//store information about newly logged user
				$_SESSION['id_user'] = $attempt['id_user'];
				$_SESSION['username'] = $data['login'];
				$_SESSION['login_string'] = hash('sha512', $userPassword.$_SERVER['HTTP_USER_AGENT']);

				Db::queryModify('INSERT INTO `login_attempts` (`login`,`success`,`timestamp`)
                                 VALUES (?, 1, NOW())', [$data['login']]);
				return ['s' => 'success',
					'cs' => 'Přihlášeno, vítejte zpět!',
					'en' => 'Logged in, welcome back!'];
			}
			//account is locked by bruteforce
		} else {
			//check if need to send unlock mail
			$timeOfAttempt = date("Y-m-d H:i:s", time() - (BRUTEFORCE_LOCKED_TIME));
			$unlockMailCheck = Db::queryOne('SELECT `timestamp` FROM `restart_brutforce`
                                             WHERE `timestamp` > ? && `email` = ?
                                             ORDER BY `timestamp` DESC', [$timeOfAttempt, $data['login']]);
			//when email has been already sent
			if ($unlockMailCheck[0] != null) return ['s' => 'error',
				'cs' => 'Už byl poslán mail s odblokováním - jestli nedorazil, konktatuj prosím správce.',
				'en' => 'Mail with unblock was already sent - if you did\'t recieve anything, please contact the webmaster'];
			//wirte into DB about unblocking key...
			$randomHash = $this->getRandomHash();
			Db::queryModify('INSERT INTO `restart_brutforce` (`validation_string`, `email`, `active`, `timestamp`)
                                 VALUES (?, ?, TRUE, NOW())', [$randomHash, $data['login']]);
			//...and send email
			$activeLink = ROOT.'/'.$language.'/unlockBrutforce/'.$randomHash;
			$subject = [
				'cs' => NAME.' Paralelní polis - příliš neúspěšných přihlášení',
				'en' => NAME.' Paralelni polis - login attemps exceeded'];
			$message = [
				'cs' => 'Ahoj! <br/>
<br/>
Někdo se pokusil na tento email přihlásit pod tímto emailem více než '.BRUTEFORCE_NUMBER_OF_ATTEMPTS.' krát do '.NAME.' Paralelního Polisu.<br/>
<br/>
<a href="'.ROOT.'/cs/contact">Pokud jsi to nebyl ty, měl by ses ozvat správci.</a><br/>
<br/>
Kliknutí na tento link ti odemkne dalších pět pokusů: <a href="'.$activeLink.'">'.$activeLink.'</a><br/>',
				'en' => 'Hi! <br/>
<br/>
Someone has tried to log in from this email more than '.BRUTEFORCE_NUMBER_OF_ATTEMPTS.' times into'.NAME.' from Paralell polis.<br/>
<br/>
<a href="'.ROOT.'/en/contact">If this wasn\'t you, you should immediately contact the webmaster.</a><br/>
<br/>
Clicking on this link will unlock '.BRUTEFORCE_NUMBER_OF_ATTEMPTS.' more tries: <a href="'.$activeLink.'">'.$activeLink.'</a><br/>'
			];
			$this->sendEmail(EMAIL, $data['login'], $subject[$language], $message[$language]);

			$dataForTicket = ['sentUnlockBruteforce', $data['login'], 'email with unlocking link is sent'];
			Db::queryModify('INSERT INTO `tickets` (`type`, `title`, `message`, `timestamp`)
                             VALUES (?,?,?, NOW())', $dataForTicket);
			return ['s' => 'error',
				'cs' => 'Zkusil jsi se přihlásit '.BRUTEFORCE_NUMBER_OF_ATTEMPTS.'krát za sebou.<br/>
                    Počkej '.round(BRUTEFORCE_LOCKED_TIME / 60).' minut nebo klikni v emailu na odemykací link, který jsme ti teď poslali',
				'en' => 'You\'ve tried to login '.BRUTEFORCE_NUMBER_OF_ATTEMPTS.' times.<br/>
                    Wait '.round(BRUTEFORCE_LOCKED_TIME / 60).' minutes or click on the link to unlock, which has been sent to your email address'];
		}
	}

	private function checkBrute($login) {
		$timeOfAttempt = date('Y-m-d H:i:s', time() - (BRUTEFORCE_LOCKED_TIME));
		$attempts = Db::queryAll('SELECT `login`, `timestamp` FROM `login_attempts`
                                  WHERE `login` = ? && `timestamp` > ? && `success` = 0', [$login, $timeOfAttempt]);
		if (BRUTEFORCE_NUMBER_OF_ATTEMPTS < count($attempts)) {
			return true;
		}
		return false;
	}
}