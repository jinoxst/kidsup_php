<?php
require_once 'DAO.php';

class AlbumDAO extends DAO {
    public function getAlbumList($member_id, $kids_id, $pageno, $pagesize) {
        $con = parent::getUpdateConnection();
        $query  = ' call getAlbumList2(?,?,?,?) ';
        return $con->query($query, array($member_id, $kids_id, $pageno, $pagesize));
    }

    public function web_getAlbumListTotalCount($member_id, $kids_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call web_getAlbumListTotalCount(?,?) ';
        return $con->fetchOne($query,array($member_id, $kids_id));
    }

    public function addAlbumData($member_id, $kids_id, $center_id, $thread_type, $thread_id,  $filename){
        $con = parent::getUpdateConnection();
        $query  = ' call addAlbumData(?,?,?,?,?,?) ';
        return $con->fetchOne($query, array($member_id, $kids_id, $center_id, $thread_type, $thread_id,  $filename));
    }

    public function deleteAlbumData($member_id, $kids_id, $idx) {
        $con = parent::getUpdateConnection();
        $query  = ' call deleteAlbumData(?,?,?) ';
        return $con->fetchOne($query, array($member_id, $kids_id, $idx));
    }
}