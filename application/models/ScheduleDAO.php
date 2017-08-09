<?php
require_once 'DAO.php';

class ScheduleDAO extends DAO {
    public function getScheduleList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getScheduleList(?,?,?,?,?) ';
        return $con->query($query, $param);
    }

    public function addScheduleData($param){
        $con = parent::getUpdateConnection();
        $query  = ' call addScheduleData2(?,?,?,?,?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function deleteScheduleData($id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteScheduleData(?) ';
        return $con->fetchOne($query, array($id));
    }

    public function updateScheduleData($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_updateScheduleData(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function changeScheduleDate($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_changeScheduleDate(?,?) ';
        return $con->fetchOne($query, $param);
    }
}