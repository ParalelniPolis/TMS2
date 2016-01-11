<?php

class UserController extends Controller {

    function process($parameters) {
        $user = new User();
        if (!$user->checkLogin()) $this->redirect('error');
        //if empty parameter, add there current user
        if (isset($parameters[0])) $id = $parameters[0]; else $id = $_SESSION['id_user'];

        if ($id != $_SESSION['id_user']) {
            //if not admin of the right place, throw error
            $placesIds = $user->returnAdminPlacesIds();
            $userPlace = $user->getUserPlaceFromId($id);
            if (!in_array($userPlace, $placesIds)) $this->redirect('error');
        }

        $data = $user->getUserData($id, $this->language);

        //TODO shift this two jobs into cron
        //actualize payments
        $resultMessages = $user->actualizePayments($data['user'], $data['payments'], $data['tariff'], $this->language);
        foreach ($resultMessages as $message) $this->messages[] = $message;
        //create new payments
        $user->makeNewPayments($data['user'], $data['tariff']);

        //actualize new data for showing off user
        $data = $user->getUserData($id, $this->language);

        //show non-active user
        if (!$data['user']['active']) $this->messages[] = ['info', 'Neaktivní uživatel - nové faktury se negenerují'];
        $this->data['tariff'] = $data['tariff'];
        $this->data['user'] = $data['user'];
        $this->data['payments'] = $data['payments'];
        $this->header['title'] = 'Přehled plateb';
        $this->view = 'user';
    }
}