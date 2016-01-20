<?php

class CheckUsers extends Model {

	public function getMembers($placesIds, $lang) {
		$members = [];
		foreach ($placesIds as $placeID) {
			$tariffMembers = Db::queryAll('SELECT `id_user`,`first_name`,`last_name`,`telephone`,`active`,`email`,`tariffCZE`,`name`,`places`.`id` AS `id_of_place`
                                           FROM `users`
                                           JOIN `tariffs` ON `user_tariff` = `id_tariff`
                                           JOIN `places` ON `tariffs`.`place_id` = `places`.`id`
                                           WHERE `place_id` = ?
                                           ORDER BY `active` DESC', [$placeID]);
			//for equvivalent position between members
			foreach ($tariffMembers as $tm) $members[] = $tm;
		}
		//add first payment date to each member
		foreach ($members as &$m) {
			$m['firstPaymentDate'] = $this->getFirstPaymentDate($m['id_user'], $lang);
			$m['paymentFlag'] = $this->getPaymentFlag($m['id_user']);
		}
		return $members;
	}

	private function getFirstPaymentDate($userId, $lang) {
		$r = Db::querySingleOne('SELECT `payment_first_date` FROM `payments`
                                 WHERE `id_payer` = ? ORDER BY `payment_first_date` ASC', [$userId]);
		if (empty($r)) {
			if ($lang == 'cs') return 'neznámé';
			if ($lang == 'en') return 'unknown';
			return 'unknown+error!';
		}
		return date('d/m Y', strtotime($r));
	}

	private function getPaymentFlag($userId) {
		//TODO more robust check over all payments
		$r = Db::querySingleOne('SELECT `status` FROM `payments`
                                 WHERE `id_payer` = ? ORDER BY ?, ?, `status` DESC', [$userId, 'received', 'confirmed']);
		if ($r == 'received' || $r == 'confirmed') return 'success';
		if (empty($r)) return 'unknown';
		else return 'error';
	}

	public function getActiveMemberMailList($members) {
		$result = [];
		foreach ($members as $m) {
			if ($m['active'] == 1) $result[] = $m['email'];
		}
		return $result;
	}

}