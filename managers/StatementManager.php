<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 15:18
 */
class StatementManager {

    public function handleStatementException(PDOStatement $sql, $msg) {
        try {
            if (!$sql->execute()) {
                throw new PDOException($msg, 500);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return true;
    }

}