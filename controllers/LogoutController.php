<?php

class LogoutController extends Controller {

    function process($parameters) {

        //we don't want to loose our messages stored in messages from past
        if (!empty($_SESSION)) foreach ($_SESSION['messages'] as $m) $this->messages[] = $m;

        session_unset();
        session_destroy();

        $this->messages[] = ['s'=>'info',
            'cs'=>'Odhlášeno',
            'en'=>'Logout successful'
        ];
        $this->header['title'] = [
            'cs' => 'Úvod - odhlášeno',
            'en' => 'Intro - successful logout'];
        $this->view = 'intro';
    }
}