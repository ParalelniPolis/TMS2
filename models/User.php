<?php

class User extends Model {
    //TODO make only one creation of Fakturoid object for whole class
    //TODO split User and Ivoice parts
    public function getUserData($userId, $lang) {
        //TODO optimalize MySQL
        $user = Db::queryOne('SELECT `id_user`,`first_name`,`last_name`,`telephone`,`active`,`email`,`name`,`tariffCZE`,`tariffENG`,`invoicing_start_date`,`ic`
                              FROM `users`
                              JOIN `tariffs` ON `id_tariff` = `user_tariff`
                              JOIN `places` ON `place_id` = `places`.`id`
                              WHERE `id_user` = ?', [$userId]);
        $tariff = Db::queryOne('SELECT `id_tariff`, `priceCZK`,`tariffCZE`,`tariffENG`
                                FROM `users`
                                JOIN `tariffs` ON `users`.`user_tariff` = `tariffs`.`id_tariff`
                                JOIN `places` ON `place_id` = `places`.`id`
                                WHERE  `id_user` = ?', [$userId]);
        $payments = Db::queryAll('SELECT `id_payment`,`bitcoinpay_payment_id`,`id_payer`,`payed_price_BTC`,`payment_first_date`,`status`,`tariff`,`invoice_fakturoid_id`
                                  FROM `payments` WHERE `id_payer` = ?
                                  ORDER BY `payment_first_date` DESC', [$userId]);
        //czech translation
        foreach ($payments as &$p) {
            $p['status'] = $this->translatePaymentStatus($p['status'], $lang);
        }

        return ['user' => $user,
            'tariff' => $tariff,
            'payments' => $payments];
    }

    public function actualizePayments($user, $payments, $tariffs, $lang) {
        $bitcoinPay = new Bitcoinpay();
        $messages = [];
        foreach ($payments as $payment) {
            //don't check already confirmed playments
            if (!$payment['status'] == 'confirmed') {
                $paymentId = $payment['id_payment'];
                $bitcoinpayId = $payment['bitcoinpay_payment_id'];

                if (empty($bitcoinpayId)) $result['status'] = 'unpaid';
                else $result = $bitcoinPay->getTransactionDetails($bitcoinpayId);

                //invalid response
                if (empty($result)) {
                    $messages[] = ['s' => 'info',
                        'cs' => 'Nepovedlo se nám spojit se se serverem bitcoinpay.com - některé platby můžou být neaktualizované',
                        'en' => 'We failed at connection with bitcoinpay.com - some payments can be outdated'];
                } else {
                    $newStatus = $result['status'];
                    //when status is different, inform user
                    if ($newStatus != $payment['status']) {
                        Db::queryModify('UPDATE `payments` SET `status` = ?
                            WHERE `id_payment` = ?', [$newStatus, $paymentId]);
                        //and when receive money, make invoice payed
                        if ($newStatus == 'received') {
                            $exchangeRate = $this->getExchangeRate();
                            if ($lang == 'cs') $tariffName = $tariffs['tariffCZE'];
                            else $tariffName = $tariffs['tariffENG'];
                            if (!$exchangeRate) $messages[] = ['s' => 'info',
                                'cs' => 'Nepovedlo se nám ze serveru bitcoinpay.com získat převodní kurz',
                                'en' => 'We failed parsing exchange amount from server bitcoinpay.com'];
                            $invoiceId = $this->createInvoice($user, $tariffs['priceCZK'], $exchangeRate, $tariffName, $payment['payed_price_BTC']);
                            $this->setFakturoidInvoicePayed($invoiceId);
                            Db::queryModify('UPDATE `payments`
                                SET `bitcoinpay_payment_id` = ?, `invoice_fakturoid_id` = ?, `payed_price_BTC` = ?, `exchange_rate` = ?
                                WHERE `id_payment` = ?',
                                [$result['payment_id'], $invoiceId, $result['price'], $exchangeRate, $paymentId]);
                        }
                        $messages[] = $bitcoinPay->getStatusMessage($newStatus);
                    }
                }
            }
        }
        return $messages;
    }

    public function makeNewPayments($user, $tariff) {
        $userId = $user['id_user'];
        $active = $user['active'];
        $currentDate = date('Y-m-d');
        if ($active) {
            $startOfLastGeneratedMonth = Db::querySingleOne('
                SELECT `payment_first_date` FROM `payments`
                WHERE `id_payer` = ?
                ORDER BY `payment_first_date` DESC', [$userId]
            );
            //new user
            if (empty($startOfLastGeneratedMonth)) {
                $startDate = $user['invoicing_start_date'];
                $this->createPayment($user, $tariff, $startDate);
                return true;
                //old user
            } else {
                $new = false;
                $endOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month - 1 day'));
                while (strtotime($endOfLastGeneratedMonth) < strtotime($currentDate)) {
                    $startOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month'));
                    $endOfLastGeneratedMonth = date('Y-m-d', strtotime($startOfLastGeneratedMonth.' + 1 month - 1 day'));
                    $this->createPayment($user, $tariff, $startOfLastGeneratedMonth);
                    $new = true;
                }
            }
            if ($new == true) return true; else return false;
        } else return false;
    }

    private function createPayment($user, $tariff, $beginningDate) {
        $userId = $user['id_user'];
        $tariffId = $tariff['id_tariff'];
        Db::queryModify('INSERT INTO `payments` (`id_payer`, `payment_first_date`, `status`, `time_generated`, `tariff`)
                         VALUES (?, ?, ?, NOW(), ?)', [$userId, $beginningDate, 'unpaid', $tariffId]);
    }

    private function createInvoice($user, $price, $exchangeRate, $tariffName, $issuedDate) {
        $subject = ['name' => $user['first_name'].' '.$user['last_name'],
            'email' => $user['email']];
        try {
            $fakturoid = new Fakturoid(FAKTUROID_SLUG, FAKTUROID_EMAIL, FAKTUROID_API_KEY, FAKTUROID_USER_AGENT);
            $subjectId = FAKTUROID_SUBJECT_ID;
            $fakturoid->update_subject($subjectId, ['id' => $subjectId,
                'name' => $subject['name'],
                'email' => $subject['email']]);
            $lines = [['name' => 'tarif: '.$tariffName.', přesná částka: '.$price.' BTC',
                'quantity' => 1,
                'unit_price' => $price]];
            $invoice = $fakturoid->create_invoice(['subject_id' => $subjectId,
                'issued_on' => $issuedDate,
                'currency' => 'BTC',
                'exchange_rate' => $exchangeRate,
                'lines' => $lines]);
            $fakturoid->fire_invoice($invoice->id, 'deliver');
            return $invoice->id;
        } catch (FakturoidException $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $this->newTicket('error', 'Fakturoid', 'code: '.$code.', message: '.$message);
            $_SESSION['messages'][] = ['s' => 'error',
                'cs' => 'Nastal problém v komunikaci se serverem fakturoid.cz. Zkuste to prosím znovu za pár minut',
                'en' => 'We encoured a problem in communication on fakturoid.cz. Please try it again after a few minutes'];
            return false;
        }
    }

    private function setFakturoidInvoicePayed($invoiceId) {
        $fakturoid = new Fakturoid(FAKTUROID_SLUG, FAKTUROID_EMAIL, FAKTUROID_API_KEY, FAKTUROID_USER_AGENT);
        try {
            $fakturoid->fire_invoice($invoiceId, 'pay');
        } catch (FakturoidException $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $this->newTicket('error', 'class user func. setFakturoidInvoicePayed', 'code: '.$code.' with message: '.$message);
        }
    }

    public function getFakturoidInvoiceAsPdf($invoiceId) {
        $fakturoid = new Fakturoid(FAKTUROID_SLUG, FAKTUROID_EMAIL, FAKTUROID_API_KEY, FAKTUROID_USER_AGENT);
        return $fakturoid->get_invoice_pdf($invoiceId);
    }

    public function getUserIdFromInvoiceId($invoiceId) {
        return Db::querySingleOne('SELECT `id_payer` FROM `payments` WHERE `invoice_fakturoid_id` = ?', [$invoiceId]);
    }

    private function getExchangeRate() {
        //TODO add fallback solution
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://bitcoinpay.com/api/v1/rates/btc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        foreach ($result as $r) {
            if (array_key_exists('CZK', $r)) return $r['CZK'];
        }
        return false;
    }

    private function translatePaymentStatus($status, $lang) {
        $a = ['pending' => [
            'cs' => 'čekající',
            'en' => 'pending'
            ],
            'confirmed' => [
                'cs' => 'potvrzená',
                'en' => 'confirmed'
            ],
            'received' => [
                'cs' => 'přijato',
                'en' => 'received'
            ],
            'insufficient_amount' => [
                'cs' => 'nedostatečná částka',
                'en' => 'insufficient amount'
            ],
            'timeout' => [
                'cs' => 'platnost vypršela',
                'en' => 'payment payout'
            ],
            'paid_after_timeout' => [
                'cs' => 'zaplaceno pozdě',
                'en' => 'payed after payout'
            ],
            'invalid' => [
                'cs' => 'invalid',
                'en' => 'invalid'
            ],
            'unpaid' => [
                'cs' => 'nezaplaceno',
                'en' => 'unpaid'
            ],
            'refund' => [
                'cs' => 'vráceno',
                'en' => 'refund'
            ],
        ];
        return $a[$status][$lang];
    }
}