<?php
require_once APPLICATION_PATH.'/library/Crypto.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/LangPack.php';
require_once APPLICATION_PATH.'/models/IssuepwDAO.php';

class IssuepwController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $req = $this->getRequest();
        $this->_helper->layout()->disableLayout(); 
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = $req->getUserParam('mid');
        $seq = $req->getUserParam('seq');
        $locale = $req->getUserParam('locale');
        $crypto = new Crypto();
        $data['status'] = Message::OK;
        $data['message'] = null;

        $paramCheck = true;
        if($member_id != null && $member_id != ''){
            $member_id = $crypto->decrypt($member_id);
        }else{
            $paramCheck = false;
        }
        if($seq != null && $seq != ''){
            $seq = $crypto->decrypt($seq);
        }else{
            $paramCheck = false;
        }
        if($locale == null || $locale == ''){
            $paramCheck = false;
        }
        $logger->info('member_id:'.$member_id.', seq:'.$seq.', locale:'.$locale);
        if($locale != null && $locale != ''){
            if($locale != 'ja' && $locale != 'ko'){
                $locale = 'en';
            }
        }else{
            $locale = 'en';
        }
        $data['locale'] = $locale;
        $logger->info('member_id:'.$member_id.', seq:'.$seq.', locale:'.$locale);
        if($paramCheck){
            try{
                $updateDAO = new IssuepwDAO();
                $stmt = $updateDAO->issuePw((int)$member_id, (int)$seq);
                if($rs = $stmt->fetch(Zend_DB::FETCH_NAMED)) {
                    $logger->info('result:'.$rs['result'].', new_pw:'.$rs['new_pw']);
                    if($rs['result'] == '1'){
                        $data['contents'] = str_replace('%_NEW_PW_%',$rs['new_pw'],LangPack::getMessage($locale, Message::IssePwOK_Context));
                    }else{
                        $data['status'] = $rs['result'];
                    }
                }else{
                    $data['status'] = Message::DBError;
                }
            }catch(Exception $e){
                $logger->error($e->getMessage());
                $data['status'] = Message::DBError;
            }
        }else{
            $data['status'] = Message::ParameterInvalid;
        }
        if($data['status'] != Message::OK){
            $data['message'] = LangPack::getMessage($locale, $data['status']);
        }
        $logger->info('status:'.$data['status'].', message:'.$data['message']);
        $this->view->data = $data;
    }
}