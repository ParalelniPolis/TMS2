<?php

class CheckUsersController extends Controller {

    function process($parameters) {
        $checkUsers = new CheckUsers();
        $placesIds = $checkUsers->returnAdminPlacesIds();
        if ($placesIds == false) $this->redirect('error');

        $members = $checkUsers->getMembers($placesIds, $this->language);

        $this->data['csrf'] = Csrf::getCsrfToken();
        $this->data['activeMemberMailList'] = $checkUsers->getActiveMemberMailList($members);
        $this->data['members'] = $members;
        $this->header['title'] = 'Ostatní členové';
        $this->view = 'checkUsers';
    }
}