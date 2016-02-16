<?php

class Extras extends Model {
	
	//TODO make more specific errors
	public function checkAddValues($paymentId, $price, $description) {
		$paymentIdFromDb = Db::querySingleOne('SELECT `id_payment` FROM `payments`
			WHERE `id_payment` = ?', [$paymentId]);
		if ($paymentId == $paymentIdFromDb && is_numeric($price) && strlen($description) <= 120)
			return ['s' => 'success'];
		else 
			return ['s' => 'error', 
				'cs' => 'Neplatné zadání', 
				'en' => 'Incorrect input'];
	}
	
	public function addExtra($paymentId, $price, $description) {
		if (Db::queryModify('INSERT INTO `extras` (`payment_id`, `description`, `priceCZK`)
 			VALUES (?, ?, ?)', [$paymentId, $description, $price]))
			return ['s' => 'success', 
				'cs' => 'Položka úspěšně uložena', 
				'en' => 'Extra is successfully saved'];
		else 
			return ['s' => 'error', 
				'cs' => 'Položku se nepovedlo uložit', 
				'en' => 'Extra is not saved corrently'];
	}
	
	public function deleteExtra($extraId) {
		if (empty($extraId)) return ['s' => 'info',
			'cs' => 'Nebyla určena žádná položka',
			'en' => 'We didn\'t catch any extra'];
		if (Db::queryModify('DELETE FROM `extras` WHERE `id_extra` = ?', [$extraId]))
			return ['s' => 'success',
				'cs' => 'Položka úspěšně smazána',
				'en' => 'Extra is successully deleted'];
		else
			return ['s' => 'error',
				'cs' => 'Položku se nepovedlo smnazat',
				'en' => 'Extra is not deleted'];
	}
}