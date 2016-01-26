<?php

class BookingController extends Controller {
	function process($parameters) {
		$booking = new Booking();

		$data = [
			'roomId' => $parameters[0],
			'date' => $parameters[1]
		];
		$data = $booking->sanitize($data);
		$this->data['rooms'] = $booking->getRooms($this->language);
		if (!$booking->validateData($data, $this->data['rooms'])) $this->redirect('error');

		//choose room
		if (empty($data['roomId'])) {
			$this->header['title'] = [
				'cs' => 'Rezervace - výběr místnosti',
				'en' => 'Booking - room choose'];
			$this->view = 'bookingRoomChoose';
		} else {
			if (empty($data['term'])) {
				$data['date'] = date('Y-m-d H:i:s', time());
			}

			//reserve term
			$this->data['terms'] = $booking->returnFreeTimes($data['term'], $data['roomId']);
			$this->header['title'] = [
				'cs' => 'Rezervace',
				'en' => 'Booking'];
			$this->view = 'booking';
		}
	}
}