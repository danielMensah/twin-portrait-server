<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 11:40
 */

class UserModel {

    private $email;
    private $userType;

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getUserType() {
        return $this->userType;
    }

    /**
     * @param mixed $userType
     */
    public function setUserType($userType) {
        $this->userType = $userType;
    }


}