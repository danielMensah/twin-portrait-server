<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 21:30
 */
require "../config/DbConnection.php";
require "../Model/ConsumerModel.php";
require "../Managers/UtilManager.php";

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

    public function registerUser() {
        $email = $this->model->getEmail();
        $feedback = $this->model->getFeedback();
        $type = $this->model->getUserType();

        if (!$this->checkIfUserExists()) {
            $sql = $this->dbh->getConnection()->prepare("INSERT INTO users ( email, user_type ) VALUES ( :email, :type )");
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':type', $type, PDO::PARAM_STR);

            $this->utilManager->handleStatementException($sql, "Error while inserting user!");

            $sql = $this->dbh->getConnection()->prepare("INSERT INTO $type ( user_id, feedback ) VALUES ( (SELECT id from users WHERE email = :email), :feedback )");
            $sql->bindParam(':feedback', $feedback, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);

            $this->utilManager->handleStatementException($sql, "Error while inserting consumer!");

            return json_encode(array(
                'response' => 'updated',
                'promoCode' => $type === 'consumer' ? $this->addPromoCode() : null
            ));
        } else {
            return json_encode(array('response' => 'Email already exists'));
        }
    }

    private function addPromoCode() {
        $promo_code = $this->generatePromoCode();
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO promo_codes ( user_id, promo_code ) VALUES ((select id from users where email = :email), :promo_code)");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':promo_code', $promo_code, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while creating a promo code!");

        return $promo_code;
    }

    private function checkIfUserExists() {
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while checking if user exists!");

        return $sql->fetchColumn();
    }

    private function generatePromoCode() {
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}