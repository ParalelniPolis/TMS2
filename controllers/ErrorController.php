<?php

class ErrorController extends Controller {

    public function process($parameters) {

        header("HTTP/1.0 404 Not Found");
        $this->header['title'] = 'Error';
        $this->messages[] = ['s' => 'error',
            'cs' => 'Chyba #404',
            'en' => 'Error #404'];
        $this->view = 'error';
    }
}