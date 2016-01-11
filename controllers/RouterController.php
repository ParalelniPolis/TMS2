<?php

class RouterController extends Controller {
    /**
     * @var Controller
     */
    protected $controller; //for controller instance

    public function process($parameters) {
        $render = new Render();

        //dig out and sanitize data from URL
        $parsedURL = $this->parseURL($render->sanitize($parameters[0]));
        array_shift($parsedURL); //throw away because of subdirectory

        //determine language - using 'en' of 'cs' because of ISO
        $userLanguage = $render->getLanguage(array_shift($parsedURL));
        if ($userLanguage == false) {
            $this->messages[] = ['s' => 'error',
                'cs' => 'Bohužel jsme správně nezrozpoznali zadaný jazyk',
                'en' => 'Sorry, we didn\'t recognize the language correctly'];
            $userLanguage = $this->language;
        }
        $this->language = $userLanguage;

        //empty URL is redirected into intro
        if (empty($parsedURL[0])) $this->redirect('intro');

        //create corrent name of controller class
        $controllerClass = $this->intoCamel(array_shift($parsedURL)).'Controller';

        //if class exist, create, else error
        if (file_exists('controllers/'.$controllerClass.'.php'))
            $this->controller = new $controllerClass($this->language);
        else $this->redirect('error');

        //make controller happen
        $this->controller->process($parsedURL);

        switch ($this->language) {
            case ('cs'): {
                $this->data['title'] = $this->controller->header['title'].' - '.NAME.' v'.VERSION.' - Paralelní polis';
                $this->data['description'] = NAME.' Tenant Management System v'.VERSION.' pro Paralelní polis - systém pravidelného placení za Bitcoiny';
                $this->data['keywords'] = "TMS, TMS2, TMSv2, Tenant Management System, Paralelni polis, BTC, bitcoin, ".NAME;
                $this->data['mainMenu'] = $render->returnMainMenu('cs');
                $this->data['loginCredentials'] = $render->returnLoginCredentials('cs');
                break;
            }
            case ('en'): {
                $this->data['title'] = $this->controller->header['title'].' - '.NAME.' v'.VERSION.' - Paralell polis';
                $this->data['description'] = NAME.' Temant Management System v'.VERSION.' for Paralell polis - system for regular Bitcoin payments';
                $this->data['keywords'] = "TMS, TMS2, TMSv2, Tenant Management System, Paralelni polis, BTC, bitcoin, ".NAME;
                $this->data['mainMenu'] = $render->returnMainMenu('en');
                $this->data['loginCredentials'] = $render->returnLoginCredentials('en');
                break;
            }
        }
        $this->data['languageSwitch'] = $render->returnLanguageSwitch($this->language);

        //catch and send to display multiple messages
        $this->data['messages'] = [];
        foreach ($this->controller->messages as $m) $this->data['messages'][] = $m;

        $this->view = 'layout'; //view for router
    }

    private function parseURL($url) {
        $result = parse_url($url);
        $result["path"] = ltrim($result["path"], "/"); //toss left slash
        $result["path"] = trim($result["path"]); //toss all whitespaces
        $parsedURL = explode("/", $result["path"]); //explode onto parameters
        return $parsedURL;
    }

    private function intoCamel($text) {
        $result = str_replace('-', ' ', $text); //dashes replace spaces
        $result = ucwords($result); //all capitals
        $result = str_replace(' ', '', $result); //delete spaces
        return $result;
    }
}