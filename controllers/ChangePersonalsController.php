<?php

class ChangePersonalsController extends Controller {

    function process($parameters) {
        $changePersonals = new ChangePersonals();
        if (!$changePersonals->checkLogin()) $this->redirect('error');
        //if not same user as logged in, throw error page
        $id = $parameters[0];
        if ($changePersonals->getUserIdFromEmail($_SESSION['username']) != $id) $this->redirect('error');

        //if form is sent
        if (isset($_POST['sent'])) {
            $data = $changePersonals->sanitize([
                "firstname" => $_POST['firstname'],
                "surname" => $_POST['surname'],
                "telephone" => $_POST['telephone'],
                "ic" => $_POST['ic'],
                "p" => $_POST['p'],
                "csrf" => $_POST['csrf']
            ]);
            if (!Csrf::validateCsrfRequest($data['csrf'])) {
                $this->messages[] = ['s' => 'error',
                    'cs' => 'Možný CSRF útok! Zkuste prosím změnit údaje znovu',
                    'en' => 'Possible CSRF attack! Please try change your personals again'];
            } else {
                $result = $changePersonals->validateData($data);

                if ($result['s'] == 'success') {
                    $result = $changePersonals->changePersonalData($data, $id);
                }
                $this->messages[] = $result;
            }
        }

        //data for form
        $user = $changePersonals->getUserData($id, $this->language);
        $this->data = $user['user'];
        $this->data['csrf'] = Csrf::getCsrfToken();
        $this->header['title'] = [
            'cs' => 'Změna osobních údajů',
            'en' => 'Change Personal info'];
        $this->view = 'changePersonals';
    }
}