<?php
require_once 'DAO.php';

class LoginDAO extends DAO {
    public function logincheck($email, $pw) {
        $con = parent::getUpdateConnection();
        $query  = ' call login(?,?) ';
        return $con->query($query, array($email, $pw));
    }

    public function reqIssuePw($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call reqIssuePw(?,?,?) ';
        return $con->fetchRow($query, $param);
    }
}