<?php

class IntroController extends Controller {

	public function process($parameters) {

		$this->header['title'] = [
			'cs' => 'Úvod',
			'en' => 'Intro'];
		$this->view = 'intro';
	}
}