<?php

require_once __DIR__ . "/../../config/DbConnection.php";
require_once __DIR__ . "/../Model/ConsumerModel.php";
require_once __DIR__ . "/../Managers/UtilManager.php";

class UserController {

    private $dbh;
    private $utilManager;
    private $model;

    /**
     * UserController constructor.
     * @param null|ConsumerModel $model
     * @internal param $dbh
     */
    public function __construct(ConsumerModel $model = null) {
        $this->dbh = new DbConnection();
        $this->utilManager = new UtilManager();
        $this->model = $model;
    }

    /**
     * @param $model
     * @param $match
     * @return string
     */
    public function registerUser($model, $match) {
        $this->model = $model;

        $email = $this->model->getEmail();
        $feedback = $this->model->getFeedback();
        $type = $this->model->getUserType();
        $satisfaction = $this->model->getSatisfaction();

        if (!$this->checkIfUserExists()) {
            $sql = $this->dbh->getConnection()->prepare("INSERT INTO users ( email, user_type ) VALUES ( :email, :type )");
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':type', $type, PDO::PARAM_STR);

            $this->utilManager->handleStatementException($sql, "Error while inserting user!");

            $sql = $this->dbh->getConnection()->prepare("INSERT INTO $type ( user_id, feedback, satisfaction ) VALUES ( (SELECT id from users WHERE email = :email), :feedback, :satisfaction )");
            $sql->bindParam(':feedback', $feedback, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':satisfaction', $satisfaction, PDO::PARAM_STR);

            $this->utilManager->handleStatementException($sql, "Error while inserting consumer!");

            return json_encode(array(
                'response' => 'add',
                'promoCode' => $type === 'consumer' ? $this->addPromoCode() : null
            ));
        } else if ($match) {
            $sql = $this->dbh->getConnection()->prepare("UPDATE consumer SET satisfaction = :satisfaction WHERE user_id = (SELECT id from users WHERE email = :email)");
            $sql->bindParam(':satisfaction', $satisfaction, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);

            $this->utilManager->handleStatementException($sql, "Error while adding satisfaction!");

            return json_encode(array('response' => "update"));
        } else {
            return json_encode(array('response' => 'Email already exists'));
        }
    }

    /**
     * @return string
     */
    private function addPromoCode() {
        $promo_code = $this->generatePromoCode();
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO promo_codes ( user_id, promo_code ) VALUES ((select id from users where email = :email), :promo_code)");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':promo_code', $promo_code, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while creating a promo code!");

        return $promo_code;
    }

    /**
     * @return mixed
     */
    private function checkIfUserExists() {
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while checking if user exists!");

        return $sql->fetchColumn();
    }

    /**
     * @return string
     */
    public function generatePromoCode() {
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $email
     * @return string
     */
    public function removeUser($email) {
        $sql = $this->dbh->getConnection()->prepare("DELETE FROM users WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while removing user from database!");

        return json_encode(array(
            'response' => "User with $email has been removed."
        ));
    }
}