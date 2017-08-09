<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/MngDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class MngController extends BaseController
{
    public function approvallistAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $trigger = $req->getQuery('trigger');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> center_id:'.$center_id.', member_type:'.$member_type.', member_id:'.$member_id.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new MngDAO();
        $totalCnt = $updateDAO->web_getApprovalListTotalCount(array($center_id, $member_id));
        $logger->info('totalCnt:'.$totalCnt);

        if($pageno > 1 && $totalCnt == Constant::PAGESIZE){
            $pageno = 1;
        }
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
        $logger->info('1-> center_id:'.$center_id.', member_type:'.$member_type.', member_id:'.$member_id.', pageno:'.$pageno.', history_seq:'.$history_seq);

        $rs = $updateDAO->getApprovalList(array($center_id, $member_id, $member_type, $pageno, Constant::PAGESIZE));
        $list = array();
        foreach($rs as $v){
            $tmp = array(
                'member_id' => $v['member_id'],
                'member_type' => $v['member_type'],
                'kids_id' => $v['kids_id'] == '' ? '-1' : $v['kids_id'],
                'kids_name' => $v['kids_name'],
                'kids_sex_img' => $v['kids_sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'birthday_str' => Util::chgDateFormat($v['kids_birthday']),
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'email' => $v['email'],
                'member_name' => $v['member_name'],
                'phonenum' => $v['phonenum'],
                'class_name' => $v['class_name'],
                'createtime' => $v['createtime2']
            );
            array_push($list,$tmp);
        }

        $this->view->list = $list;
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function approveAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = $req->getQuery('member_id');
        $member_type = $req->getQuery('member_type');
        $kids_id = $req->getQuery('kids_id');
        $logger->info('member_id:'.$member_id.', member_type:'.$member_type.', kids_id:'.$kids_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->appvove(array($member_id, $member_type, $kids_id));
            $logger->info('result => '.$rs);
            if($rs == 1){
                //push
                $rs = $updateDAO->getApproveRequestSuccessInfo($member_id);
                $pushSendCnt = 0;
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'5','','');
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                        $pushSendCnt++;
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'5','','');
                        $pushSendCnt++;
                    }
                }
                $result['push_send_cnt'] = $pushSendCnt;
            }else{
                $logger->info('push is not sent(result not 1)');
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

    public function rejectAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = $req->getQuery('member_id');
        $member_type = $req->getQuery('member_type');
        $kids_id = $req->getQuery('kids_id');
        $logger->info('member_id:'.$member_id.', member_type:'.$member_type.', kids_id:'.$kids_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->reject(array($member_id, $member_type, $kids_id));
            $logger->info('result => '.$rs);
            if($rs == 1){
                //push
                $rs = $updateDAO->getApproveRequestRejectInfo($member_id);
                $pushSendCnt = 0;
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'8','','');
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                        $pushSendCnt++;
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'8','','');
                        $pushSendCnt++;
                    }
                }
                $result['push_send_cnt'] = $pushSendCnt;
            }else{
                $logger->info('push is not sent(result not 1)');
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

    public function attendanceinfoAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $history_seq = $req->getQuery('history_seq');
        $class_id = $req->getQuery('class_id');

        $updateDAO = new MngDAO();
        if($class_id == ''){
            $class_id = $updateDAO->getMemberClassLeastOne(array($member_id));
        }
        $date = $req->getQuery('date');
        if($date == ''){
            $date = date('Y').date('m').date('d');
        }
        $logger->info('1-> center_id:'.$center_id.', member_id:'.$member_id.', class_id:'.$class_id.', date:'.$date.', history_seq:'.$history_seq);
        
        $rs = $updateDAO->getAttendanceInfo(array($center_id, $date, $class_id));
        $list = array();
        foreach($rs as $v){
            $tmp = array(
                'id' => $v['id'] == '' ? '-1' : $v['id'],
                'name' => $v['name'],
                'sex_img' => $v['sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'birthday_str' => Util::chgDateFormat($v['birthday']),
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'attendance' => $v['attendance']
            );
            array_push($list,$tmp);
        }

        $classlist = array();
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

        $this->view->classlist = $classlist;
        $this->view->list = $list;
        $this->view->class_id = $class_id;
        $this->view->date = $date;
    }

    public function checkattendanceAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $req = $this->getRequest();
        $class_id = $req->getQuery('class_id');
        $date = $req->getQuery('date');
        $kids_id_str = $req->getQuery('kids_id_str');
        $logger->info('center_id:'.$center_id.', class_id:'.$class_id.', date:'.$date.', kids_id_str:'.$kids_id_str);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            if(strlen($kids_id_str) > 0){
                $rs = $updateDAO->checkAttendance(array($center_id, $date, $class_id, $kids_id_str));
                $logger->info('result => '.$rs);
                $currentDate = date('Y').date('m').date('d');
                if($rs == 1){
                    $pushSendCnt = 0;
                    if($date == $currentDate){
                        //push
                        $rs = $updateDAO->getAttendanceCheckMemberList(array($center_id, $class_id, $kids_id_str));
                        foreach($rs as $v){
                            if($v['device_type'] == '2'){//android
                                $push = new PushAndroid();
                                $re = $push->send($v['device_token'],$v['alert'],'9','1','');
                                $logger->info($re);
                                $reObj = json_decode($re);
                                // $logger->info(var_export($reObj, true));
                                if($reObj->{'canonical_ids'} === 1 ){
                                    $rs = $updateDAO->expireDeviceToken($v['device_token']);
                                    $logger->info('canonical expireDeviceToken result:'.$rs);
                                }
                                $pushSendCnt++;
                            }else{
                                $push = new PushIOS();
                                $push->send($v['device_token'],$v['alert'],'9','1','');
                                $pushSendCnt++;
                            }
                        }
                    }
                    $result['push_send_cnt'] = $pushSendCnt;
                }else{
                    $logger->info('push is not sent(result not 1)');
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

    public function clslistAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $logger->info('1-> center_id:'.$center_id);
        
        $updateDAO = new MngDAO();
        $list = $updateDAO->getMngClassList(array($center_id));
        $this->view->list = $list;
    }

    public function updateclassinfoAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $class_id = $req->getPost('class_id');
        $class_name = $req->getPost('class_name');
        $class_desc = $req->getPost('class_desc');
        $logger->info('class_id:'.$class_id.', class_name:'.$class_name.', class_desc:'.$class_desc);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->updateClassInfo(array($class_id, $class_name, $class_desc));
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

    public function deleteclassinfoAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $class_id = $req->getQuery('class_id');
        $logger->info('class_id:'.$class_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->deleteClassInfo(array($class_id));
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

    public function postclassinfoAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $req = $this->getRequest();
        $class_name = $req->getPost('class_name');
        $class_desc = $req->getPost('class_desc');
        $logger->info('center_id:'.$center_id.', class_name:'.$class_name.', class_desc:'.$class_desc);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->postClassInfo(array($center_id, $class_name, $class_desc));
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

    public function kidslistAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $updateDAO = new MngDAO();

        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $class_id = $req->getQuery('class_id');
        if($class_id == ''){
            $class_id = $updateDAO->getMemberClassLeastOne(array($member_id));
        }
        $logger->info('1-> center_id:'.$center_id.', member_id:'.$member_id.', class_id:'.$class_id);
        
        $rs = $updateDAO->getKidsList(array($center_id, $class_id));
        $list = array();
        foreach($rs as $v){
            $tmp = array(
                'kids_id' => $v['id'],
                'kids_name' => $v['name'],
                'kids_sex_img' => $v['sex'] == '1' ? '/img/icon/icon_boy.png' : '/img/icon/icon_girl.png',
                'birthday_str' => Util::chgDateFormat($v['birthday']),
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'member_name' => $v['member_name'],
                'phonenum' => $v['phonenum']
            );
            array_push($list,$tmp);
        }

        $classlist = array();
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

        $allclasslist = array();
        if($member_type == '1'){
            $allclasslist = $classlist;
        }else{
            $rs = $updateDAO->getMngClassList($center_id);
            foreach($rs as $v){
                $tmp = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'desc' => $v['desc'],
                    'approved_cnt' => $v['approved_cnt']
                );
                array_push($allclasslist,$tmp);
            }
        }

        $this->view->class_id = $class_id;
        $this->view->classlist = $classlist;
        $this->view->allclasslist = $allclasslist;
        $this->view->list = $list;
    }

    public function teacherlistAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $updateDAO = new MngDAO();

        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('1-> center_id:'.$center_id.', member_id:'.$member_id);
        
        $rs = $updateDAO->getTeacherList(array($center_id));
        $list = array();
        foreach($rs as $v){
            $class_info = $v['class_info'];
            $classArr = explode('::',$class_info);
            $firstClassArr = explode('||',$classArr[0]);
            $classStr = $firstClassArr[1];
            if(count($classArr) > 1){
                $classStr .= ' +' . (count($classArr) - 1);
            }
            $tmp = array(
                'id' => $v['id'],
                'name' => $v['name'],
                'img' => $v['img'] == '' ? Constant::NO_IMG_URL  : Constant::IMG_HOST_URL.''.$v['img'],
                'phonenum' => $v['phonenum'],
                'class_str' => $classStr,
                'class_info' => $v['class_info']
            );
            array_push($list,$tmp);
        }

        $classlist = array();
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

        $this->view->classlist = $classlist;
        $this->view->list = $list;
    }

    public function voidkidsapprovalAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $kids_id = $req->getQuery('kids_id');
        $logger->info('kids_id:'.$kids_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->voidKidsApproval(array($kids_id));
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

    public function changekidsclassAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $kids_id = $req->getQuery('kids_id');
        $class_id = $req->getQuery('class_id');
        $logger->info('kids_id:'.$kids_id.', class_id:'.$class_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->changeKidsClass(array($kids_id, $class_id));
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

    public function voidteacherapprovalAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = $req->getQuery('member_id');
        $logger->info('member_id:'.$member_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->voidTeacherApproval(array($member_id));
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

    public function changeteacherclassAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $member_id = $req->getQuery('member_id');
        $class_id = $req->getQuery('class_id');
        $logger->info('member_id:'.$member_id.', class_id:'.$class_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->changeTeacherClass(array($member_id, $class_id));
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

    public function invitationcodeAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $updateDAO = new MngDAO();

        $center_id = parent::getSessionValue('center_id');
        $logger->info('center_id:'.$center_id);
        
        $invitaion_code = $updateDAO->getInvitationCode(array($center_id));

        $this->view->invitation_code = $invitaion_code;
    }

    public function setinvitationcodeAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $logger->info('center_id:'.$center_id);

        $result['status'] = Message::OK;
        $result['message'] = '';
        $result['invitation_code'] = '';

        try{
            $updateDAO = new MngDAO();
            $rs = $updateDAO->setInvitationCode(array($center_id));
            $logger->info('result => '.$rs);
            $result['invitation_code'] = $rs;
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