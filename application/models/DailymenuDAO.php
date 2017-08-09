<?php
require_once 'DAO.php';

class DailymenuDAO extends DAO {
    public function getDailymenuList($center_id, $class_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getDailymenuList(?,?,?,?) ';
        return $con->query($query, array($center_id, $class_id, $pageno, $pagesize));
    }

    public function web_getDailymenuListTotalCount($center_id, $class_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getDailymenuListTotalCount(?,?) ';
        return $con->fetchOne($query,array($center_id, $class_id));
    }

    public function postDailymenuData($center_id, $date, $title, $member_id, $filename,  $class_id){
        $con = parent::getUpdateConnection();
        $query  = ' call postDailymenuData(?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $date, $title, $member_id, $filename, $class_id));
    }

    public function getDailymenuTargetPushList($center_id, $class_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getDailymenuTargetPushList(?,?,?) ';
        return $con->query($query, array($center_id, $class_id, $member_id));
    }

    public function deleteDailymenuData($center_id, $date, $filename) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteDailymenuData(?,?,?) ';
        return $con->fetchOne($query, array($center_id, $date, $filename));
    }
}