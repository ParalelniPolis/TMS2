<?php

class MakeAdminController extends Controller {
    function process($parameters) {
        //safety constant check
        if (!ALLOW_MAKE_ADMIN) $this->redirect('error');

        $newAdminId = $parameters[0];
        array_shift($parameters);
        $newAdminPlacesId = [];
        while (!empty($parameters)) {
            $newAdminPlacesId[] = $parameters[0];
            array_shift($parameters);
        }
        $makeAdmin = new MakeAdmin();
        if (!$makeAdmin->checkLogin()) $this->redirect('error');

        $result = $makeAdmin->checkInputs($newAdminId, $newAdminPlacesId);
        if ($result['s'] == 'success') {
            $result = $makeAdmin->makeNewAdmin($newAdminId, $newAdminPlacesId);
        }

        $this->messages[] = $result;
        $this->header['title'] = [
            'cs' => 'Vytvořit admina',
            'en' => 'Make admin'];
        $this->view = 'intro';
    }
}