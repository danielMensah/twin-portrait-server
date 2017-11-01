<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 26/10/2017
 * Time: 11:29
 */
require "./UserModel.php";

class ConsumerModel extends UserModel {

    private $feedback;
    private $registered_at;

    /**
     * @return mixed
     */
    public function getFeedback() {
        return $this->feedback;
    }

    /**
     * @param mixed $feedback
     */
    public function setFeedback($feedback) {
        $this->feedback = $feedback;
    }

    /**
     * @return mixed
     */
    public function getRegisteredAt() {
        return $this->registered_at;
    }

    /**
     * @param mixed $registered_at
     */
    public function setRegisteredAt($registered_at) {
        $this->registered_at = $registered_at;
    }


}