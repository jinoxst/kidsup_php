<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/models/ContactDAO.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';
require_once 'Zend/Locale.php';

class ContactController extends BaseController
{
    public function indexAction()
    {
        $logger = new Logger(__CLASS__, __FUNCTION__);        
        $this->view->pageno = 1;
        $locale = new Zend_Locale();
        $logger->info('locale:'.$locale->toString().', language:'.$locale->getLanguage().', region:'.$locale->getRegion());
    }

    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $year = $req->getQuery('year');
        $month = $req->getQuery('month');
        $trigger = $req->getQuery('trigger');
        if(isset($year) === false){
            $year = date('Y');
            $month = date('m');
        }
        $date = $year . '' . $month;
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $kids_id = parent::getSessionValue('kids_id');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> center_id:'.$center_id.', date:'.$date.', member_id:'.$member_id.', kids_id:'.$kids_id.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new ContactDAO();
        $totalCnt = $updateDAO->web_getContactListTotalCount($center_id, $date, $member_id);
        $logger->info('totalCnt:'.$totalCnt);
        $groupno = Util::getGroupno((int)$pageno);
        if($trigger == 'nextgroup'){
            $groupno++;
            $pageno = Util::getPageno($groupno, $totalCnt);
        }else if($trigger == 'prevgroup'){
            $groupno--;
            $pageno = Util::getPageno($groupno, $totalCnt);
        }else if($trigger == 'lastpageno'){
            $pageno = Util::getLastPageno($totalCnt);
            $groupno = Util::getLastGroupno($pageno);
        }
        $pagenator = Util::getPagenator($groupno, $totalCnt);
        /*foreach($pagenator['pagenoArr'] as $pno){
            $logger->info($pno);
        }
        $logger->info('firstPageFlag:'.$pagenator['firstPageFlag']);
        $logger->info('preGroupFlag:'.$pagenator['preGroupFlag']);
        $logger->info('lastPageFlag:'.$pagenator['lastPageFlag']);
        $logger->info('nextGroupFlag:'.$pagenator['nextGroupFlag']);*/

        $logger->info('2-> center_id:'.$center_id.', date:'.$date.', member_id:'.$member_id.', pageno:'.$pageno);

        $rs = $updateDAO->getContactList($center_id, $date, $member_id, $kids_id, $pageno, Constant::PAGESIZE);
        $list = array();
        foreach($rs as $v){
            $contents = $v['contents'];
            $strLen = strlen($contents);
            if($strLen > 75){
                $contents = mb_substr($contents, 0, 75) . '...';
            }
            $order = array("\r\n", "\n", "\r");
            $contents = str_replace($order, '<br>', $contents);
            
            $tmp = array(
                'contact_id' => $v['contact_id'],
                'contact_type' => $v['contact_type'],
                'center_id' => $v['center_id'],
                'contents' => $contents,
                'createtime' => $v['createtime'],
                'writer_name' => $v['writer_name'],
                'reply_cnt' => $v['reply_cnt'],
                'status' => $v['status'],
                'attention' => '',
                'contact_confirm' => '',
                'target_read_info' => '',
                'all_read_flag' => ''
            );
            if($v['img1'] != '' || $v['img2'] != '' || $v['img3'] != '' || $v['img4'] != '' || $v['img5'] != '' || $v['img6'] != '' || $v['img7'] != '' || $v['img8'] != '' || $v['img9'] != '' || $v['img10'] != ''){
                $tmp['have_pictures_url'] = '/img/icon/picture.png';
            }
            if(($v['contact_type'] == '1' && $member_type != '3') || ($v['contact_type'] == '2' && $member_type == '3')){
                $tmp['attention'] = "<img src='/img/icon/attention.png' style='vertical-align: middle'/>";
            }
            if($member_type == '3'){
                if($v['contact_type'] == '2' && $v['readyn'] == '0'){
                    $tmp['contact_confirm'] = '1';
                }
                if($v['contact_type'] == '1'){
                    if($v['target_readcnt'] == $v['target_totalcnt']){
                        $tmp['all_read_flag'] = '1';
                    }
                }
            }else{
                if($v['contact_type'] == '1' && $v['readyn'] == '0'){
                    $tmp['contact_confirm'] = '1';
                }
                if($v['contact_type'] == '2'){
                    $tmp['target_read_info'] = $v['target_readcnt'].'/'.$v['target_totalcnt'];
                    if($v['target_readcnt'] == $v['target_totalcnt']){
                        $tmp['all_read_flag'] = '1';
                    }
                }
            }

            array_push($list,$tmp);
        }

