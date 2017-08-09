<?php
require_once 'DAO.php';

class SettingDAO extends DAO {
    public function updateMemberType3Info($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call updateMemberType3Info(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function chgPw($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call chgPw(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getPushReceiveYnList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getPushReceiveYnList(?) ';
        return $con->fetchRow($query, $param);
    }

    public function setPushReceiveYn($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call setPushReceiveYn(?,?,?) ';
        return $con->fetchOne($query, $param);
    }
}