<?php

class Contact extends Model {
    public function sendContactEmail($year, $email, $message) {
        if ($year != date("Y") + 1) return ['s' => 'error',
            'cs' => 'Bohužel, nic se neodeslalo, antispam byl tentokrát mocnější než ty',
            'en' => 'Nothing happend, antispam was stronger than you'];

        //send email to admin
        $this->sendEmail($email, EMAIL, "Paralelní polis - TMS2", $message);
        //and copy to user
        $this->sendEmail(EMAIL, $email, "Paralelni polis - TMS2", "Kopie emailu zaslaného ze systému TMS2: " . PHP_EOL . PHP_EOL . $message);

        if (!Db::queryModify('INSERT INTO `tickets` (`type`, `title`, `message`, `timestamp`)
                            VALUES (?,?,?, NOW())', ["sent contact email", $email, $message])
        ) return ['s' => 'info',
            'cs' => 'Email odešel, ale neuložil se do databáze. Brzo se ozveme',
            'en' => 'Email was sent, but didn\'n save in our database. We will be in touch'];
        else return ['s' => 'success',
            'cs' => 'Díky za zprávu, brzo se ozveme',
            'en' => 'Thanks for the message, we will be in touch'];
    }
}