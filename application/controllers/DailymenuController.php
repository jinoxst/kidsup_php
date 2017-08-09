<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/DailymenuDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class DailymenuController extends BaseController
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $class_id = parent::getSessionValue('class_id');
        $trigger = $req->getQuery('trigger');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $class_id = $class_id == '' ? -1 : $class_id;
        $logger->info('1-> center_id:'.$center_id.', class_id:'.$class_id.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new DailymenuDAO();
        $totalCnt = $updateDAO->web_getDailymenuListTotalCount($center_id, $class_id);
        $logger->info('totalCnt:'.$totalCnt);
        if($totalCnt <= 10 && (int)$pageno > 1){
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
        $logger->info('2-> center_id:'.$center_id.', class_id:'.$class_id.', pageno:'.$pageno.', history_seq:'.$history_seq);

        $rs = $updateDAO->getDailymenuList($center_id, $class_id, $pageno, Constant::PAGESIZE);
        $olist = array();
        foreach($rs as $v){
            $arr_thm_img = explode(Constant::NEWLINE, $v['thm_img']);
            $arr_chg_img = explode(Constant::NEWLINE, $v['chg_img']);
            $arr_title = explode(Constant::NEWLINE, $v['titles']);
            $ilist = array();
            for($i=0;$i<(int)$v['menu_cnt'];$i++){
                $title = $arr_title[$i];
                $order = array("\r\n", "\n", "\r");
                $br_title = str_replace($order, '<br>', $title);
                $strLen = mb_strlen($title);
                if($strLen > 18){
                    $title = mb_substr($title, 0, 18).'...';
                }
                array_push($ilist, 
                    array(
                        'title' => $title,
                        'br_title' => $br_title,
                        'thm_img' => $arr_thm_img[$i],
                        'chg_img' => $arr_chg_img[$i]
                    )
                );
            }
            array_push($olist, 
                array(
                    'date' => $v['date'],
                    'date_str' => Util::chgDateFormat($v['date']).' ('.Util::getDayOfWeekFromDate($v['date']).')',
                    'menu_cnt' => $v['menu_cnt'],
                    'ilist' => $ilist
                )
            );
        }

        $this->view->olist = $olist;
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function addformAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
    }

    public function postcontentsAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);

        $req = $this->getRequest();
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $date = $req->getPost('date');
        $title = $req->getPost('title');
        $image_file_str = $req->getPost('image_file_str');
        $class_id = '-1';
        $filename = '';
        $logger->info('1 -> center_id:'.$center_id.', member_id:'.$member_id.', class_id:'.$class_id.', title:'.$title.', image_file_str:'.$image_file_str);
        $result['status'] = Message::OK;
        $result['message'] = '';
        try{
            //image file process
            if(isset($image_file_str) && strlen($image_file_str) > 0){
                $files = explode(';', $image_file_str);
                $workDir = Constant::IMAGE_WORK_DIR . 'c' . $center_id . '/'. Constant::DAILYMENU_DIR . '/' . $member_id;
                $targetDir = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::DAILYMENU_DIR;
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
                            $filename = $newFileName;
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
                            }
                        }
                    }
                }
                //delete work dir
                Util::rmDir($workDir);
            }
            $logger->info('2 -> center_id:'.$center_id.', member_id:'.$member_id.', class_id:'.$class_id.', title:'.$title.', filename:'.$filename);

            $updateDAO = new DailymenuDAO();
            $rst = $updateDAO->postDailymenuData($center_id, $date, $title, $member_id, $filename, $class_id);
            $logger->info('postDailymenuData result:'.$rst);

            //push
            $rs = $updateDAO->getDailymenuTargetPushList($center_id, $class_id, $member_id);
            foreach($rs as $v){
                if($v['device_type'] == '2'){//android
                    $push = new PushAndroid();
                    $re = $push->send($v['device_token'],$v['alert'],'7','1','');
                    $logger->info($re);
                    $reObj = json_decode($re);
                    // $logger->info(var_export($reObj, true));
                    if($reObj->{'canonical_ids'} === 1 ){
                        $rs = $updateDAO->expireDeviceToken($v['device_token']);
                        $logger->info('canonical expireDeviceToken result:'.$rs);
                    }
                }else{
                    $push = new PushIOS();
                    $push->send($v['device_token'],$v['alert'],'7','1','');
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

    public function deleteAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $req = $this->getRequest();
        $date = $req->getQuery('date');
        $filename = $req->getQuery('filename');
        $logger->info('center_id:'.$center_id.', date:'.$date.', filename:'.$filename);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $org_file_full_path = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::DAILYMENU_DIR . '/' . Constant::IMAGE_ORG_DIR . '/' . $filename;
            $chg_file_full_path = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::DAILYMENU_DIR . '/' . Constant::IMAGE_CHG_DIR . '/' . $filename;
            $thm_file_full_path = Constant::IMAGE_MAIN_DIR . 'c' . $center_id . '/'. Constant::DAILYMENU_DIR . '/' . Constant::IMAGE_THM_DIR . '/thm_' . $filename;
            unlink($org_file_full_path);
            unlink($chg_file_full_path);
            unlink($thm_file_full_path);
            $updateDAO = new DailymenuDAO();
            $rst = $updateDAO->deleteDailymenuData($center_id, $date, $filename);
            $logger->info('deleteDailymenuData rst:'.$rst);
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