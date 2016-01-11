<?php

abstract class Controller {
    protected $data = []; //main data from the models
    protected $view = '';
    protected $header = ['title' => '', 'keywords' => '', 'description' => ''];
    protected $messages = [];
    protected $language;

    abstract function process($parameters);

    function __construct($language) {
        $this->language = $language;
    }

    //render the view with all extracted data with theid own keys
    public function render() {
        if (!empty($this->view)) {
            extract($this->data);
            require('views/'.$this->language.'/'.$this->view.'.phtml');
        } else echo('Cannot find desired view :(');
    }

    //redirection
    public function redirect($url) {
        //safe all previouses messages for next page
        if (!empty($_SESSION['messages']))
            $_SESSION['messages'] = array_merge($_SESSION['messages'], $this->messages);
        else
            $_SESSION['messages'] = $this->messages;
        header("Location: ".ROOT."/".$this->language."/$url");
        header("Connection: close");
        exit;
    }

    //redirection outside the system
    public function redirectOut($url) {
        header("Location: $url");
        header("Connection: close");
        exit;
    }

    public function displayPdf($pdf) {
        header('Content-Type: application/pdf');
        //TODO make nice header
        echo($pdf);
        exit;
    }
}