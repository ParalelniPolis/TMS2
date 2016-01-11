<?php

class Bitcoinpay extends Model {

    function createPayment($paymentId) {
        $payment = $this->getPaymentData($paymentId);
        //$price = round($payment['price'], 5); //should not be needed due to rounding in database
        $price = $payment['price'];
        $email = $payment['email'];
        //make warning ticket if paying user is different from the owner
        if ($email != $_SESSION['username']) $this->newTicket('warning', 'function BitcoinPay->TryPayInvoice', 'users '.$_SESSION['username'].' invoice with id:'.$paymentId.' is payed by:'.$email);

        $ch = curl_init();
        //production
        curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);

        //TODO make valid address /PayInvoice/notify (about change of payment) (with security!)
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"settled_currency\": \"BTC\",
            \"return_url\": \"".ROOT."/PayInvoice/return/".$paymentId."\",
            \"notify_url\": \"".ROOT."/PayInvoice/notify\",
            \"notify_email\": \"".EMAIL."\",
            \"price\": $price,
            \"currency\": \"BTC\",
            \"reference\": {
                \"id_invoice\": \"$paymentId\",
                \"customer_email\": \"$email\"
            },
            \"item\": \"Invoice from TMS2 in PP\",
            \"lang\": \"cs\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Token ".BITCOINPAY_TOKEN
        ]);

        $response = curl_exec($ch);
        if ($response == false) {
            $this->newTicket('error', 'BitcoinPay curl', 'Error Number:'.curl_errno($ch)."Error String:".curl_error($ch));
            return false;
        }

        curl_close($ch);
        $data = json_decode($response, true);

        return $data['data'];
    }

    function getTransactionDetails($bitcoinpayId) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/transaction-history/$bitcoinpayId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Token ".BITCOINPAY_TOKEN
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);

        return $data['data'];
    }

    public function updatePayment($id, $data) {
        $bitcoinpayId = $data['payment_id'];
        $time = $data['time'];
        $status = $data['status'];
        Db::queryModify('UPDATE `payments` SET `bitcoinpay_payment_id` = ?, `status` = ?, `time_generated` = ?
                         WHERE `id_payment` = ?', [$bitcoinpayId, $status, $time, $id]);
    }

    function getStatusMessage($case) {
        switch ($case) {
            case 'pending': {
                $r = ['s' => 'info',
                    'cs' => 'Čekáme na zaplacení',
                    'en' => 'Waiting for payment'];
                break;
            }
            case 'confirmed':
            case 'received': {
                $r = ['s' => 'success',
                    'cs' => 'Úspěšně zaplaceno. Děkujeme!',
                    'en' => 'Successfully payed. Thanks!'];
                break;
            }
            case 'insufficient_amount': {
                $r = ['s' => 'error',
                    'cs' => 'Poslána menší částka než je vyžadováno',
                    'en' => 'Sent smaller amount than we expected'];
                break;
            }
            case 'invalid': {
                $this->newTicket('error', 'BitcoinPay->getStatusMessage', 'returned "invalid" value');
                $r = ['s' => 'error',
                    'cs' => 'Bohužel se něco po cestě pokazilo. Ozvěte se nám a dáme to do pořádku',
                    'en' => 'Sorry, something wrong on the way. Let us know and we will fix it'];
                break;
            }
            case 'timeout': {
                $r = ['s' => 'info',
                    'cs' => 'Platba nebyla zaplacena v daném čase a tak vypršela její platnost',
                    'en' => 'Payment was not payed in time and it\'s no longer valid'];
                break;
            }
            case 'paid_after_timeout': {
                $r = ['s' => 'error',
                    'cs' => 'Platba byla odeslána po splatnosti',
                    'en' => 'Payment was send after timeout'];
                break;
            }
            case 'refund': {
                $r = ['s' => 'info',
                    'cs' => 'Platba Vám byla vrácena',
                    'en' => 'Payment was refunded'];
                break;
            }
            case 'unpaid': {
                $r = ['s' => 'info',
                    'cs' => 'Nová nezaplacená faktura',
                    'en' => 'New unpaid invoice'];
                break;
            }
            default: {
                $this->newTicket('error', 'BitcoinPay->getStatusMessage', 'unexpected return value');
                $r = ['s' => 'error',
                    'cs' => 'Nečekaná návratová hodnota z bitcoinpay.com. Víme o tom a fičíme to spravit!',
                    'en' => 'Unexpected return value from bitcoinpay.com. We know about it and already on it!'];
            }
        }
        return $r;
    }

    public function getPaymentData($paymentId) {
        return Db::queryOne('SELECT `id_payment`,`bitcoinpay_payment_id`,`id_payer`,`price`,`email` FROM `payments`
                             JOIN `users` ON `users`.`id_user` = `payments`.`id_payer`
                             WHERE `id_payment` = ?', [$paymentId]);
    }

}