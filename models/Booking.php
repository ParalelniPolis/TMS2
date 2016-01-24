<?php

class Booking extends Model {

	public function validateData($data, $rooms = []) {
		if (!empty($data['roomId']) && !in_array($data['roomId'], $rooms))
			return false;
		if (!empty($data['date']) && !is_numeric($data['term']))
			return false;
		//no problem
		return true;
	}

	public function isTermFree($term, $room) {
		//TODO
		//overlapping:
		//startA < endB && startB < endA
		//magic! :)
		return true;
	}

	public function returnFreeTimes($date, $room) {
		//TODO
		return [];
	}

	public function getRooms($language) {
		switch ($language) {
			case 'cs':
				return Db::queryAll('SELECT `id_room`, `nameCZE`, `descriptionCZE` FROM `rooms`');
				break;
			case 'en':
				return Db::queryAll('SELECT `id_room`, `nameENG`, `descriptionENG` FROM `rooms`');
				break;
			default:
				return false;
		}
	}

	public function validateReservationData($data, $rooms = []) {
		if (!$this->validateTermFormat($data['term'])) return false;
		if (!in_array($data['roomId'], $rooms)) return false;
		if (!$this->isTermFree($data['term'], $data['roomId'])) return false;
		//no problem
		return true;
	}

	private function validateTermFormat ($dateTime) {
		return $dateTime === strtotime($dateTime);
	}

	public function reserveTerm($roomId, $term, $id_user) {
		Db::queryModify('INSERT INTO `bookings` (`user_id`, `room_id`, `term`)
			VALUES (?, ?, ?)', [$id_user, $roomId, $term]);
		return ['s' => 'success',
			'cs' => 'Termín úspěšně zarezervován',
			'en' => 'Term is successfully reserved'];
	}
}