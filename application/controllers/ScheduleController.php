<?php
require_once 'BaseController.php';
require_once APPLICATION_PATH.'/library/Logger.php';
require_once APPLICATION_PATH.'/library/Message.php';
require_once APPLICATION_PATH.'/library/SimpleImage.php';
require_once APPLICATION_PATH.'/models/ScheduleDAO.php';
require_once APPLICATION_PATH.'/library/PushAndroid.php';
require_once APPLICATION_PATH.'/library/PushIOS.php';
require_once APPLICATION_PATH.'/library/calendar.php';

class ScheduleController extends BaseController
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $req = $this->getRequest();
        $month = $req->getQuery('month');
        if($month == ''){
            $month = date('Ym');
        }
        list($first_day, $last_day) = Util::getCalendarFirstLastDay($month);
        $history_seq = $req->getQuery('history_seq');
        $logger->info('1-> member_id:'.$member_id.', kids_id:'.$kids_id.', month:'.$month.', history_seq:'.$history_seq.', first_day:'.$first_day.', last_day:'.$last_day);
        
        $updateDAO = new ScheduleDAO();
        $param = array($center_id, $member_id, $kids_id, $first_day, $last_day);
        $rs = $updateDAO->getScheduleList($param);
        $result = array();
        foreach($rs as $v){
            $title = $v['title'];
            $contents = $v['contents'];
            if($v['attendance'] == '1'){
                $title = '出席';
                $contents = '出席が確認されました。';
            }
            $tmp = array(
                'id' => $v['id'],
                'title' => $title,
                'start' => $v['date'],
                'contents' => $contents,
                'attendance' => $v['attendance'],
                'deletable' => $v['deletable'],
                'updatable' => $v['updatable']
            );
            array_push($result,$tmp);
        }
        $data = Util::escapeJsonChar(json_encode($result));
        //$logger->info($data);
        $this->view->data = $data;
        $this->view->defaultYear = substr($month,0,4);
        $this->view->defaultMonth = substr($month,4,2);
    }

    public function addAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $center_id = parent::getSessionValue('center_id');
        $member_id = parent::getSessionValue('member_id');
        $kids_id = parent::getSessionValue('kids_id');
        $req = $this->getRequest();
        $date = $req->getPost('date');
        $title = $req->getPost('title');
        $detail = $req->getPost('detail');
        $time = $req->getPost('time');
        $param = array($center_id, $date, $member_id, $kids_id, $title, $detail, $time);
        $logger->info(var_export($param, true));

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ScheduleDAO();
            $rst = $updateDAO->addScheduleData($param);
            $logger->info('addScheduleData rst:'.$rst);
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
        $req = $this->getRequest();
        $id = $req->getQuery('id');
        $logger->info('member_id:'.$member_id.', schedule id:'.$id);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ScheduleDAO();
            $rst = $updateDAO->deleteScheduleData($id);
            $logger->info('deleteScheduleData rst:'.$rst);
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

    public function updateAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = parent::getSessionValue('member_id');
        $req = $this->getRequest();
        $id = $req->getPost('id');
        $title = $req->getPost('title');
        $detail = $req->getPost('detail');
        $logger->info('member_id:'.$member_id.', schedule id:'.$id.', title:'.$title.', detail:'.$detail);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ScheduleDAO();
            $rst = $updateDAO->updateScheduleData(array($id, $title, $detail));
            $logger->info('updateScheduleData rst:'.$rst);
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

    public function chgdateAction(){
        $this->_helper->layout->disableLayout();
        $logger = new Logger(__CLASS__, __FUNCTION__);
        $member_id = parent::getSessionValue('member_id');
        $req = $this->getRequest();
        $id = $req->getQuery('id');
        $date = $req->getQuery('date');
        $logger->info('member_id:'.$member_id.', schedule id:'.$id.', date:'.$date);

        $result['status'] = Message::OK;
        $result['message'] = '';

        try{
            $updateDAO = new ScheduleDAO();
            $rst = $updateDAO->changeScheduleDate(array($id, $date));
            $logger->info('changeScheduleDate rst:'.$rst);
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