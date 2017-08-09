<?php
require_once 'DAO.php';

class IssuepwDAO extends DAO {
    public function issuePw($member_id, $seq) {
        $con = parent::getUpdateConnection();
        $query  = ' call issuePw(?,?) ';
        return $con->query($query, array($member_id, $seq));
    }
}