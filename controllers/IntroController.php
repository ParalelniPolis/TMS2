<?php

class IntroController extends Controller {

    public function process($parameters) {

        $this->header['title'] = 'Úvod';
        $this->view = 'intro';
    }
}