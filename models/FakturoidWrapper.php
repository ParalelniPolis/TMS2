<?php

class FakturoidWrapper extends Model {
	
	private $fakturoid;
	
	public function __construct() {
		$this->fakturoid = new Fakturoid(FAKTUROID_SLUG, FAKTUROID_EMAIL, FAKTUROID_API_KEY, FAKTUROID_USER_AGENT);
	}
	
	public function createCustomer($user) {
		try {
			$data = [
				'name' => $user['firstname'].' '.$user['surname'],
				'registration_no' => $user['ic'],
				'street' => $user['address']
			];
			$customer = $this->fakturoid->create_subject($data);
			
			return $customer;
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'Fakturoid', 'code: '.$code.', message: '.$message);
			$_SESSION['messages'][] = [
				's' => 'error',
				'cs' => 'Nastal problém v komunikaci se serverem fakturoid.cz. Zkuste to prosím znovu za pár minut',
				'en' => 'We encoured a problem in communication on fakturoid.cz. Please try it again after a few minutes'
			];
			
			return false;
		}
	}
	
	public function updateCustomer($user) {
		try {
			$data = [
				'name' => $user['firstname'].' '.$user['surname'],
				'registration_no' => $user['ic'],
				'street' => $user['address']
			];
			$id = $user['fakturoid_id'];
			$result = $this->fakturoid->update_subject($id, $data);
			
			return $result;
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'Fakturoid', 'code: '.$code.', message: '.$message);
			$_SESSION['messages'][] = [
				's' => 'error',
				'cs' => 'Nastal problém v komunikaci se serverem fakturoid.cz. Zkuste to prosím znovu za pár minut',
				'en' => 'We encoured a problem in communication on fakturoid.cz. Please try it again after a few minutes'
			];
			
			return false;
		}
	}
	
	public function createInvoice($user, $price, $tariffName, $issuedDate, $lang) {
		try {
			$this->fakturoid->update_subject($user['fakturoid_id'], [
				'id' => $user['fakturoid_id'],
				'name' => $user['first_name'].' '.$user['last_name'],
				'email' => $user['email'],
				'registration_no' => $user['ic'],
				'street' => $user['address'],
			]);
			
			//disabled multilingual genereation fo texts
			//if ($lang == 'cs') $tariffLine = 'Tarif: '.$tariffName.' se začátkem ke dni '.date('d. m. Y', strtotime($issuedDate)); else $tariffLine = 'Tariff: '.$tariffName.' with beginning from '.date('d. m. Y', strtotime($issuedDate));
			
			$tariffLine = 'Tarif: '.$tariffName.' se začátkem ke dni '.date('d. m. Y', $issuedDate);
			$lines = [
				[
					'name' => $tariffLine,
					'quantity' => 1,
					'unit_price' => $price
				]
			];
			$invoice = $this->fakturoid->create_invoice([
				'subject_id' => $user['fakturoid_id'],
				'issued_on' => date('Y-m-d'),
				'currency' => 'CZK',
				'tags' => ['Paper Hub'],
				'lines' => $lines
			]);
			//deliver the invoice
			//$this->fakturoid->fire_invoice($invoice->id, 'deliver');
			return $invoice;
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'Fakturoid', 'code: '.$code.', message: '.$message);
			$_SESSION['messages'][] = [
				's' => 'error',
				'cs' => 'Nastal problém v komunikaci se serverem fakturoid.cz. Zkuste to prosím znovu za pár minut',
				'en' => 'We encoured a problem in communication on fakturoid.cz. Please try it again after a few minutes'
			];
			
			return false;
		}
	}
	
	public function setInvoicePayed($invoiceId) {
		try {
			$this->fakturoid->fire_invoice($invoiceId, 'pay');
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'class FakturoidWrapper func. setFakturoidInvoicePayed', 'code: '.$code.' with message: '.$message);
		}
	}
	
	public function cancelInvoice($invoiceId) {
		try {
			$this->fakturoid->fire_invoice($invoiceId, 'cancel');
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'class FakturoidWrapper func. cancelInvoice', 'code: '.$code.' with message: '.$message);
		}
	}
	
	public function addExtra($invoiceFakturoidId, $price, $description) {
		try {
			$lines = [
				[
					'name' => $description,
					'quantity' => 1,
					'unit_price' => $price
				]
			];
			$invoice = $this->fakturoid->update_invoice($invoiceFakturoidId, ['lines' => $lines]);
			
			return $extraFakturoidId = end($invoice->lines)->id;
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'class FakturoidWrapper func. addExtra', 'code: '.$code.' with message: '.$message);
		}
		
		return false;
	}
	
	public function deleteExtra($invoiceFakturoidId, $extraFakturoidId) {
		try {
			$lines = [
				[
					'id' => $extraFakturoidId,
					"_destroy" => true
				]
			];
			$this->fakturoid->update_invoice($invoiceFakturoidId, ['lines' => $lines]);
		} catch (FakturoidException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$this->newTicket('error', 'class FakturoidWrapper func. deleteExtra', 'code: '.$code.' with message: '.$message);
		}
	}
	
	public function getInvoiceAsPdf($invoiceId) {
		return $this->fakturoid->get_invoice_pdf($invoiceId);
	}
	
	public function getFakturoidInvoiceIdFromPaymentId($paymentId) {
		return Db::querySingleOne('SELECT `invoice_fakturoid_id` FROM `payments` WHERE `id_payment` = ?', [$paymentId]);
	}
	
	public function getUserIdFromInvoiceId($invoiceId) {
		return Db::querySingleOne('SELECT `id_payer` FROM `payments` WHERE `invoice_fakturoid_id` = ?', [$invoiceId]);
	}
	
	public function getExtraFakturoidId($extraId) {
		return Db::querySingleOne('SELECT `fakturoid_id` FROM `extras` WHERE `id_extra` = ?', [$extraId]);
	}
	
	public function getInvoiceFakturoidIdFromExtraId($extraId) {
		return Db::querySingleOne('SELECT `invoice_fakturoid_id` FROM `payments`
			JOIN `extras` ON `extras`.`payment_id` = `payments`.`id_payment`
			WHERE `id_extra` = ?', [$extraId]);
	}
	
	public function getFakturoidIdFromUserId($userId) {
		return Db::querySingleOne('SELECT `fakturoid_id` FROM `users` WHERE `id_user` = ?', [$userId]);
	}
}