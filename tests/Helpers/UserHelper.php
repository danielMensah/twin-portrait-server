<?php
/**
 * Created by PhpStorm.
 * User: MrDan
 * Date: 23/11/2017
 * Time: 16:50
 */

class UserHelper {

    /**
     * @param $email
     * @return string
     */
    public function removeUser($email) {
        return json_encode(array(
            'response' => "User with $email has been removed."
        ));
    }

}