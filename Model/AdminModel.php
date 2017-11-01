<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 26/10/2017
 * Time: 11:26
 */

require "./UserModel.php";

class AdminModel extends UserModel {

    private $username;
    private $password;

    /**
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }



}