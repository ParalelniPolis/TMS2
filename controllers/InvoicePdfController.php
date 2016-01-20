<?php

class InvoicePdfController extends Controller {

	function process($parameters) {
		$fakturoid = new FakturoidWrapper();
		if (!$fakturoid->checkLogin()) $this->redirect('error');
		$fakturoidInvoiceId = null;
		if (isset($parameters[0])) $fakturoidInvoiceId = $parameters[0]; else $this->redirect('error');

		$userOfInvoice = $fakturoid->getUserIdFromInvoiceId($fakturoidInvoiceId);

		if ($userOfInvoice != $_SESSION['id_user']) {
			//if not admin of the right place then throw error
			$placesIds = $fakturoid->returnAdminPlacesIds();
			$userPlace = $fakturoid->getUserPlaceFromId($_SESSION['id_user']);
			if (!in_array($userPlace, $placesIds)) $this->redirect('error');
		}

		$pdf = $fakturoid->getInvoiceAsPdf($fakturoidInvoiceId);
		$this->displayPdf($pdf);
	}
}