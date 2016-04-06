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
	
	//render the view with all extracted data with their own keys
	public function render() {
		if (!empty($this->view)) {
			extract($this->data);
			require('views/'.$this->language.'/'.$this->view.'.phtml');
		} else echo('Unable to find desired view :(');
	}
	
	//redirection
	public function redirect($url) {
		//safe all previous messages for next page
		if (!empty($_SESSION['messages']))
			$_SESSION['messages'] = array_merge($_SESSION['messages'], $this->messages); else
			$_SESSION['messages'] = $this->messages;
		header('Location: '.ROOT.'/'.$this->language.'/'.$url);
		header('Connection: close');
		exit;
	}
	
	//redirection outside the system
	public function redirectOut($url) {
		header('Location: '.$url);
		header('Connection: close');
		exit;
	}
	
	public function displayPdf($pdf) {
		//TODO add nice header
		//TODO fix nice name when user is saving the document
		header('Content-Type: application/pdf');
		echo($pdf);
		exit;
	}
}