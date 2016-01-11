<?php

class InvoicePdfController extends Controller {

    function process($parameters) {
        $user = new User();
        if (!$user->checkLogin()) $this->redirect('error');
        $invoiceId = null;
        if (isset($parameters[0])) $invoiceId = $parameters[0]; else $this->redirect('error');

        $userOfInvoice = $user->getUserIdFromInvoiceId($invoiceId);

        if ($userOfInvoice != $_SESSION['id_user']) {
            //if not admin of the right place then throw error
            $placesIds = $user->returnAdminPlacesIds();
            $userPlace = $user->getUserPlaceFromId($_SESSION['id_user']);
            if (!in_array($userPlace, $placesIds)) $this->redirect('error');
        }

        $pdf = $user->getFakturoidInvoiceAsPdf($invoiceId);
        $this->displayPdf($pdf);
    }
}