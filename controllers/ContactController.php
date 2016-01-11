<?php

class ContactController extends Controller {

    public function process($parameters) {
        $contact = new Contact();

        if (isset($_POST['send'])) {
            $data = $contact->sanitize(['year' => $_POST['year'],
                'email' => $_POST["email"],
                'message' => $_POST["message"]
            ]);

            $result = $contact->sendContactEmail($data['year'], $data['email'], $data['message']);
            $this->messages[] = $result;
            if ($result['s'] != 'success') $this->data = $data; //for autofilling from previous page when error
        }

        if (isset($_SESSION['username'])) $this->data['email'] = $_SESSION['username']; //autofilling current user email
        $this->header['title'] = 'Kontakt';
        $this->view = 'contact';
    }
}