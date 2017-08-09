<?php
require_once 'DAO.php';

class ServicestartDAO extends DAO {
    public function doServiceStart($center_id) {
        $con = parent::getUpdateConnection();
        $query  = ' call doServiceStart(?) ';
        return $con->query($query, array($center_id));
    }
}