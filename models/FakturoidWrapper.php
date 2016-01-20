<?php

class FakturoidWrapper extends Model {

	private $fakturoid;
	private $subjectId;

	public function __construct() {
		$this->fakturoid = new Fakturoid(FAKTUROID_SLUG, FAKTUROID_EMAIL, FAKTUROID_API_KEY, FAKTUROID_USER_AGENT);
		$this->subjectId = FAKTUROID_SUBJECT_ID;
	}

	public function createInvoice($user, $price, $tariffName, $issuedDate) {
		try {
			$this->fakturoid->update_subject($this->subjectId, ['id' => $this->subjectId,
				'name' => $user['first_name'].' '.$user['last_name'],
				'email' => $user['email'],
				'registration_no' => $user['ic'],
				'street' => $user['address'],

			]);
			$lines = [['name' => 'tarif: '.$tariffName,
				'quantity' => 1,
				'unit_price' => $price]];
			$invoice = $this->fakturoid->create_invoice(['subject_id' => $this->subjectId,
				'issued_on' => $issuedDate,
				'currency' => 'CZK',
				'lines' => $lines]);
			$this->fakturoid->fire_invoice($invoice->id, 'deliver');
			return $invoice;
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'Fakturoid', 'code: '.$code.', message: '.$message);
			$_SESSION['messages'][] = ['s' => 'error',
				'cs' => 'Nastal problém v komunikaci se serverem fakturoid.cz. Zkuste to prosím znovu za pár minut',
				'en' => 'We encoured a problem in communication on fakturoid.cz. Please try it again after a few minutes'];
			return false;
		}
	}

	public function setInvoicePayed($invoiceId) {
		try {
			$this->fakturoid->fire_invoice($invoiceId, 'pay');
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'class user func. setFakturoidInvoicePayed', 'code: '.$code.' with message: '.$message);
		}
	}

	public function getInvoiceAsPdf($invoiceId) {
		return $this->fakturoid->get_invoice_pdf($invoiceId);
	}

	public function getUserIdFromInvoiceId($invoiceId) {
		return Db::querySingleOne('SELECT `id_payer` FROM `payments` WHERE `invoice_fakturoid_id` = ?', [$invoiceId]);
	}

}