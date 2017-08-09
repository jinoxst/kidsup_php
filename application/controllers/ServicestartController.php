<?php
require_once APPLICATION_PATH.'/library/Crypto.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/LangPack.php';
require_once APPLICATION_PATH.'/models/ServicestartDAO.php';

class ServicestartController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $req = $this->getRequest();
        $this->_helper->layout()->disableLayout(); 
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = $req->getUserParam('cid');
        $locale = $req->getUserParam('locale');
        $crypto = new Crypto();
        $data['status'] = Message::OK;
        $data['message'] = null;

        $logger->info('center_id:'.$center_id.', locale:'.$locale);
        $paramCheck = true;
        if($center_id != null && $center_id != ''){
            $center_id = $crypto->decrypt($center_id);
        }else{
            $paramCheck = false;
        }
        if($locale == null || $locale == ''){
            $paramCheck = false;
        }
        if($locale != null && $locale != ''){
            if($locale != 'ja' && $locale != 'ko'){
                $locale = 'en';
            }
        }else{
            $locale = 'en';
        }
        $data['locale'] = $locale;
        $logger->info('center_id:'.$center_id.', locale:'.$locale.', paramCheck:'.$paramCheck);
        if($paramCheck){
            try{
                $updateDAO = new ServicestartDAO();
                $stmt = $updateDAO->doServiceStart((int)$center_id);
                if($rs = $stmt->fetch(Zend_DB::FETCH_NAMED)) {
                    $logger->info('result:'.$rs['result']);
                    if($rs['result'] == 1){
                        $data['contents'] = LangPack::getMessage($locale, Message::ServiceStartOk_Context);
                        $logger->info($data['contents']);
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