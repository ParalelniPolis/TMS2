<?php

class RestartPasswordByLinkController extends Controller {

    public function process($parameters) {
        $restartPasswordByLink = new RestartPasswordByLink();
        $link = $parameters[0];

        //if form is filled (button si clicked)
        if (isset($_POST['sent'])) {
            $result = $restartPasswordByLink->checkForm($link, $_POST['p']);
            $this->messages[] = $result;

            //when user is logged, logout him, else show intro
            if (isset($_SESSION['username'])) $this->redirect('logout');
            else $this->view = 'intro';

        //if not, then show form (or not ;)
        } else {
            $result = $restartPasswordByLink->isLinkValid($link);
            if ($result[0] != 'success') {
                $this->messages[] = $result;
                $this->view = 'intro';
            } else {
                //invalidation of link (bcs showing form only once)
                $restartPasswordByLink->invalidateLink($link);
                $this->view = 'restartPasswordByLink';
            }
        }
        $this->header['title'] = 'Obnov heslo linkem';
    }
}