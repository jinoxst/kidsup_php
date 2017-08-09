<?php
require_once 'DAO.php';

class ContactDAO extends DAO {
    public function getContactList($center_id, $date, $member_id, $kids_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getContactList2(?,?,?,?,?,?) ';
        return $con->query($query, array($center_id, $date, $member_id, $kids_id, $pageno, $pagesize));
    }

    public function getKidsList($center_id, $class_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getKidsList(?,?) ';
        return $con->query($query, array($center_id, $class_id));
    }

    public function getNotReadMemberList($contact_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMemberListNotReadContact(?) ';
        return $con->query($query, array($contact_id));
    }

    public function postContactContents($center_id, $member_id, $kids_id, $contents, $to_kids_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call postContactContents2(?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $member_id,$kids_id, $contents, $to_kids_id));
    }

    public function web_getContactListTotalCount($center_id, $date, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getContactListTotalCount(?,?,?) ';
        return $con->fetchOne($query,array($center_id, $date, $member_id));
    }

    public function postContactImage($contact_id, $img, $w, $h) {
        $con = parent::getUpdateConnection();
        $query  = ' call postContactImage(?,?,?,?) ';
        return $con->fetchOne($query, array($contact_id, $img, $w, $h));
    }

    public function getContactTargetPushList($contactId) {
        $con = parent::getUpdateConnection();
        $query  = ' call getContactTargetPushList(?) ';
        return $con->query($query, array($contactId));
    }

    public function getContactDetail($contact_id, $member_id, $kids_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getContactDetail(?,?,?) ';
        return $con->fetchRow($query, array($contact_id, $member_id, $kids_id));
    }

    public function getContactReplyList($contact_id, $member_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getContactReplyList2(?,?,?,?) ';
        return $con->query($query, array($contact_id, $member_id, $pageno, $pagesize));
    }

    public function deleteContact($contact_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteContact(?) ';
        return $con->fetchOne($query, array($contact_id));
    }

    public function postContactReply($contact_id, $member_id, $kids_id, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postContactReply2(?,?,?,?) ';
        return $con->fetchOne($query, array($contact_id, $member_id, $kids_id, $contents));
    }

    public function getContactReplyAddPushInfo($contact_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getContactReplyAddPushInfo(?,?) ';
        return $con->query($query, array($contact_id, $member_id));
    }

    public function sendContactRead($contact_id, $member_id, $kids_id){
        $con = parent::getUpdateConnection();
        $query  = ' call sendContactRead2(?,?,?) ';
        return $con->fetchOne($query, array($contact_id, $member_id, $kids_id));
    }

    public function getContactReadOverPushInfo($contact_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getContactReadOverPushInfo(?,?) ';
        return $con->query($query, array($contact_id, $member_id));
    }

    public function getNotReadContactMemberList($contact_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getNotReadContactMemberList(?) ';
        return $con->query($query, array($contact_id));
    }

    public function getClassList($center_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMngClassList(?) ';
        return $con->query($query, array($center_id));
    }

    public function getMemberClassList($member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMemberClassList(?) ';
        return $con->query($query, array($member_id));
    }

    public function getContactReplyCnt($contact_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getContactReplyCnt(?) ';
        return $con->fetchOne($query, array($contact_id));
    }
}