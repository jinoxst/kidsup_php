<?php
require_once 'DAO.php';

class EventDAO extends DAO {
    public function getEventList($center_id, $class_id, $member_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getEventList(?,?,?,?,?) ';
        return $con->query($query, array($center_id, $class_id, $member_id, $pageno, $pagesize));
    }

    public function web_getEventListTotalCount($center_id, $class_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getEventListTotalCount(?,?,?) ';
        return $con->fetchOne($query,array($center_id, $class_id, $member_id));
    }

    public function getEventDetail($event_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getEventDetail(?,?) ';
        return $con->fetchRow($query, array($event_id, $member_id));
    }

    public function getEventReplyList($event_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getEventReplyList2(?,?,?) ';
        return $con->query($query, array($event_id, $pageno, $pagesize));
    }

    public function postEventContents($type, $center_id, $class_id, $member_id, $title, $contents, $address, $date) {
        $con = parent::getUpdateConnection();
        $query  = ' call postEventContents(?,?,?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($type, $center_id, $class_id, $member_id, $title, $contents, $address, $date));
    }

    public function getEventTargetPushList($noticeId) {
        $con = parent::getUpdateConnection();
        $query  = ' call getEventTargetPushList(?) ';
        return $con->query($query, array($noticeId));
    }

    public function postEventImage($event_id, $img, $w, $h) {
        $con = parent::getUpdateConnection();
        $query  = ' call postEventImage(?,?,?,?) ';
        return $con->fetchOne($query, array($event_id, $img, $w, $h));
    }

    public function postEventReply($event_id, $member_id, $kids_id, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postEventReply2(?,?,?,?) ';
        return $con->fetchOne($query, array($event_id, $member_id, $kids_id, $contents));
    }

    public function getEventReplyAddPushInfo($event_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getEventReplyAddPushInfo(?) ';
        return $con->query($query, array($event_id));
    }

    public function getEventReplyCnt($event_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getEventReplyCnt(?) ';
        return $con->fetchOne($query, array($event_id));
    }

    public function sendEventRead($event_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call sendEventRead(?,?) ';
        return $con->fetchOne($query, array($event_id, $member_id));
    }

    public function getEventReadOverPushInfo($event_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getEventReadOverPushInfo(?) ';
        return $con->query($query, array($event_id));
    }

    public function getNotReadMemberList($event_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMemberListNotReadEvent(?) ';
        return $con->query($query, array($event_id));
    }

    public function getNotReadEventMemberList($event_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getNotReadEventMemberList(?) ';
        return $con->query($query, array($event_id));
    }

    public function deleteEvent($event_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteEvent(?) ';
        return $con->fetchOne($query, array($event_id));
    }

    public function plusEventGoodCnt($event_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call plusEventGoodCnt(?,?) ';
        return $con->fetchOne($query, array($event_id, $member_id));
    }

    public function addScheduleDataByThread($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time) {
        $con = parent::getUpdateConnection();
        $query  = ' call addScheduleDataByThread2(?,?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time));
    }

    public function deleteEventReply($event_id, $reply_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteEventReply(?,?) ';
        return $con->fetchOne($query, array($event_id, $reply_id));
    }

    public function updateEventContents($event_id, $class_id, $title, $contents, $address, $date) {
        $con = parent::getUpdateConnection();
        $query  = ' call updateEventContents(?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($event_id, $class_id, $title, $contents, $address, $date));
    }

    public function deleteEventImage($event_id, $img_name) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteEventImage(?,?) ';
        return $con->fetchOne($query, array($event_id, $img_name));
    }
}