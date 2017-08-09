<?php
require_once 'DbManager.php';
class DAO {
    protected function getSelectConnection(){
        return DbManager::getConnection(0);
    }

    protected function getUpdateConnection(){
        return DbManager::getConnection(1);
    }

    public function getMemberClassList($member_id){
    	$con = $this->getUpdateConnection();
    	$query  = ' call getMemberClassList(?) ';
        return $con->query($query, array($member_id));
    }

    public function getMngClassList($center_id){
        $con = $this->getUpdateConnection();
        $query  = ' call getMngClassList(?) ';
        return $con->query($query, array($center_id));
    }

    public function expireDeviceToken($registrationId) {
        $con = $this->getUpdateConnection();
        $query  = ' call expireDeviceToken(?) ';
        return $con->fetchOne($query, array($registrationId));
    }
}