        $this->view->list = $list;
        $this->view->curY = $year;
        $this->view->curM = $month;
        $this->view->minY = Constant::MIN_YEAR;
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function addformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $center_id = parent::getSessionValue('center_id');
        $class_id = parent::getSessionValue('class_id');
        $member_type = parent::getSessionValue('member_type');
        $member_id = parent::getSessionValue('member_id');

        $kidslist = array();
        $classlist = array();
        if($member_type == '1' || $member_type == '2'){
            $updateDAO = new ContactDAO();
            $rs = $updateDAO->getMemberClassList($member_id);
            foreach($rs as $v){
                $tmp = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'desc' => $v['desc'],
                    'approved_cnt' => $v['approved_cnt']
                );
                array_push($classlist,$tmp);
            }
        }

        $this->view->kidslist = $kidslist;
        $this->view->classlist = $classlist;
        $this->view->year = $req->getQuery('year');
        $this->view->month = $req->getQuery('month');
    }

    public function kidslistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $center_id = parent::getSessionValue('center_id');
        $class_id = $req->getQuery('class_id');
        $logger->info('center_id:'.$center_id.', class_id:'.$class_id);

        $kidslist = array();
        $updateDAO = new ContactDAO();
        $rs = $updateDAO->getKidsList($center_id, $class_id);
        foreach($rs as $v){
            $tmp = array(
                'id' => $v['id'],
                'name' => $v['name'],
                'birthday_str' => Util::chgDateFormat($v['birthday']),
                'class_id' => $v['class_id'],
                'sex_img' => $v['sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'member_name' => $v['member_name'],
                'phonenum' => $v['phonenum']
            );
            array_push($kidslist,$tmp);
        }

        $this->view->kidslist = $kidslist;
    }

    public function notreadmemberlistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $contact_id = $req->getQuery('contact_id');
        $logger->info('contact_id:'.$contact_id);

        $notreadmemberlist = array();
        $updateDAO = new ContactDAO();
        $rs = $updateDAO->getNotReadMemberList($contact_id);
        $not_read_cnt = 0;
        foreach($rs as $v){
            $tmp = array(
                'kids_name' => $v['kids_name'],
                'kids_sex_img' => $v['kids_sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'birthday_str' => Util::chgDateFormat($v['birthday']),
                'kids_img' => $v['kids_img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['kids_img'],
                'member_name' => $v['member_name'],
                'phonenum' => $v['phonenum'],
                'class_name' => $v['class_name'],
                'readyn' => $v['readyn']
            );
            array_push($notreadmemberlist,$tmp);
            if($v['readyn'] == '0'){
                $not_read_cnt++;
            }
        }

        $this->view->notreadmemberlist = $notreadmemberlist;
        $this->view->not_read_cnt = $not_read_cnt;
        $this->view->contact_id = $contact_id;
    }

    public function replylistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $contact_id = $req->getQuery('contact_id');
        $member_id = parent::getSessionValue('member_id');
        $reply_pageno = $req->getQuery('reply_pageno');
        $logger->info('contact_id:'.$contact_id.', member_id:'.$member_id.', reply_pageno:'.$reply_pageno);

        $replyList = array();
        $updateDAO = new ContactDAO();
        $rs = $updateDAO->getContactReplyList($contact_id, $member_id, $reply_pageno, Constant::PAGESIZE);
        foreach($rs as $v){
            $member_type_desc = '';
            if($v['type'] == '1'){
                $member_type_desc = '管理者';
            }else if($v['type'] == '2'){
                $member_type_desc = '先生';
            }else{
                $member_type_desc = 'キッズ会員（保護者）';
            }
            $tmp = array(
                'contact_id' => $v['contact_id'],
                'reply_id' => $v['reply_id'],
                'member_id' => $v['id'],
                'member_type' => $v['type'],
                'member_type_desc' => $member_type_desc,
                'writer_name' => $v['name'],
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'contents' => str_replace(array("\r\n", "\n", "\r"), '<br>', $v['contents']),
                'createtime' => $v['createtime']
            );
            array_push($replyList,$tmp);
        }
        $reply_cnt = $updateDAO->getContactReplyCnt($contact_id);
        $pagenator = Util::getReplyPagenator($reply_pageno, $reply_cnt);

        $this->view->replyList = $replyList;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function detailAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $req = $this->getRequest();
        $contact_id = $req->getQuery('contact_id');
        $contact_confirm = $req->getQuery('contact_confirm');
        $reply_pageno = $req->getQuery('reply_pageno');
        $reply_cnt = $req->getQuery('reply_cnt');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $kids_id = parent::getSessionValue('kids_id');
        $target_read_info = $req->getQuery('target_read_info');
        $logger->info('contact_id:'.$contact_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', confirm:'.$contact_confirm.', target_read_info:'.$target_read_info);
        $updateDAO = new ContactDAO();
        $rs = $updateDAO->getContactDetail($contact_id, $member_id, $kids_id);
        $rs['writer_img'] = Constant::IMG_HOST_URL.''.$rs['writer_img'];
        if($rs['writer_type'] == '1'){
            $rs['writer_type_desc'] = '管理者';
        }else if($rs['writer_type'] == '2'){
            $rs['writer_type_desc'] = '先生';
        }else{
            $rs['writer_type_desc'] = 'キッズ会員（保護者）';
        }
        $contents = $rs['contents'];
        $contents = str_replace(array("\r\n", "\n", "\r"), '<br>', $contents);
        $rs['contents'] = $contents;

        for($i=1;$i<=10;$i++){
            if($rs['img'.$i] != ''){
                $rs['img'.$i.'_filename'] = basename($rs['img'.$i]);
                $ext = pathinfo(Constant::IMAGE_MAIN_DIR.'c'.$center_id.'/'.Constant::CONTACT_DIR.'/'.Constant::IMAGE_ORG_DIR.''.$rs['img'.$i], PATHINFO_EXTENSION);
                $rs['img'.$i.'_only_filename'] = basename($rs['img'.$i], '.'.$ext);
            }
        }

        $deletableContact = '';
        if($member_type == '1'){
            $deletableContact = '1';
        }else{
            if($member_id == $rs['writer_id']){
                $deletableContact = '1';
            }
        }

        $replyList = array();
        $rs2 = $updateDAO->getContactReplyList($contact_id, $member_id, $reply_pageno, Constant::PAGESIZE);
        foreach($rs2 as $v){
            $member_type_desc = '';
            if($v['type'] == '1'){
                $member_type_desc = '管理者';
            }else if($v['type'] == '2'){
                $member_type_desc = '先生';
            }else{
                $member_type_desc = 'キッズ会員（保護者）';
            }
            $tmp = array(
                'contact_id' => $v['contact_id'],
                'reply_id' => $v['reply_id'],
                'member_id' => $v['id'],
                'member_type' => $v['type'],
                'member_type_desc' => $member_type_desc,
                'writer_name' => $v['name'],
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'contents' => str_replace(array("\r\n", "\n", "\r"), '<br>', $v['contents']),
                'createtime' => $v['createtime']
            );
            array_push($replyList,$tmp);
        }

        $pagenator = Util::getReplyPagenator($reply_pageno, $reply_cnt);

        $this->view->year = $req->getQuery('year');
        $this->view->month = $req->getQuery('month');
        $this->view->detail = $rs;
        $this->view->replyList = $replyList;
        $this->view->deletableContact = $deletableContact;
        $this->view->contact_confirm = $contact_confirm;
        $this->view->target_read_info = $target_read_info;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function deleteAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $contact_id = $req->getQuery('contact_id');
        $logger->info('contact_id:'.$contact_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ContactDAO();
            $rs = $updateDAO->deleteContact($contact_id);
            $logger->info('contact_id:'.$contact_id.' result => '.$rs);
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

    public function postcontactreplyAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $contact_id = $req->getPost('contact_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $contents = $req->getPost('contents');
        $logger->info('contact_id:'.$contact_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', contents:'.$contents);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ContactDAO();
            $re = $updateDAO->postContactReply($contact_id, $member_id, $kids_id, $contents);
            $logger->info('result:'.$re);

            if($re == 1){
                //push
                $rs = $updateDAO->getContactReplyAddPushInfo($contact_id, $member_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'2','2',$contact_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'2','2',$contact_id);
                    }
                }
            }else{
                $logger->info('push is not sent(reply to self)');
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

    public function sendcontactreadAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $contact_id = $req->getQuery('contact_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $logger->info('contact_id:'.$contact_id.', member_id:'.$member_id.', kids_id:'.$kids_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ContactDAO();
            $rs = $updateDAO->sendContactRead($contact_id, $member_id, $kids_id);
            $logger->info('result:'.$rs);

            if($rs == 1){
                //push
                $rs = $updateDAO->getContactReadOverPushInfo($contact_id, $member_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'2','',$contact_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'2','',$contact_id);
                    }
                }
            }else{
                $logger->info('push is not sent(reply to self)');
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

    public function pushnotreadcontactlistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $contact_id = $req->getQuery('contact_id');
        $logger->info('contact_id:'.$contact_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ContactDAO();
            $rs = $updateDAO->getNotReadContactMemberList($contact_id);
            foreach($rs as $v){
                if($v['device_type'] == '2'){//android
                    $push = new PushAndroid();
                    $re = $push->send($v['device_token'],$v['alert'],'2','',$contact_id);
                    $logger->info($re);
                    $reObj = json_decode($re);
                    if($reObj->{'canonical_ids'} === 1 ){
                        $rs = $updateDAO->expireDeviceToken($v['device_token']);
                        $logger->info('canonical expireDeviceToken result:'.$rs);
                    }
                }else{
                    $push = new PushIOS();
                    $push->send($v['device_token'],$v['alert'],'2','',$contact_id);
                }
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

    public function postcontentsAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $to_kids_id = $req->getPost('to_kids_id');
        $contents = $req->getPost('contents');
        $image_file_str = $req->getPost('image_file_str');

        $logger->info('center_id:'.$center_id.', member_id:'.$member_id.', contents:'.$contents.', to_kids_id:'.$to_kids_id.', image_file_str:'.$image_file_str);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            $updateDAO = new ContactDAO();
            $contactId = $updateDAO->postContactContents($center_id, $member_id, $kids_id, $contents, $to_kids_id);
            $logger->info('contact id:'.$contactId);

            //push
            $rs = $updateDAO->getContactTargetPushList($contactId);
            foreach($rs as $v){
                if($v['device_type'] == '2'){//android
                    $push = new PushAndroid();
                    $re = $push->send($v['device_token'],$v['alert'],'2','1',$contactId);
                    $logger->info($re);
                    $reObj = json_decode($re);
                    // $logger->info(var_export($reObj, true));
                    if($reObj->{'canonical_ids'} === 1 ){
                        $rs = $updateDAO->expireDeviceToken($v['device_token']);
                        $logger->info('canonical expireDeviceToken result:'.$rs);
                    }
                }else{
                    $push = new PushIOS();
                    $push->send($v['device_token'],$v['alert'],'2','1',$contactId);
                }
            }

            //image file process
            if(isset($image_file_str) && strlen($image_file_str) > 0){
                $files = explode(';', $image_file_str);
                $workDir = Constant::IMAGE_WORK_DIR . 'c' . $center_id . '/'. Constant::CONTACT_DIR . '/' . $member_id;
                $targetDir = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::CONTACT_DIR;
                Util::mkDir($targetDir.'/'.Constant::IMAGE_ORG_DIR);
                Util::mkDir($targetDir.'/'.Constant::IMAGE_CHG_DIR);
                Util::mkDir($targetDir.'/'.Constant::IMAGE_THM_DIR);
                foreach($files as $file){
                    if(isset($file) && strlen($file) > 0){
                        $fileFullPath =  $workDir .'/'. $file;
                        if(file_exists($fileFullPath)){
                            $logger->info('file => '.$file);
                            $ext = pathinfo($fileFullPath, PATHINFO_EXTENSION);
                            $file = basename($fileFullPath, '.'.$ext);
                            $logger->info($file.' <-> '.$ext);
                            $newFileName = sha1($center_id.''.$file.time()).'.'.$ext;
                            // org file copy
                            if(copy($fileFullPath, $targetDir.'/'.Constant::IMAGE_ORG_DIR.'/'.$newFileName)){
                                $logger->info($targetDir.'/'.Constant::IMAGE_ORG_DIR.'/'.$newFileName.' OK');
                            }else{
                                $logger->info($targetDir.'/'.Constant::IMAGE_ORG_DIR.'/'.$newFileName.' NG');
                            }

                            $imageInfo = getimagesize($fileFullPath);
                            if($imageInfo[2] == IMAGETYPE_JPEG || $imageInfo[2] == IMAGETYPE_PNG){
                                // chg file create
                                $image = new SimpleImage($fileFullPath);
                                $image->resizeToWidth(Util::getChgImageWidth($image->width, $image->height));
                                $image->save($targetDir.'/'.Constant::IMAGE_CHG_DIR.'/'.$newFileName, 75);
                                $imageInfo = getimagesize($targetDir.'/'.Constant::IMAGE_CHG_DIR.'/'.$newFileName);

                                // thm file create
                                $image = new SimpleImage($fileFullPath);
                                $image->resizeToWidth(Util::getThmImageWidth($image->width, $image->height));
                                $image->save($targetDir.'/'.Constant::IMAGE_THM_DIR.'/thm_'.$newFileName, 75);

                                //post image info to DB
                                $rs = $updateDAO->postContactImage($contactId, $newFileName, $imageInfo[0], $imageInfo[1]);
                                $logger->info('image info db insert result:'.$rs);
                            }
                        }
                    }
                }
                //delete work dir
                Util::rmDir($workDir);
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
        // $logger->info('result:'.var_export($result, true));
        $this->_helper->json($result);
    }
}