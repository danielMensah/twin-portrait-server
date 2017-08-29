<?php
    function registerUser($user){
        $dbh = getConnection();

        $user_name = $user['name'];
        $user_lastName = $user['lastName'];
        $email = strtolower($user['email']);

        if (!checkIfUserExists($email)) {
            $sql = $dbh->prepare("INSERT INTO users ( name, lastName, email ) VALUES ( '$user_name', '$user_lastName', '$email')");

            $response = [];
            if ( $sql->execute()) {
                $response = array(
                    'response' => 'updated',
                    'promoCode' => addPromoCode($email)
                );
            } else {
                $response = array(
                    'response' => 'error'
                );
            }

            return json_encode($response);
        } else {
            return json_encode(array('response' => 'User already exists'));
        }

    }

    function addPromoCode($email) {
        require '../util/promoCodeGeneratorUtil.php';

        $dbh = getConnection();
        $promo_code = generatePromoCode();

        $sql = $dbh->prepare("INSERT INTO promo_codes ( user_id, promo_code ) VALUES ((select id from users where email = '$email'), '$promo_code')");
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