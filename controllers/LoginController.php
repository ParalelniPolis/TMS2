<?php

class LoginController extends Controller {

    function process($parameters) {
        $login = new Login();
        //user is already logged
        if ($login->checkLogin() != 0) {
            $this->messages[] = ['s' => 'info',
                'cs' => 'Už jsi přihlášen',
                'en' => 'You are already logged'];
            $this->redirect('');
        }

        if (isset($_POST['sent'])) {
            $data = $login->sanitize([
                'login' => $_POST['login'],
                'p' => $_POST['p']
                ]);
            $result = $login->tryLogin($data);
            if ($result['s'] == 'success') $this->redirect('payments');
            else $this->messages[] = $result;
            $this->data = $data; //for autofilling imputs from previous page
        }

        $this->header['title'] = [
            'cs' => 'Přihlášení',
            'en' => 'Login'];
        $this->view = 'login';
    }
}