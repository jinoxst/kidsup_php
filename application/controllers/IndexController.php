<?php
require_once 'BaseController.php';

class IndexController extends BaseController
{
    public function indexAction()
    {
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $logger->info();
        $this->_redirect('contact');
    }
}