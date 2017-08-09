<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/AlbumDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';

class AlbumController extends BaseController
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $req = $this->getRequest();
        $trigger = $req->getQuery('trigger');
        $pageno = $req->getQuery('pageno');
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> member_id:'.$member_id.', kids_id:'.$kids_id.', trigger:'.$trigger.', pageno:'.$pageno.', history_seq:'.$history_seq);
        
        $updateDAO = new AlbumDAO();
        $totalCnt = $updateDAO->web_getAlbumListTotalCount($member_id, $kids_id);
        $logger->info('totalCnt:'.$totalCnt);
        if($totalCnt <= Constant::ALBUMPAGESIZE && (int)$pageno > 1){
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
            $pageno = Util::getLastPageno($totalCnt,'album');
            $groupno = Util::getLastGroupno($pageno);
        }
        $pagenator = Util::getPagenator($groupno, $totalCnt, 'album');
        $logger->info('2-> member_id:'.$member_id.', kids_id:'.$kids_id.', trigger:'.$trigger.', pageno:'.$pageno.', history_seq:'.$history_seq);

        $rs = $updateDAO->getAlbumList($member_id, $kids_id, $pageno, Constant::ALBUMPAGESIZE);
        $list = array();
        foreach($rs as $v){
            $memo = $v['memo'];
            $order = array("\r\n", "\n", "\r");
            $memo = str_replace($order, '<br>', $memo);
            $center_name = $v['center_name'];
            $date_str = Util::chgDateFormat($v['thread_time']);
            $title = $center_name . '<br>' . $memo . '<br>' . $date_str;
            $tmp = array(
                'member_id' => $v['member_id'],
                'kids_id' => $v['kids_id'],
                'idx' => $v['idx'],
                'memo' => $memo,
                'thm_img' => $v['thm_img'],
                'chg_img' => $v['chg_img'],
                'title' => $title
            );
            array_push($list,$tmp);
        }
        $this->view->list = $list;
        $this->view->pageno = $pageno;
        $this->view->pagenator = $pagenator;
    }

    public function addAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $req = $this->getRequest();
        $thread_id = $req->getQuery('thread_id');
        $thread_type = $req->getQuery('thread_type');
        $filename = $req->getQuery('filename');
        $logger->info('center_id:'.$center_id.', member_id:'.$member_id.', kids_id:'.$kids_id.', thread_id:'.$thread_id.', thread_type:'.$thread_type.', filename:'.$filename);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new AlbumDAO();
            $rst = $updateDAO->addAlbumData($member_id, $kids_id, $center_id, $thread_type, $thread_id, $filename);
            $logger->info('addAlbumData rst:'.$rst);
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
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $req = $this->getRequest();
        $idx = $req->getQuery('idx');
        $logger->info('member_id:'.$member_id.', kids_id:'.$kids_id.', idx:'.$idx);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new AlbumDAO();
            $rst = $updateDAO->deleteAlbumData($member_id, $kids_id, $idx);
            $logger->info('deleteAlbumData rst:'.$rst);
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