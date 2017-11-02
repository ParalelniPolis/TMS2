<?php

class Extras extends Model {
	
	public function checkAddValues($paymentId, $price, $description, $vat) {
		$paymentIdFromDb = Db::querySingleOne('SELECT `id_payment` FROM `payments`
			WHERE `id_payment` = ?', [$paymentId]);
		if (empty($paymentIdFromDb))
			return [
				's' => 'error',
				'cs' => 'Nepovedlo se najít platbu v databázi',
				'en' => 'We cannot find correct payment in database'
			];
		
		if (!is_numeric($price))
			return [
				's' => 'error',
				'cs' => 'Částka musí být číslo',
				'en' => 'Value must be a number'
			];
		
		if (strlen($description) > 120)
			return [
				's' => 'error',
				'cs' => 'Popis musí být do 120 znaků',
				'en' => 'Description must be up to 120 characters'
			];
		
		if (!is_numeric($vat) || $vat < 0 || $vat > 100)
			return [
				's' => 'error',
				'cs' => 'Daně musí být číslo od nuly do sta',
				'en' => 'Vat must be a number'
			];
		
		return ['s' => 'success'];
	}
	
	public function checkAddBlankValues($userId, $price, $description, $vat) {
		$userIdFromDb = Db::querySingleOne('SELECT `id_user` FROM `users`
			WHERE `id_user` = ?', [$userId]);
		if (empty($userIdFromDb))
			return [
				's' => 'error',
				'cs' => 'Nepovedlo se najít uživatele v databázi',
				'en' => 'We cannot find desired user in database'
			];
		
		if (!is_numeric($price))
			return [
				's' => 'error',
				'cs' => 'Částka musí být číslo',
				'en' => 'Value must be a number'
			];
		
		if (strlen($description) > 120)
			return [
				's' => 'error',
				'cs' => 'Popis musí být do 120 znaků',
				'en' => 'Description must be up to 120 characters'
			];
		
		if (!is_numeric($vat) || $vat < 0 || $vat > 100)
			return [
				's' => 'error',
				'cs' => 'Daně musí být číslo od nuly do sta',
				'en' => 'Vat must be a number'
			];
		
		return ['s' => 'success'];
	}
	
	public function addExtra($paymentId, $price, $description, $extraFakturoidId, $vat) {
		if (Db::queryModify('INSERT INTO `extras` (`payment_id`, `description`, `priceCZK`, `fakturoid_id`, `vat`)
 			VALUES (?, ?, ?, ?, ?)', [$paymentId, $description, $price, $extraFakturoidId, $vat])
		)
			return [
				's' => 'success',
				'cs' => 'Položka úspěšně uložena',
				'en' => 'Extra is successfully saved'
			];
		else
			return [
				's' => 'error',
				'cs' => 'Položku se nepovedlo uložit',
				'en' => 'Extra is not saved corrently'
			];
	}
	
	public function addBlankExtra($userId, $price, $description, $vat) {
		if (Db::queryModify('INSERT INTO `extras` (`description`, `priceCZK`, `blank_user_id`, `vat`)
 			VALUES (?, ?, ?, ?)', [$description, $price, $userId, $vat])
		)
			return [
				's' => 'success',
				'cs' => 'Položka pro novou fakturu je úspěšně uložena',
				'en' => 'Extra for next invoice is successfully saved'
			];
		else
			return [
				's' => 'error',
				'cs' => 'Položku pro novou fakturu se nepovedlo uložit',
				'en' => 'Extra for next invoice is not saved corrently'
			];
	}
	
	public function assignBlankExtra($paymentId, $price, $description, $fakturoidExtraId, $extraId) {
		if (Db::queryModify('UPDATE `extras` SET 
			`payment_id` = ?, 
			`description` = ?,  
			`priceCZK` = ?, 
			`fakturoid_id` = ? 
			WHERE `id_extra` = ?', [$paymentId, $description, $price, $fakturoidExtraId, $extraId])
		)
			return [
				's' => 'success',
				'cs' => 'Položka úspěšně uložena',
				'en' => 'Extra is successfully saved'
			];
		else
			return [
				's' => 'error',
				'cs' => 'Položku se nepovedlo uložit',
				'en' => 'Extra is not saved corrently'
			];
	}
	
	public function deleteExtra($extraId) {
		if (empty($extraId))
			return [
				's' => 'info',
				'cs' => 'Nebyla určena žádná položka',
				'en' => 'We didn\'t catch any extra'
			];
		
		if (Db::queryModify('DELETE FROM `extras` WHERE `id_extra` = ?', [$extraId]))
			return [
				's' => 'success',
				'cs' => 'Položka úspěšně smazána',
				'en' => 'Extra is successully deleted'
			];
		else
			return [
				's' => 'error',
				'cs' => 'Položku se nepovedlo smnazat',
				'en' => 'Extra is not deleted'
			];
	}
	
	public function getPaymentIdOfExtra($extraId) {
		return Db::querySingleOne('SELECT `payment_id` FROM `extras` WHERE `id_extra` = ?', [$extraId]);
	}
	
	public function getStatusOfPayment($paymentId) {
		return Db::querySingleOne('SELECT `status` FROM `payments` WHERE `id_payment` = ?', [$paymentId]);
	}
	
	public function getStatusOfPaymentFromExtraId($extraId) {
		return Db::querySingleOne('SELECT `status` FROM `payments`
 			JOIN `extras` ON `extras`.`payment_id` = `payments`.`id_payment`
			WHERE `id_extra` = ?', [$extraId]);
	}
	
	public function getBlankExtras($id_user) {
		return Db::queryAll('SELECT `id_extra`,`description`,`priceCZK`,`vat` FROM `extras`
			WHERE `blank_user_id` = ? AND `payment_id` IS NULL', [$id_user]);
	}
}