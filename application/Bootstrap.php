<?php
require_once APPLICATION_PATH.'/library/Constant.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Util.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	// protected function _initView()
 //    {
 //        $view = new Zend_View();
 //        // more initialization...
 
 //        return $view;
 //    }

	// public function _initUserSession()
 //    {
 //    	$logger = new Logger(__CLASS__, __FUNCTION__);
 //        // If not already done, bootstrap the view
 //        $this->bootstrap('view');
 //        $view = $this->getResource('view');

 //        // Initialise the session
 //        $session = new Zend_Session_Namespace('user_session');

 //        // See if the username has been set, and if not at 
 //        // least make the variable
 //        if(isset($session->loginedInfo)){
 //            $view->loginedInfo = $session->loginedInfo;
 //            $logger->info('session exist');
 //        }else{
 //            $view->loginedInfo = null;
 //            $logger->info('no session');
 //        }
 //    }
}