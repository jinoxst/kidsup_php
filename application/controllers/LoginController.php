<?php
require_once APPLICATION_PATH.'/models/LoginDAO.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/Crypto.php';
require_once APPLICATION_PATH.'/library/Util.php';
require_once 'CommonController.php';
require_once 'Zend/Locale.php';

class LoginController extends CommonController
{
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $id_pw_save = parent::getCookie('ID_PW_SAVE');
        $email = '';
        $pw = '';
        $logger->info('id_pw_save:'.$id_pw_save);
        if(isset($id_pw_save) && $id_pw_save != ''){
            $id_pw_save = Util::encrypt_decrypt('decrypt', $id_pw_save);
            if($id_pw_save == 1){
                $email = Util::encrypt_decrypt('decrypt', parent::getCookie('EMAIL'));
                $pw = Util::encrypt_decrypt('decrypt', parent::getCookie('PW'));
            }else{
                $id_pw_save = '';
            }
        }else{
            $id_pw_save = '';
        }
        $logger->info('email:['.$email.'], pw:['.$pw.'], id_pw_save:['.$id_pw_save.']');

        $this->view->email = $email;
        $this->view->pw = $pw;
        $this->view->id_pw_save = $id_pw_save;
    }

    public function htmlsearAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->ViewRenderer->setNoRender();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $logger->info();

        $this->getResponse()->setBody(Constant::SEAR_STR);
    }

    public function jsonsearAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->ViewRenderer->setNoRender();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $logger->info();

        $this->_helper->json(array(
            'status' => Constant::SEAR_STR
        ));
    }

    public function logincheckAction()
    {
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $email = $req->getQuery('email');
        $pw = $req->getQuery('pw');
        $id_pw_save = $req->getQuery('id_pw_save');
        $logger->info('email:'.$email.', pw:'.$pw.', id_pw_save:'.$id_pw_save);

        $updateDAO = new LoginDAO();
        $stmt = $updateDAO->logincheck($email, $pw);
        if($rs = $stmt->fetch(Zend_DB::FETCH_NAMED)) {
            $logger->info('result:'.$rs['login_result']);
            if($rs['login_result'] == Constant::LOGIN_OK){
                $sessionNameSpace = new Zend_Session_Namespace(Constant::SESSION_NAMESPACE_NAME);
                $sessionNameSpace->loginedInfo = array(
                    'login_result' => Constant::LOGIN_OK,
                    'member_id' => $rs['member_id'],
                    'center_id' => $rs['center_id'],
                    'center_name' => $rs['center_name'],
                    'member_name' => $rs['member_name'],
                    'phonenum' => $rs['phonenum'],
                    'img' => $rs['member_img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$rs['member_img'],
                    'member_type' => $rs['member_type'],
                    'member_subtype' => $rs['member_subtype'],
                    'kids_id' => $rs['kids_id'],
                    'kids_name' => $rs['kids_name'],
                    'approval_state' => $rs['approval_state'],
                    'class_id' => $rs['class_id'],
                    'class_name' => $rs['class_name'],
                    'admin_yn' => $rs['admin_yn']
                );
                $idx = 0;
                if($rs['member_type'] == '2'){
                    $stmt = $updateDAO->getMemberClassList($rs['member_id']);
                    $className = '';
                    $classCnt = 0;
                    while($rs = $stmt->fetch(Zend_DB::FETCH_NAMED)) {
                        if($idx++ == 0){
                            $sessionNameSpace->loginedInfo['class_id'] = $rs['id'];
                            $className = $rs['name'];
                        }
                        $classCnt++;
                    }
                    if($classCnt > 1){
                        $className .= '+'.($classCnt - 1);
                    }
                    $sessionNameSpace->loginedInfo['class_name'] = $className;
                }
                $sessionNameSpace->setExpirationSeconds(Constant::SESSION_TIMEOUT);

                if($id_pw_save == '1'){
                    parent::setCookie('EMAIL', Util::encrypt_decrypt('encrypt', $email));
                    parent::setCookie('PW', Util::encrypt_decrypt('encrypt', $pw));
                    parent::setCookie('ID_PW_SAVE', Util::encrypt_decrypt('encrypt', $id_pw_save));
                }else{
                    parent::deleteCookie('EMAIL');
                    parent::deleteCookie('PW');
                    parent::deleteCookie('ID_PW_SAVE');
                }

                $this->_helper->json(array(
                    'login_result' => Constant::LOGIN_OK
                ));
            }else{
                $this->_helper->json(array(
                    'login_result' => Constant::LOGIN_NG
                ));
            }
        }else{
            $this->_helper->json(array(
                'login_result' => Constant::LOGIN_NG
            ));
        }
    }

    public function logoutAction()
    {
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $logger->info();
        $sessionNameSpace = new Zend_Session_Namespace(Constant::SESSION_NAMESPACE_NAME);
        unset($sessionNameSpace->loginedInfo);

        $this->_forward('index');
    }

    public function issuepwformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $email = $req->getQuery('email');
        $this->view->email = $email;
    }

    public function issuepwAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $locale = new Zend_Locale();
        $crypto = new Crypto();

        $req = $this->getRequest();
        $email = $req->getPost('email');
        $sender = $req->getPost('sender');
        $locale = $locale->getLanguage();
        if($locale != 'ja' && $locale != 'en' && $locale != 'ko'){
            $locale = 'en';
        }
        $logger->info('email:'.$email.', sender:'.$sender.', locale:'.$locale);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new LoginDAO();
            $rs = $updateDAO->reqIssuePw(array($email, $sender, $locale));
            $logger->info('result => '.var_export($rs, true));
            $result['result'] = $rs['result'];

            $langStr = file_get_contents(Constant::ISSUEPW_I18N_DIR.''.$locale.'.json');
            $lang = json_decode($langStr);
            $lang->{'PW_REISSUE_MAIL_CONTENTS'} = str_replace('%_NAME_%', $rs['name'], $lang->{'PW_REISSUE_MAIL_CONTENTS'});
            $lang->{'PW_REISSUE_MAIL_CONTENTS'} = str_replace('%_REQNAME_%', $sender, $lang->{'PW_REISSUE_MAIL_CONTENTS'});
            $lang->{'PW_REISSUE_MAIL_CONTENTS'} = str_replace('%_EXPIREDATE_%', $rs['expire_date'], $lang->{'PW_REISSUE_MAIL_CONTENTS'});

            $html = file_get_contents(Constant::ISSUEPW_FORMAT_FILE);
            $html = str_replace('%_MAILCONTENTS_%', $lang->{'PW_REISSUE_MAIL_CONTENTS'}, $html);
            $html = str_replace('%_WEB_ROOT_%', Constant::ISSUEPW_WEB_ROOT, $html);
            $html = str_replace('%_MID_%', $crypto->encrypt($rs['id']), $html);
            $html = str_replace('%_SEQ_%', $crypto->encrypt($rs['seq']), $html);
            $html = str_replace('%_PAGE_TITLE_%', $lang->{'PAGE_TITLE'}, $html);
            $html = str_replace('%_LOCALE_%', $locale, $html);
            $html = str_replace('%_LINK_%', $lang->{'PW_REISSUE_LINK'}, $html);

            $logger->info($html);
            if($locale == 'ja'){
                $mail = new Zend_Mail('ISO-2022-JP');
                $mail->setBodyHtml(mb_convert_encoding($html, 'ISO-2022-JP', 'UTF-8'), null, Zend_Mime::ENCODING_7BIT);
                $mail->setSubject(mb_convert_encoding($lang->{'MAIL_TITLE'},'ISO-2022-JP', 'UTF-8'));
            }else if($locale == 'ko'){
                $mail = new Zend_Mail('EUC-KR');
                $mail->setBodyHtml($html, null, Zend_Mime::ENCODING_7BIT);
                $mail->setSubject(mb_convert_encoding($lang->{'MAIL_TITLE'},'EUC-KR', 'UTF-8'));
            }else{
                $mail = new Zend_Mail();
                $mail->setBodyHtml($html, null, Zend_Mime::ENCODING_7BIT);
                $mail->setSubject($lang->{'MAIL_TITLE'});
            }
            $mail->setFrom(Constant::ISSUEPW_FROM);
            $mail->addTo($email);
            $mail->send();
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