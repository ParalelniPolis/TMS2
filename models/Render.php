<?php

class Render extends Model {

	public function returnLanguageSwitch($lang) {
		$result = ('<div class="languageSwitch">');
		if ($lang == 'cs') $result .= ('<b>česky</b> - <a class="languageSwitch" href="'.ROOT.'/en">english</a></div>');
		if ($lang == 'en') $result .= ('<a class="languageSwitch" href="'.ROOT.'/cs">česky</a> - <b>english</b></div>');
		return $result;
	}

	public function returnLoginCredentials($lang) {
		$result = '';
		$label = ['cs' => 'Odhlásit se', 'en' => 'Logout'];
		if (!empty($_SESSION['username'])) {
			$result .= ('<div><b>'.$_SESSION['username'].'</b>');
			$result .= ('<a href="'.ROOT.'/'.$lang.'/logout"><button class="logout">'.$label[$lang].'</button></a></div>');
		}
		return $result;
	}

	public function returnMainMenu($lang) {
		$login = $this->checkLogin();
		$adminPlacesIds = $this->returnAdminPlacesIds();

		$labels = [
			'cs' => [
				'intro' => 'Úvod',
				'login' => 'Přihlášení',
				'registration' => 'Registrace',
				'forceRegistration' => 'Zaregistrovat člena',
				'payments' => 'Platby',
				'changePersonals' => 'Změnit údaje',
				'checkUsers' => 'Ostatní členové',
				'contact' => 'Kontakt'
			], 'en' => [
				'intro' => 'Intro',
				'login' => 'Login',
				'registration' => 'Registration',
				'forceRegistration' => 'Register member',
				'payments' => 'Payments',
				'changePersonals' => 'Change personals',
				'checkUsers' => 'Other users',
				'contact' => 'Contact'
			]
		];

		$result = '<li><a href="'.ROOT.'/'.$lang.'/intro">'.$labels[$lang]['intro'].'</a></li>';
		if ($login == false) $result .= '<li><a href="'.ROOT.'/'.$lang.'/login">'.$labels[$lang]['login'].'</a></li>';
		if ($login == false) $result .= '<li><a href="'.ROOT.'/'.$lang.'/registration">'.$labels[$lang]['registration'].'</a></li>';
		if ($login == true) $result .= '<li><a href="'.ROOT.'/'.$lang.'/payments/'.$_SESSION["id_user"].'">'.$labels[$lang]['payments'].'</a></li>';
		if ($login == true) $result .= '<li><a href="'.ROOT.'/'.$lang.'/changePersonals/'.$_SESSION["id_user"].'">'.$labels[$lang]['changePersonals'].'</a></li>';
		if ($adminPlacesIds != false) $result .= '<li><a href="'.ROOT.'/'.$lang.'/checkUsers">'.$labels[$lang]['checkUsers'].'</a></li>';
		if ($adminPlacesIds != false) $result .= '<li><a href="'.ROOT.'/'.$lang.'/forceRegistration">'.$labels[$lang]['forceRegistration'].'</a></li>';
		$result .= '<li><a href="'.ROOT.'/'.$lang.'/contact">'.$labels[$lang]['contact'].'</a></li>';

		return $result;
	}

}