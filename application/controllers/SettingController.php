<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/SettingDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class SettingController extends BaseController
{
    public function updatememberinfoformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('center_id:'.$center_id.', member_id:'.$member_id);
    }

    public function updatememberinfoAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = parent::getSessionValue('member_id');
        $member_name = $req->getPost('member_name');
        $phonenum = $req->getPost('phonenum');
        $logger->info('member_id:'.$member_id.', member_name:'.$member_name.', phonenum:'.$phonenum);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new SettingDAO();
            $rs = $updateDAO->updateMemberType3Info(array($member_id, $member_name, $phonenum));
            $logger->info('result => '.$rs);
            if($rs == '1'){
                $sessionNameSpace = new Zend_Session_Namespace(Constant::SESSION_NAMESPACE_NAME);
                $sessionNameSpace->loginedInfo['member_name'] = $member_name;
                $sessionNameSpace->loginedInfo['phonenum'] = $phonenum;
                $sessionNameSpace->setExpirationSeconds(Constant::SESSION_TIMEOUT);
            }
       }catch(Zend_Db_Statement_Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }catch(Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }
        if($result['status'] != Message::OK){
            $result['message'] = Message::getMessage($result);
        }
        $logger->info('result:'.var_export($result, true));
        $this->_helper->json($result);
    }

    public function chgpwformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = parent::getSessionValue('member_id');
        $logger->info('member_id:'.$member_id);
    }

    public function chgpwAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = parent::getSessionValue('member_id');
        $current_pw = $req->getQuery('current_pw');
        $new_pw = $req->getQuery('new_pw');
        $logger->info('member_id:'.$member_id.', current_pw:'.$current_pw.', new_pw:'.$new_pw);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new SettingDAO();
            $rs = $updateDAO->chgPw(array($member_id, $current_pw, $new_pw));
            $logger->info('result => '.$rs);
       }catch(Zend_Db_Statement_Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }catch(Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }
        if($result['status'] != Message::OK){
            $result['message'] = Message::getMessage($result);
        }
        $logger->info('result:'.var_export($result, true));
        $this->_helper->json($result);
    }

    public function pushreceiveynformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = parent::getSessionValue('member_id');
        $logger->info('member_id:'.$member_id);

        $data = null;
        try{
            $updateDAO = new SettingDAO();
            $rs = $updateDAO->getPushReceiveYnList(array($member_id));
            $data = array(
                'contact' => $rs['contact'] == '1' ? 'checked' : '',
                'notice' => $rs['notice'] == '1' ? 'checked' : '',
                'event' => $rs['event'] == '1' ? 'checked' : '',
                'dailymenu' => $rs['dailymenu'] == '1' ? 'checked' : '',
                'reply' => $rs['reply'] == '1' ? 'checked' : '',
                'attendance' => $rs['attendance'] == '1' ? 'checked' : '',
                'confirm' => $rs['confirm'] == '1' ? 'checked' : ''
            );
       }catch(Zend_Db_Statement_Exception $e){
            $logger->error($e->getMessage());
        }catch(Exception $e){
            $logger->error($e->getMessage());
        }

        $this->view->push = $data;
    }

    public function setpushreceiveynAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = parent::getSessionValue('member_id');
        $push_key = $req->getQuery('push_key');
        $push_value = $req->getQuery('push_value');
        $logger->info('member_id:'.$member_id.', push_key:'.$push_key.', push_value:'.$push_value);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new SettingDAO();
            $rs = $updateDAO->setPushReceiveYn(array($member_id, $push_key, $push_value));
            $logger->info('result => '.$rs);
       }catch(Zend_Db_Statement_Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }catch(Exception $e){
            $logger->error($e->getMessage());
            $result['status'] = Message::SystemError;
        }
        if($result['status'] != Message::OK){
            $result['message'] = Message::getMessage($result);
        }
        $logger->info('result:'.var_export($result, true));
        $this->_helper->json($result);
    }
}