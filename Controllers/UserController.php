<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 21:30
 */
require_once __DIR__ . "/../config/DbConnection.php";
require_once __DIR__ . "/../Model/UserModel.php";

class UserController {

    protected $dbh;
    private $model;

    /**
     * UserController constructor.
     * @param null|UserModel $model
     * @internal param $dbh
     */
    public function __construct(UserModel $model = null) {
        $this->dbh = new DbConnection();
        $this->model = $model;
    }

    public function registerUser() {
        $email = $this->model->getEmail();
        $feedback = $this->model->getFeedback();

        if (!$this->checkIfUserExists()) {
            $sql = $this->dbh->getConnection()->prepare("INSERT INTO users ( email, feedback ) VALUES ( :email, :feedback )");
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':feedback', $feedback, PDO::PARAM_STR);

            return $sql->execute() ? json_encode(array(
                'response' => 'updated',
                'promoCode' => $this->addPromoCode()
            )) : json_encode(array(
                'response' => 'error'
            ));
        } else {
            return json_encode(array('response' => 'Email already exists'));
        }
    }

    public function addPromoCode() {
        $promo_code = $this->generatePromoCode();
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO promo_codes ( user_id, promo_code ) VALUES ((select id from users where email = :email), :promo_code)");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':promo_code', $promo_code, PDO::PARAM_STR);

        $sql->execute();

        return $promo_code;
    }

    public function checkIfUserExists() {
        $email = $this->model->getEmail();

        $sql = $this->dbh->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->execute();

        return $sql->fetchColumn();
    }

    protected function generatePromoCode() {
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