<?php
require_once 'CommonController.php';

class BaseController extends CommonController {
    public function init(){
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $httpHeaders = getallheaders();
        $sessionNameSpace = new Zend_Session_Namespace(Constant::SESSION_NAMESPACE_NAME);
        if(isset($sessionNameSpace->loginedInfo) && isset($sessionNameSpace->loginedInfo['login_result']) && $sessionNameSpace->loginedInfo['login_result'] == 1){
            // $timeLeftTillSessionExpires = $_SESSION['__ZF'][Constant::SESSION_NAMESPACE_NAME]['ENT'] - time();
            // $logger->info('session timeout:'.$timeLeftTillSessionExpires);
            $sessionNameSpace->setExpirationSeconds(Constant::SESSION_TIMEOUT);
            $this->view->loginedInfo = $sessionNameSpace->loginedInfo;

            // $logger->info('session timeout:'.$timeLeftTillSessionExpires);
            //$logger->info('login OK');
        }else{
            $logger->info('login fail, redirect login');
            if($httpHeaders['X-Requested-With'] == 'XMLHttpRequest'){
                if(Util::startsWith($httpHeaders['Accept'], 'text/html')){
                    $this->_redirect('login/htmlsear');//login/session expire ajax request, html response
                }else if(Util::startsWith($httpHeaders['Accept'], 'application/json')){
                    $this->_redirect('login/jsonsear');//login/session expire ajax request, json response
                }else{
                    $this->_redirect('login/htmlsear');//login/session expire ajax request, html response
                }
            }else{
                $this->_redirect('login');
            }
        }
    }

    public function uploadimageAction()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $action = $req->getPost('action');
        $logger->info('action:'.$action);
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $targetDir = Constant::IMAGE_WORK_DIR . 'c' . $center_id . '/'. $action . '/' . $member_id;
        Util::mkDir($targetDir);
        try{
            if (!empty($_FILES)) {
                $tempFile = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $logger->info('filename:'.$fileName);
                $fileFullPath =  $targetDir .'/'. $fileName;
                $logger->info('fileFullPath:'.$fileFullPath);
                $dl_result = move_uploaded_file($tempFile, $fileFullPath);
                $logger->info('upload_result:'.var_export($dl_result, ture));
                if($dl_result){
                    $ext = pathinfo($fileFullPath, PATHINFO_EXTENSION);
                    $file = basename($fileFullPath, '.'.$ext);
                    $logger->info($file.' <-> '.$ext);
                    $imageInfo = getimagesize($fileFullPath);
                    if($imageInfo[2] == IMAGETYPE_JPEG || $imageInfo[2] == IMAGETYPE_PNG){
                        $image = new SimpleImage($fileFullPath);
                        $image->resizeToWidth(Util::getOrgImageWidth($image->width, $image->height));
                        $image->save($fileFullPath, 75);
                    }
                }
            }
        }catch(Exception $e){
            $logger->error($e->getMessage());
        }
        $this->_helper->json(array());
    }
}