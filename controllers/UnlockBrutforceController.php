<?php

class UnlockBrutforceController extends Controller {

    public function process($parameters) {
        $unlockBrutforce = new UnlockBrutforce();
        $key = $parameters[0];

        $result = $unlockBrutforce->checkKeyReturnEmail($key);
        if ($result[0] != 'error') {
            $result = $unlockBrutforce->unlockFiveAttempts($result);
        }

        $this->messages[] = $result;
        $this->header['title'] = [
            'cs' => 'Odemknout brutforce systém',
            'en' => 'Unlock bruteforce system'];
        $this->view = 'unlockBrutforce';
    }
}