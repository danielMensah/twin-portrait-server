<?php
    function registerUser($user){
        $dbh = getConnection();

        $email = strtolower($user['email']);
        $feedback = $user['feedback'];

        if (!checkIfUserExists($email)) {
            $sql = $dbh->prepare("INSERT INTO users ( email, feedback ) VALUES ( :email, :feedback )");
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':feedback', $feedback, PDO::PARAM_STR);

            return $sql->execute() ? json_encode(array(
                'response' => 'updated',
                'promoCode' => addPromoCode($email)
            )) : json_encode(array(
                'response' => 'error'
            ));
        } else {
            return json_encode(array('response' => 'Email already exists'));
        }

    }

    function addPromoCode($email) {
        require '../util/promoCodeGeneratorUtil.php';

        $dbh = getConnection();
        $promo_code = generatePromoCode();

        $sql = $dbh->prepare("INSERT INTO promo_codes ( user_id, promo_code ) VALUES ((select id from users where email = :email), :promo_code)");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':promo_code', $promo_code, PDO::PARAM_STR);

        $sql->execute();

        return $promo_code;
    }

    function checkIfUserExists($email) {
        $dbh = getConnection();

        $sql = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->execute();

        return $sql->fetchColumn();
    }