<?php
class CommonController extends Zend_Controller_Action {
    protected function getCookie($key){
        return $this->getRequest()->getCookie($key);
    }

    protected function setCookie($key, $value){
        setCookie($key, $value, time() + Constant::COOKIE_TIMEOUT, Constant::COOKIE_PATH);
    }

    protected function deleteCookie($key){
        setCookie($key, '', time() - 3600, Constant::COOKIE_PATH);
    }

    protected function getMenuCookie($menu){
        return $this->getRequest()->getCookie($menu);
    }

    protected function setMenuCookie($key, $value){
        setCookie($key, $value, time() + Constant::COOKIE_TIMEOUT_ONEYEAR, Constant::COOKIE_PATH);
    }

    protected function getSessionValue($key){
        $sessionNameSpace = new Zend_Session_Namespace(Constant::SESSION_NAMESPACE_NAME);
        if(isset($sessionNameSpace->loginedInfo) && isset($sessionNameSpace->loginedInfo['login_result']) && $sessionNameSpace->loginedInfo['login_result'] == 1){
            return isset($sessionNameSpace->loginedInfo[$key]) ? $sessionNameSpace->loginedInfo[$key] : null;
        }else{
            return null;
        }
    }
}