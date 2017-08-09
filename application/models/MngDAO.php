<?php
require_once 'DAO.php';

class MngDAO extends DAO {
    public function getApprovalList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getAskApprovalList(?,?,?,?,?) ';
        return $con->query($query, $param);
    }

    public function web_getApprovalListTotalCount($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getApprovalListTotalCount(?,?) ';
        return $con->fetchOne($query,$param);
    }

    public function appvove($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call doAskApprove(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getApproveRequestSuccessInfo($member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getApproveRequestSuccessInfo(?) ';
        return $con->query($query, array($member_id));
    }

    public function reject($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteAskApprove(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getApproveRequestRejectInfo($member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getApproveRequestRejectInfo(?) ';
        return $con->query($query, array($member_id));
    }

    public function getAttendanceInfo($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getAttendanceInfo(?,?,?) ';
        return $con->query($query, $param);
    }

    public function checkAttendance($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call checkAttendance(?,?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getAttendanceCheckMemberList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getAttendanceCheckMemberList(?,?,?) ';
        return $con->query($query, $param);
    }

    public function getMemberClassLeastOne($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getMemberClassLeastOne(?) ';
        return $con->fetchOne($query, $param);
    }

    public function getMngClassList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMngClassList(?) ';
        return $con->query($query, $param);
    }

    public function updateClassInfo($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call updateClassInfo(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function deleteClassInfo($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteClassInfo(?) ';
        return $con->fetchOne($query, $param);
    }

    public function postClassInfo($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call postClassInfo(?,?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getKidsList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getKidsList(?,?) ';
        return $con->query($query, $param);
    }

    public function voidKidsApproval($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call voidKidsApproval(?) ';
        return $con->fetchOne($query, $param);
    }

    public function changeKidsClass($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call changeKidsClass(?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getTeacherList($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getTeacherList_121(?) ';
        return $con->query($query, $param);
    }

    public function voidTeacherApproval($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call voidTeacherApproval(?) ';
        return $con->fetchOne($query, $param);
    }

    public function changeTeacherClass($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call changeTeacherClass_121(?,?) ';
        return $con->fetchOne($query, $param);
    }

    public function getInvitationCode($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call getInvitationCode(?) ';
        return $con->fetchOne($query, $param);
    }

    public function setInvitationCode($param) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_setInvitationCode(?) ';
        return $con->fetchOne($query, $param);
    }
}