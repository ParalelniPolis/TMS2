<?php

class CheckUsers extends Model {
	
	public function getMembers($adminId) {
		$placesIds = $this->returnAdminPlacesIds($adminId);
		$members = [];
		foreach ($placesIds as $placeID) {
			$tariffMembers = Db::queryAll('SELECT `id_user`,`first_name`,`last_name`,`telephone`,`active`,`email`,`tariffCZE`,`name`,`places`.`id` AS `id_of_place`
                                           FROM `users`
                                           JOIN `tariffs` ON `user_tariff` = `id_tariff`
                                           JOIN `places` ON `tariffs`.`place_id` = `places`.`id`
                                           WHERE `place_id` = ?
                                           ORDER BY `active` DESC, `invoicing_start_date` ASC', [$placeID]);
			//for equvivalent position between all members
			foreach ($tariffMembers as $tm)
				$members[] = $tm;
		}
		//add first payment date and status of all invoices to each member
		foreach ($members as &$m) {
			$m['firstPaymentDate'] = $this->getFirstPaymentDate($m['id_user']);
			$m['paymentFlag'] = $this->getPaymentFlag($m['id_user']);
		}
		
		return $members;
	}
	
	private function getFirstPaymentDate($userId) {
		$datePayments = Db::querySingleOne('SELECT `payment_first_date` FROM `payments`
            WHERE `id_payer` = ? ORDER BY `payment_first_date` ASC', [$userId]);
		$dateStart = Db::querySingleOne('SELECT `invoicing_start_date` FROM `users`
			WHERE `id_user` = ?', [$userId]);
		
		if (!empty($datePayments))
			$r = min($datePayments, $dateStart); else $r = $dateStart;
		
		if (empty($r))
			return 'unknown+error!';
		
		return date('d/m/y', strtotime($r));
	}
	
	private function getPaymentFlag($userId) {
		//TODO more robust check over all payments
		$r = Db::querySingleOne('SELECT `status` FROM `payments`
                                 WHERE `id_payer` = ? ORDER BY ?, ?, `status` DESC', [
			$userId,
			'received',
			'confirmed'
		]);
		if ($r == 'received' || $r == 'confirmed')
			return 'success';
		if (empty($r))
			return 'unknown'; else return 'error';
	}
	
	public function getActiveMemberMailList($members) {
		$result = [];
		foreach ($members as $m) {
			if ($m['active'] == 1)
				$result[] = $m['email'];
		}
		
		return $result;
	}
}