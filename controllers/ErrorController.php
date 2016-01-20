<?php

class ErrorController extends Controller {

	public function process($parameters) {

		header("HTTP/1.0 404 Not Found");
		$this->messages[] = ['s' => 'error',
			'cs' => 'Chyba #404',
			'en' => 'Error #404'];
		$this->header['title'] = [
			'cs' => 'Chyba',
			'en' => 'Error'];
		$this->view = 'error';
	}
}