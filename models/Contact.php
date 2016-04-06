<?php

class Contact extends Model {
	public function sendContactEmail($year, $email, $message, $language) {
		if ($year != date("Y") + 1)
			return [
				's' => 'error',
				'cs' => 'Bohužel, nic se neodeslalo, antispam byl tentokrát mocnější než ty',
				'en' => 'Nothing happend, antispam was stronger than you'
			];
		
		$subject = ['cs' => NAME.'Paralelní Polis', 'en' => NAME.' - Paralell Polis'];
		$prefix = [
			'cs' => 'Kopie emailu zaslaného ze systému '.NAME.': '.PHP_EOL.PHP_EOL,
			'en' => 'Copy of email send from system '.NAME.': '.PHP_EOL.PHP_EOL
		];
		
		//send email to admin
		$this->sendEmail($email, EMAIL, $subject[$language], $message);
		//and copy to user
		$this->sendEmail(EMAIL, $email, $subject[$language], $prefix[$language].$message);
		
		if (!Db::queryModify('INSERT INTO `tickets` (`type`, `title`, `message`, `timestamp`)
                            VALUES (?,?,?, NOW())', ["sent contact email", $email, $message])
		)
			return [
				's' => 'info',
				'cs' => 'Email odešel, ale neuložil se do databáze. Brzo se ozveme',
				'en' => 'Email was sent, but didn\'n save in our database. We will be in touch'
			]; else return [
			's' => 'success',
			'cs' => 'Díky za zprávu, brzo se ozveme',
			'en' => 'Thanks for the message, we will be in touch'
		];
	}
}