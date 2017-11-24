<?php
/**
 * Created by PhpStorm.
 * User: MrDan
 * Date: 24/11/2017
 * Time: 12:44
 */

class UtilManagerHelper {

    /**
     * @param $msg
     */

    public function getHandleStatementExceptionHelper($msg) {
        throw new PDOException($msg, 500);
    }

}