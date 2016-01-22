<?php

class GetLinkForNewPassword extends Model {
	public function trySendLink($email, $year, $language) {
		//inkredintions are correctly set
		if (!isset($email, $year)) return ['s' => 'error',
			'cs' => 'Nepovedlo se získat data. Zkus to znovu prosím',
			'en' => 'We didn\'t catch data correctly - please try it again'];
		//correct year in antispam
		if ($_POST['year'] != date("Y") - 1) return ['s' => 'error',
			'cs' => 'Bohužel, antispam byl tentokrát mocnější než ty',
			'en' => 'Nothing happend, antispam was stronger than you'];


		$result = Db::queryOne('SELECT `email` FROM `users`
                                WHERE `email` = ?', [$_POST['email']]);
		//skip all when email ins't the same as typed
		if ($_POST['email'] == $result[0]) {
			$randomHash = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			if (!Db::queryModify('INSERT INTO `restart_password` (`validation_string`, `email`, `active`, `timestamp`)
                                  VALUES (?, ?, TRUE, NOW())', [$randomHash, $result[0]])
			) {
				$this->newTicket('problem', $_SESSION['id_user'], 'nepovedlo se zapsat do restart_password ve funkci register');
				return ['s' => 'chyba',
					'cs' => 'Pokus se nepovedl uložit; zkus to prosím znovu za pár minut',
					'en' => 'We failed on saving data. Try it again please after couple of minutes'];
			}

			$subject = [
				'cs' => NAME.' Paralelní Polis - žádost o restart hesla',
				'en' => NAME.' Paralell Polis - change password request'];
			$activeLink = ROOT.'/'.$language.'/RestartPasswordByLink/'.$randomHash;
			$message = [
				'cs' => 'Zdravím!<br/>
<br/>
Na stránce <a href="'.ROOT.'/'.$language.'">'.ROOT.'</a> jsme registrovali žádost o restart hesla.<br/>
<br/>
Heslo si můžeš změnit klikem na odkaz <a href="'.$activeLink.'">'.$activeLink.'</a>. Platnost odkazu je <b>'.round(CHANGE_PASS_TIME_VALIDITY / 60).'</b> minut.<br/>
<br/>
Pokud tento mail neočekáváš, stačí ho ignorovat. Pokud by ti přesto přišel podezřelý nebo vícekrát za sebou,
prosím konkatuj správce stránek na <a href="'.ROOT.'/'.$language.'/contact">'.ROOT.'/'.$language.'/contact</a><br/>',
				'en' => 'Hello!<br/>
<br/>
We are register request about password change on the page <a href="'.ROOT.'/'.$language.'">'.ROOT.'</a>.<br/>
<br/>
You can change your password by clicking on this link: <a href="'.$activeLink.'">'.$activeLink.'</a>. Time validity of this link is <b>'.round(CHANGE_PASS_TIME_VALIDITY / 60).'</b> minutes.<br/>
<br/>
If you don\'t awaiting for this mail, just ignore it. But if you want to know what to do next,
please contact our webmaster on this page: <a href="'.ROOT.'/'.$language.'/contact">'.ROOT.'/'.$language.'/contact</a><br/>'
			];

			if (!$this->sendEmail(EMAIL, $email, $subject[$language], $message[$language])) {
				$this->newTicket('problem', $_SESSION['id_user'], 'nepovedlo se odeslat email');
				return ['s' => 'error',
					'cs' => 'Nepovedlo se odeslat email s aktivačním linkem; zkus to prosím za pár minut znovu',
					'en' => 'We failed in sending email with activation link; try it again please after couple of minutes'];
			}
			$this->newTicket("restartHesla", $email, 'poslan mail s linkem');
		} else {
			//check if we can grab who is logged - serve as primitive honeypot
			if (isset($_SESSION['username'])) $loggedUser = $_SESSION['username'];
			else $loggedUser = "we dont know :(";
			$this->newTicket("restartHesla", $loggedUser, 'neplatny pokus restartu hesla pro uzivatele: '.$_POST['email']);
		}
		return ['s' => 'success',
			'cs' => 'Ozvali jsme se na zadaný email',
			'en' => 'We send as email on desired address'];
	}
}