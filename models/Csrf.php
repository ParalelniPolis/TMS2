<?php

class Csrf extends Model {
    public static function getCsrfToken () {
        $csrfToken = self::getRandomHash();
        //added extra layer with adding actual uri into hash
        $actualUri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $csrfRefererToken = hash('sha512', $actualUri . $csrfToken, false);
        Db::queryModify('INSERT INTO `csrf` (`user_id`, `token`, `active`, `timestamp`)
                         VALUES (?, ?, 1, NOW())', [$_SESSION['id_user'], $csrfRefererToken]);
        return $csrfToken;
    }

    public static function validateCsrfRequest($returnedToken) {
        $storedToken = Db::querySingleOne('SELECT `token` FROM `csrf`
                                           WHERE `user_id` = ? AND `active` = 1
                                           ORDER BY `id` DESC',
            [$_SESSION['id_user']]);
        //unactive all entries
        Db::queryModify('UPDATE `csrf` SET `active` = 0 WHERE `user_id` = ? AND `active` = 1',
            [$_SESSION['id_user']]);

        //add referer uri into hash to get stored value
        $returnedRefererToken = hash('sha512', $_SERVER['HTTP_REFERER'] . $returnedToken, false);

        if ($storedToken == $returnedRefererToken) return true;
        else {
            self::newTicket('warning', $_SESSION['id_user'], 'Possible CSRF attack (returned false on stored token '. $storedToken);
            return false;
        }
    }
}