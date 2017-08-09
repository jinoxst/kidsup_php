<?php
require_once 'DAO.php';

class NoticeDAO extends DAO {
    public function getNoticeList($center_id, $class_id, $member_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getNoticeList(?,?,?,?,?) ';
        return $con->query($query, array($center_id, $class_id, $member_id, $pageno, $pagesize));
    }

    public function web_getNoticeListTotalCount($center_id, $class_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getNoticeListTotalCount(?,?,?) ';
        return $con->fetchOne($query,array($center_id, $class_id, $member_id));
    }

    public function getNoticeDetail($notice_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getNoticeDetail(?,?) ';
        return $con->fetchRow($query, array($notice_id, $member_id));
    }

    public function getNoticeReplyList($notice_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getNoticeReplyList2(?,?,?) ';
        return $con->query($query, array($notice_id, $pageno, $pagesize));
    }

    public function postNoticeContents($type, $center_id, $class_id, $member_id, $title, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postNoticeContents(?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($type, $center_id, $class_id, $member_id, $title, $contents));
    }

    public function getNoticeTargetPushList($noticeId) {
        $con = parent::getUpdateConnection();
        $query  = ' call getNoticeTargetPushList(?) ';
        return $con->query($query, array($noticeId));
    }

    public function postNoticeImage($notice_id, $img, $w, $h) {
        $con = parent::getUpdateConnection();
        $query  = ' call postNoticeImage(?,?,?,?) ';
        return $con->fetchOne($query, array($notice_id, $img, $w, $h));
    }

    public function postNoticeReply($notice_id, $member_id, $kids_id, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postNoticeReply2(?,?,?,?) ';
        return $con->fetchOne($query, array($notice_id, $member_id, $kids_id, $contents));
    }

    public function getNoticeReplyAddPushInfo($notice_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getNoticeReplyAddPushInfo(?) ';
        return $con->query($query, array($notice_id));
    }

    public function getNoticeReplyCnt($notice_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getNoticeReplyCnt(?) ';
        return $con->fetchOne($query, array($notice_id));
    }

    public function sendNoticeRead($notice_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call sendNoticeRead(?,?) ';
        return $con->fetchOne($query, array($notice_id, $member_id));
    }

    public function getNoticeReadOverPushInfo($notice_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getNoticeReadOverPushInfo(?) ';
        return $con->query($query, array($notice_id));
    }

    public function getNotReadMemberList($notice_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMemberListNotReadNotice(?) ';
        return $con->query($query, array($notice_id));
    }

    public function getNotReadNoticeMemberList($notice_id){
        $con = parent::getUpdateConnection();
        $query  = ' call getNotReadNoticeMemberList(?) ';
        return $con->query($query, array($notice_id));
    }

    public function deleteNotice($notice_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteNotice(?) ';
        return $con->fetchOne($query, array($notice_id));
    }

    public function plusNoticeGoodCnt($notice_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call plusNoticeGoodCnt(?,?) ';
        return $con->fetchOne($query, array($notice_id, $member_id));
    }

    public function addScheduleDataByThread($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time) {
        $con = parent::getUpdateConnection();
        $query  = ' call addScheduleDataByThread2(?,?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time));
    }

    public function deleteNoticeReply($notice_id, $reply_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteNoticeReply(?,?) ';
        return $con->fetchOne($query, array($notice_id, $reply_id));
    }

    public function updateNoticeContents($notice_id, $class_id, $title, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call updateNoticeContents(?,?,?,?) ';
        return $con->fetchOne($query, array($notice_id, $class_id, $title, $contents));
    }

    public function deleteNoticeImage($notice_id, $img_name) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteNoticeImage(?,?) ';
        return $con->fetchOne($query, array($notice_id, $img_name));
    }
}