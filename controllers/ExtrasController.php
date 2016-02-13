<?php

class ExtrasController extends Controller {
	
	function process($parameters) {
		$extras = new Extras();
		$action = $extras->sanitize($parameters[0]);
		
		//TODO add Fakturoid updates
		switch ($action) {
			case 'add':
				$paymentId = $extras->sanitize($_POST['paymentId']);
				$price = $extras->sanitize($_POST['price']);
				$description = $extras->sanitize($_POST['description']);
				
				$result = $extras->checkAddValues($paymentId, $price, $description);
				if ($result['s'] == 'success') {
					$result = $extras->addExtra($paymentId, $price, $description);
				}
				$this->messages[] = $result;
				$this->redirect('checkUsers');
				break;
			
			case 'delete':
				$extraId = $parameters[1];
				$result = $extras->deleteExtra($extraId);
				$this->messages[] = $result;
				$this->redirect('checkUsers');
				break;
			
			default:
				$this->redirect('error');
		}
	}
}