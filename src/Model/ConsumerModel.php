<?php
/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 26/10/2017
 * Time: 11:29
 */
require_once "UserModel.php";

class ConsumerModel extends UserModel {

    private $feedback;
    private $registered_at;
    private $satisfaction;

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

    /**
     * @return mixed
     */
    public function getSatisfaction() {
        return $this->satisfaction;
    }

    /**
     * @param mixed $satisfaction
     */
    public function setSatisfaction($satisfaction) {
        $this->satisfaction = $satisfaction;
    }

}