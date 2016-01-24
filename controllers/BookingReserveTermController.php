<?php

class BookingReserveTermController extends Controller {
	function process($parameters) {
		$booking = new Booking();

		$data = [
			'roomId' => $parameters[0],
			'term' => $parameters[1]
		];
		$data = $booking->sanitize($data);
		$rooms = $booking->getRooms($this->language);
		if (!$booking->validateReservationData($data, $rooms)) $this->redirect('error');

		//reserve term
		$this->messages = $booking->reserveTerm($data['roomId'], $data['term'], $_SESSION['id_user']);
		$this->redirect('booking');
	}
}