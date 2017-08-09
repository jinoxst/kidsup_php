<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/EventDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class EventController extends BaseController
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $class_id = parent::getSessionValue('class_id');
        if($member_type != '3'){
            $class_id = -1;
        }
        $trigger = $req->getQuery('trigger');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> center_id:'.$center_id.', member_type:'.$member_type.', member_id:'.$member_id.', class_id:'.$class_id.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new EventDAO();
        $totalCnt = $updateDAO->web_getEventListTotalCount($center_id, $class_id, $member_id);
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
        $logger->info('2-> center_id:'.$center_id.', member_type:'.$member_type.', member_id:'.$member_id.', class_id:'.$class_id.', pageno:'.$pageno.', history_seq:'.$history_seq);

        $rs = $updateDAO->getEventList($center_id, $class_id, $member_id, $pageno, Constant::PAGESIZE);
        $list = array();
        foreach($rs as $v){
            $contents = $v['contents'];
            $strLen = strlen($contents);
            if($strLen > 75){
                $contents = mb_substr($contents, 0, 75) . '...';
            }
            $order = array("\r\n", "\n", "\r");
            $contents = str_replace($order, '<br>', $contents);

            $title = $v['title'];
            $strLen = strlen($title);
            if($strLen > 25){
                $title = mb_substr($title, 0, 25) . '...';
            }
            $tmp = array(
                'event_id' => $v['event_id'],
                'event_type' => $v['event_type'],
                'center_id' => $v['center_id'],
                'title' => $title,
                'contents' => $contents,
                'createtime' => $v['createtime'],
                'writer_name' => $v['writer_name'],
                'good_cnt' => $v['goodcnt'],
                'reply_cnt' => $v['reply_cnt'],
                'status' => $v['status'],
                'class_name' => $v['class_name'],
                'event_confirm' => '',
                'target_read_info' => '',
                'all_read_flag' => ''
            );
            if($v['img1'] != '' || $v['img2'] != '' || $v['img3'] != '' || $v['img4'] != '' || $v['img5'] != '' || $v['img6'] != '' || $v['img7'] != '' || $v['img8'] != '' || $v['img9'] != '' || $v['img10'] != ''){
                $tmp['have_pictures_url'] = '/img/icon/picture.png';
            }
            if($member_type == '3'){
                if($v['readyn'] == '0'){
                    $tmp['event_confirm'] = '1';
                }
            }else{
                $tmp['target_read_info'] = $v['target_readcnt'].'/'.$v['target_totalcnt'];
            }

            array_push($list,$tmp);
        }

        $this->view->list = $list;
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function detailAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $event_confirm = $req->getQuery('event_confirm');
        $reply_pageno = $req->getQuery('reply_pageno');
        $reply_cnt = $req->getQuery('reply_cnt');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $target_read_info = $req->getQuery('target_read_info');
        $logger->info('event_id:'.$event_id.', member_id:'.$member_id.', confirm:'.$event_confirm.', target_read_info:'.$target_read_info);
        $updateDAO = new EventDAO();
        $rs = $updateDAO->getEventDetail($event_id, $member_id);
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

        $deletableEvent = '';
        if($member_type == '1'){
            $deletableEvent = '1';
        }else{
            if($member_id == $rs['writer_id']){
                $deletableEvent = '1';
            }
        }

        $target_read_info_plus = '';
        if($target_read_info != ''){
            if($rs['class_id'] == ''){
                $target_read_info_plus = '全体の組('.$target_read_info.')';
            }else{
                $target_read_info_plus = $rs['class_name'].'('.$target_read_info.')';
            }
        }

        $replyList = array();
        $rs2 = $updateDAO->getEventReplyList($event_id, $reply_pageno, Constant::PAGESIZE);
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
                'event_id' => $v['event_id'],
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

        $this->view->detail = $rs;
        $this->view->replyList = $replyList;
        $this->view->reply_cnt = $reply_cnt;
        $this->view->deletableEvent = $deletableEvent;
        $this->view->event_confirm = $event_confirm;
        $this->view->target_read_info = $target_read_info;
        $this->view->target_read_info_plus = $target_read_info_plus;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function updateformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $event_confirm = $req->getQuery('event_confirm');
        $reply_pageno = $req->getQuery('reply_pageno');
        $reply_cnt = $req->getQuery('reply_cnt');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $target_read_info = $req->getQuery('target_read_info');
        $logger->info('event_id:'.$event_id.', member_id:'.$member_id.', confirm:'.$event_confirm.', target_read_info:'.$target_read_info);
        $updateDAO = new EventDAO();
        $rs = $updateDAO->getEventDetail($event_id, $member_id);
        $rs['writer_img'] = Constant::IMG_HOST_URL.''.$rs['writer_img'];
        if($rs['writer_type'] == '1'){
            $rs['writer_type_desc'] = '管理者';
        }else if($rs['writer_type'] == '2'){
            $rs['writer_type_desc'] = '先生';
        }else{
            $rs['writer_type_desc'] = 'キッズ会員（保護者）';
        }

        $deletableEvent = '';
        if($member_type == '1'){
            $deletableEvent = '1';
        }else{
            if($member_id == $rs['writer_id']){
                $deletableEvent = '1';
            }
        }

        $target_read_info_plus = '';
        if($target_read_info != ''){
            if($rs['class_id'] == ''){
                $target_read_info_plus = '全体の組('.$target_read_info.')';
            }else{
                $target_read_info_plus = $rs['class_name'].'('.$target_read_info.')';
            }
        }

        $this->view->detail = $rs;
        $this->view->reply_cnt = $reply_cnt;
        $this->view->deletableEvent = $deletableEvent;
        $this->view->event_confirm = $event_confirm;
        $this->view->target_read_info = $target_read_info;
        $this->view->target_read_info_plus = $target_read_info_plus;
        $this->view->reply_pageno = $reply_pageno;
    }

    public function addformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $center_id = parent::getSessionValue('center_id');
        $class_id = parent::getSessionValue('class_id');
        $member_type = parent::getSessionValue('member_type');
        $member_id = parent::getSessionValue('member_id');

        $classlist = array();
        if($member_type == '1' || $member_type == '2'){
            $updateDAO = new EventDAO();
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

        $this->view->classlist = $classlist;
    }

    public function postcontentsAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $class_id = $req->getPost('class_id');
        $title = $req->getPost('title');
        $contents = $req->getPost('contents');
        $address = $req->getPost('address');
        $date = $req->getPost('date');
        $type = $class_id == '00' ? '1' : '2';//1:all class, 2:only 1 class
        $class_id = $class_id == '00' ? '-1' : $class_id;

        $logger->info('center_id:'.$center_id.', member_id:'.$member_id.', class_id:'.$class_id.', type:'.$type.' title:'.$title.', contents:'.$contents.', address:'.$address.', date:'.$date);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            $updateDAO = new EventDAO();
            $eventId = $updateDAO->postEventContents($type, $center_id, $class_id, $member_id, $title, $contents, $address, $date);
            $logger->info('event id:'.$eventId);

            //push
            $rs = $updateDAO->getEventTargetPushList($eventId);
            foreach($rs as $v){
                if($v['device_type'] == '2'){//android
                    $push = new PushAndroid();
                    $re = $push->send($v['device_token'],$v['alert'],'3','1',$eventId);
                    $logger->info($re);
                    $reObj = json_decode($re);
                    // $logger->info(var_export($reObj, true));
                    if($reObj->{'canonical_ids'} === 1 ){
                        $rs = $updateDAO->expireDeviceToken($v['device_token']);
                        $logger->info('canonical expireDeviceToken result:'.$rs);
                    }
                }else{
                    $push = new PushIOS();
                    $push->send($v['device_token'],$v['alert'],'3','1',$eventId);
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
        // $logger->info('result:'.var_export($result, true));
        $this->_helper->json($result);
    }

    public function posteventreplyAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getPost('event_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $contents = $req->getPost('contents');
        $logger->info('event_id:'.$event_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', contents:'.$contents);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $re = $updateDAO->postEventReply($event_id, $member_id, $kids_id, $contents);
            $logger->info('result:'.$re);

            if($re == 1){
                //push
                $rs = $updateDAO->getEventReplyAddPushInfo($event_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'3','2',$event_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'3','2',$event_id);
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

    public function replylistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $event_id = $req->getQuery('event_id');
        $reply_pageno = $req->getQuery('reply_pageno');
        $logger->info('event_id:'.$event_id.', reply_pageno:'.$reply_pageno);

        $replyList = array();
        $updateDAO = new EventDAO();
        $rs = $updateDAO->getEventReplyList($event_id, $reply_pageno, Constant::PAGESIZE);
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
                'event_id' => $v['event_id'],
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
        $reply_cnt = $updateDAO->getEventReplyCnt($event_id);
        $pagenator = Util::getReplyPagenator($reply_pageno, $reply_cnt);

        $this->view->replyList = $replyList;
        $this->view->reply_cnt = $reply_cnt;
        $this->view->event_id = $event_id;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function sendeventreadAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('event_id:'.$event_id.', member_id:'.$member_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->sendEventRead($event_id, $member_id);
            $logger->info('result:'.$rs);

            if($rs == 1){
                //push
                $rs = $updateDAO->getEventReadOverPushInfo($event_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'3','',$event_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'3','',$event_id);
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

    public function notreadmemberlistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();

        $event_id = $req->getQuery('event_id');
        $logger->info('event_id:'.$event_id);

        $notreadmemberlist = array();
        $updateDAO = new EventDAO();
        $rs = $updateDAO->getNotReadMemberList($event_id);
        $not_read_cnt = 0;
        foreach($rs as $v){
            $tmp = array(
                'kids_name' => $v['kids_name'],
                'kids_sex_img' => $v['kids_sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'birthday_str' => Util::chgDateFormat($v['birthday']),
                'kids_img' => $v['kids_img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['kids_img'],
                'member_name' => $v['member_name'],
                'phonenum' => $v['phonenum'],
                'class_name' => $v['class_name']
            );
            array_push($notreadmemberlist,$tmp);
            $not_read_cnt++;
        }

        $this->view->notreadmemberlist = $notreadmemberlist;
        $this->view->not_read_cnt = $not_read_cnt;
        $this->view->event_id = $event_id;
    }

    public function pushnotreadeventlistAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $logger->info('event_id:'.$event_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->getNotReadEventMemberList($event_id);
            foreach($rs as $v){
                if($v['device_type'] == '2'){//android
                    $push = new PushAndroid();
                    $re = $push->send($v['device_token'],$v['alert'],'3','',$event_id);
                    $logger->info($re);
                    $reObj = json_decode($re);
                    if($reObj->{'canonical_ids'} === 1 ){
                        $rs = $updateDAO->expireDeviceToken($v['device_token']);
                        $logger->info('canonical expireDeviceToken result:'.$rs);
                    }
                }else{
                    $push = new PushIOS();
                    $push->send($v['device_token'],$v['alert'],'3','',$event_id);
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

    public function deleteAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $logger->info('event_id:'.$event_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->deleteEvent($event_id);
            $logger->info('event_id:'.$event_id.' result => '.$rs);
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

    public function pluseventgoodcntAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('event_id:'.$event_id.', member_id:'.$member_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->plusEventGoodCnt($event_id, $member_id);
            $logger->info('event_id:'.$event_id.' result => '.$rs);
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

    public function addscheduledataAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $date = $req->getQuery('date');
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $logger->info('event_id:'.$event_id.', date:'.$date.', center_id:'.$center_id.', member_id:'.$member_id.', kids_id:'.$kids_id.'');

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->addScheduleDataByThread($center_id, $date, $member_id, $kids_id, 1, $event_id, '');
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

    public function deleteeventreplyAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $event_id = $req->getQuery('event_id');
        $reply_id = $req->getQuery('reply_id');
        $logger->info('event_id:'.$event_id.', reply_id:'.$reply_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->deleteEventReply($event_id, $reply_id);
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

    public function updatecontentsAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $event_id = $req->getPost('event_id');
        $class_id = $req->getPost('class_id');
        $title = $req->getPost('title');
        $contents = $req->getPost('contents');
        $class_id = $class_id == '' ? '-1' : $class_id;
        $address = $req->getPost('address');
        $date = $req->getPost('date');

        $logger->info('center_id:'.$center_id.', event_id:'.$event_id.', class_id:'.$class_id.', member_id:'.$member_id.', title:'.$title.', contents:'.$contents.', address:'.$address.', date:'.$date);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            $updateDAO = new EventDAO();
            $rs = $updateDAO->updateEventContents($event_id, $class_id, $title, $contents, $address, $date);
            $logger->info('updateEventContents result:'.$rs);
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