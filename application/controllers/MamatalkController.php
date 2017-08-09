<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/MamatalkDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class MamaTalkController extends BaseController
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $class_id = $req->getQuery('class_id');
        $class_id = $class_id == '00' ? '-1' : $class_id;
        $trigger = $req->getQuery('trigger');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> center_id:'.$center_id.', member_type:'.$member_type.', member_id:'.$member_id.', class_id:'.$class_id.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new MamatalkDAO();
        $totalCnt = $updateDAO->web_getMamaTalkListTotalCount($center_id, $class_id);
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

        $rs = $updateDAO->getMamaTalkList($center_id, $class_id, $pageno, Constant::PAGESIZE);
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
                'mamatalk_id' => $v['mamatalk_id'],
                'center_id' => $v['center_id'],
                'title' => $title,
                'contents' => $contents,
                'createtime' => $v['createtime'],
                'writer_name' => $v['writer_name'],
                'good_cnt' => $v['goodcnt'],
                'reply_cnt' => $v['reply_cnt'],
                'class_name' => $v['class_name']
            );
            if($v['img1'] != '' || $v['img2'] != '' || $v['img3'] != '' || $v['img4'] != '' || $v['img5'] != '' || $v['img6'] != '' || $v['img7'] != '' || $v['img8'] != '' || $v['img9'] != '' || $v['img10'] != ''){
                $tmp['have_pictures_url'] = '/img/icon/picture.png';
            }
            array_push($list,$tmp);
        }

        $classlist = array();
        $rs = $updateDAO->getMngClassList($center_id);
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
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function detailAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $reply_pageno = $req->getQuery('reply_pageno');
        $reply_cnt = $req->getQuery('reply_cnt');
        $member_id = parent::getSessionValue('member_id');
        $member_type = parent::getSessionValue('member_type');
        $logger->info('mamatalk_id:'.$mamatalk_id.', member_id:'.$member_id);
        $updateDAO = new MamatalkDAO();
        $rs = $updateDAO->getMamaTalkDetail($mamatalk_id);
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

        $deletableMamaTalk = $member_id == $rs['writer_id'] ? '1' : '';

        for($i=1;$i<=10;$i++){
            if($rs['img'.$i] != ''){
                $rs['img'.$i.'_filename'] = basename($rs['img'.$i]);
                $ext = pathinfo(Constant::IMAGE_MAIN_DIR.'c'.$center_id.'/'.Constant::MAMATALK_DIR.'/'.Constant::IMAGE_ORG_DIR.''.$rs['img'.$i], PATHINFO_EXTENSION);
                $rs['img'.$i.'_only_filename'] = basename($rs['img'.$i], '.'.$ext);
            }
        }

        $replyList = array();
        $rs2 = $updateDAO->getMamaTalkReplyList($mamatalk_id, $reply_pageno, Constant::PAGESIZE);
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
                'mamatalk_id' => $v['mamatalk_id'],
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
        $this->view->deletableMamaTalk = $deletableMamaTalk;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function updateformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('mamatalk_id:'.$mamatalk_id.', member_id:'.$member_id);
        $updateDAO = new MamatalkDAO();
        $rs = $updateDAO->getMamaTalkDetail($mamatalk_id);
        $rs['writer_img'] = Constant::IMG_HOST_URL.''.$rs['writer_img'];
        if($rs['writer_type'] == '1'){
            $rs['writer_type_desc'] = '管理者';
        }else if($rs['writer_type'] == '2'){
            $rs['writer_type_desc'] = '先生';
        }else{
            $rs['writer_type_desc'] = 'キッズ会員（保護者）';
        }
        $this->view->detail = $rs;
    }

    public function addformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $class_id = $req->getQuery('class_id');
        $this->view->class_id = $class_id;
    }

    public function postcontentsAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $class_id = parent::getSessionValue('class_id');
        $title = $req->getPost('title');
        $contents = $req->getPost('contents');
        $image_file_str = $req->getPost('image_file_str');

        $logger->info('center_id:'.$center_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', class_id:'.$class_id.' title:'.$title.', contents:'.$contents.', image_file_str:'.$image_file_str);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            $updateDAO = new MamatalkDAO();
            $mamatalkId = $updateDAO->postMamaTalkContents($center_id, $class_id, $member_id, $kids_id, $title, $contents);
            $logger->info('mamatalk id:'.$mamatalkId);

            //image file process
            if(isset($image_file_str) && strlen($image_file_str) > 0){
                $files = explode(';', $image_file_str);
                $workDir = Constant::IMAGE_WORK_DIR . 'c' . $center_id . '/'. Constant::MAMATALK_DIR . '/' . $member_id;
                $targetDir = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::MAMATALK_DIR;
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
                                $rs = $updateDAO->postMamaTalkImage($mamatalkId, $newFileName, $imageInfo[0], $imageInfo[1]);
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

    public function postmamatalkreplyAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getPost('mamatalk_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $contents = $req->getPost('contents');
        $logger->info('mamatalk_id:'.$mamatalk_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', contents:'.$contents);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $re = $updateDAO->postMamaTalkReply($mamatalk_id, $member_id, $kids_id, $contents);
            $logger->info('result:'.$re);

            if($re == 1){
                //push
                $rs = $updateDAO->getMamaTalkReplyAddPushInfo($mamatalk_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'6','1',$mamatalk_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'6','1',$mamatalk_id);
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

        $mamatalk_id = $req->getQuery('mamatalk_id');
        $reply_pageno = $req->getQuery('reply_pageno');
        $logger->info('mamatalk_id:'.$mamatalk_id.', reply_pageno:'.$reply_pageno);

        $replyList = array();
        $updateDAO = new MamatalkDAO();
        $rs = $updateDAO->getMamaTalkReplyList($mamatalk_id, $reply_pageno, Constant::PAGESIZE);
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
                'mamatalk_id' => $v['mamatalk_id'],
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
        $reply_cnt = $updateDAO->getMamaTalkReplyCnt($mamatalk_id);
        $pagenator = Util::getReplyPagenator($reply_pageno, $reply_cnt);

        $this->view->replyList = $replyList;
        $this->view->reply_cnt = $reply_cnt;
        $this->view->mamatalk_id = $mamatalk_id;
        $this->view->reply_pageno = $reply_pageno;
        $this->view->pagenator = $pagenator;
    }

    public function sendmamatalkreadAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('mamatalk_id:'.$mamatalk_id.', member_id:'.$member_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->sendMamaTalkRead($mamatalk_id, $member_id);
            $logger->info('result:'.$rs);

            if($rs == 1){
                //push
                $rs = $updateDAO->getMamaTalkReadOverPushInfo($mamatalk_id);
                foreach($rs as $v){
                    if($v['device_type'] == '2'){//android
                        $push = new PushAndroid();
                        $re = $push->send($v['device_token'],$v['alert'],'1','',$mamatalk_id);
                        $logger->info($re);
                        $reObj = json_decode($re);
                        // $logger->info(var_export($reObj, true));
                        if($reObj->{'canonical_ids'} === 1 ){
                            $rs = $updateDAO->expireDeviceToken($v['device_token']);
                            $logger->info('canonical expireDeviceToken result:'.$rs);
                        }
                    }else{
                        $push = new PushIOS();
                        $push->send($v['device_token'],$v['alert'],'1','',$mamatalk_id);
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

    public function deleteAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $logger->info('mamatalk_id:'.$mamatalk_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->deleteMamaTalk($mamatalk_id);
            $logger->info('mamatalk_id:'.$mamatalk_id.' result => '.$rs);
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

    public function plusmamatalkgoodcntAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $member_id = parent::getSessionValue('member_id');
        $logger->info('mamatalk_id:'.$mamatalk_id.', member_id:'.$member_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->plusMamaTalkGoodCnt($mamatalk_id, $member_id);
            $logger->info('mamatalk_id:'.$mamatalk_id.' result => '.$rs);
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
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $date = $req->getQuery('date');
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $logger->info('mamatalk_id:'.$mamatalk_id.', date:'.$date.', center_id:'.$center_id.', member_id:'.$member_id.', kids_id:'.$kids_id.'');

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->addScheduleDataByThread($center_id, $date, $member_id, $kids_id, 1, $mamatalk_id, '');
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

    public function deletemamatalkreplyAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $mamatalk_id = $req->getQuery('mamatalk_id');
        $reply_id = $req->getQuery('reply_id');
        $logger->info('mamatalk_id:'.$mamatalk_id.', reply_id:'.$reply_id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->deleteMamaTalkReply($mamatalk_id, $reply_id);
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
        $mamatalk_id = $req->getPost('mamatalk_id');
        $class_id = $req->getPost('class_id');
        $title = $req->getPost('title');
        $contents = $req->getPost('contents');
        $delete_image_str = $req->getPost('deleteImageStr');
        $add_image_str = $req->getPost('addImageStr');
        $class_id = $class_id == '' ? '-1' : $class_id;

        $logger->info('center_id:'.$center_id.', mamatalk_id:'.$mamatalk_id.', class_id:'.$class_id.', member_id:'.$member_id.', title:'.$title.', contents:'.$contents.', delete_image_str:'.$delete_image_str.', add_image_str:'.$add_image_str);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            $updateDAO = new MamatalkDAO();
            $rs = $updateDAO->updateMamaTalkContents($mamatalk_id, $class_id, $title, $contents);
            $logger->info('updateMamaTalkContents result:'.$rs);

            //delete image file process
            if(isset($delete_image_str) && strlen($delete_image_str) > 0){
                $files = explode(';', $delete_image_str);
                foreach($files as $file){
                    if(isset($file) && strlen($file) > 0){
                        $rs = $updateDAO->deleteMamaTalkImage($mamatalk_id, $file);
                        $logger->info($file.' is deleted result : '.$rs);
                    }
                }
            }

            //add image file process
            if(isset($add_image_str) && strlen($add_image_str) > 0){
                $files = explode(';', $add_image_str);
                $workDir = Constant::IMAGE_WORK_DIR . 'c' . $center_id . '/'. Constant::MAMATALK_DIR . '/' . $member_id;
                $targetDir = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::MAMATALK_DIR;
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
                                $rs = $updateDAO->postMamaTalkImage($mamatalk_id, $newFileName, $imageInfo[0], $imageInfo[1]);
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