<?php
require_once 'DAO.php';

class MamaTalkDAO extends DAO {
    public function getMamaTalkList($center_id, $class_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMamaTalkList(?,?,?,?) ';
        return $con->query($query, array($center_id, $class_id, $pageno, $pagesize));
    }

    public function web_getMamaTalkListTotalCount($center_id, $class_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getMamaTalkListTotalCount(?,?) ';
        return $con->fetchOne($query,array($center_id, $class_id));
    }

    public function getMamaTalkDetail($mamatalk_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getMamaTalkDetail(?) ';
        return $con->fetchRow($query, array($mamatalk_id));
    }

    public function getMamaTalkReplyList($mamatalk_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMamaTalkReplyList2(?,?,?) ';
        return $con->query($query, array($mamatalk_id, $pageno, $pagesize));
    }

    public function postMamaTalkContents($center_id, $class_id, $member_id, $kids_id, $title, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postMamaTalkContents2(?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $class_id, $member_id, $kids_id, $title, $contents));
    }

    public function getMamaTalkTargetPushList($noticeId) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMamaTalkTargetPushList(?) ';
        return $con->query($query, array($noticeId));
    }

    public function postMamaTalkImage($mamatalk_id, $img, $w, $h) {
        $con = parent::getUpdateConnection();
        $query  = ' call postMamaTalkImage(?,?,?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $img, $w, $h));
    }

    public function postMamaTalkReply($mamatalk_id, $member_id, $kids_id, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call postMamaTalkReply2(?,?,?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $member_id, $kids_id, $contents));
    }

    public function getMamaTalkReplyAddPushInfo($mamatalk_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call getMamaTalkReplyAddPushInfo(?) ';
        return $con->query($query, array($mamatalk_id));
    }

    public function getMamaTalkReplyCnt($mamatalk_id){
        $con = parent::getUpdateConnection();
        $query  = ' call web_getMamaTalkReplyCnt(?) ';
        return $con->fetchOne($query, array($mamatalk_id));
    }

    public function sendMamaTalkRead($mamatalk_id, $member_id){
        $con = parent::getUpdateConnection();
        $query  = ' call sendMamaTalkRead(?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $member_id));
    }

    public function deleteMamaTalk($mamatalk_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteMamaTalk(?) ';
        return $con->fetchOne($query, array($mamatalk_id));
    }

    public function plusMamaTalkGoodCnt($mamatalk_id, $member_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call plusMamaTalkGoodCnt(?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $member_id));
    }

    public function addScheduleDataByThread($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time) {
        $con = parent::getUpdateConnection();
        $query  = ' call addScheduleDataByThread2(?,?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($center_id, $date, $member_id, $kids_id, $thread_type, $thread_id, $time));
    }

    public function deleteMamaTalkReply($mamatalk_id, $reply_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteMamaTalkReply(?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $reply_id));
    }

    public function updateMamaTalkContents($mamatalk_id, $class_id, $title, $contents) {
        $con = parent::getUpdateConnection();
        $query  = ' call updateMamaTalkContents(?,?,?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $class_id, $title, $contents));
    }

    public function deleteMamaTalkImage($mamatalk_id, $img_name) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteMamaTalkImage(?,?) ';
        return $con->fetchOne($query, array($mamatalk_id, $img_name));
    }
}