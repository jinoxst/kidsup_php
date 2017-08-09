<?php

require_once APPLICATION_PATH.'/library/ApnsPHP/Autoload.php';
require_once APPLICATION_PATH.'/library/ApnsPHP/Log/Simplelog.php';

class PushIOS {
	public function send($token, $alert, $thread_type, $thread_sub_type, $thread_id) {
        $logger = new Logger(__CLASS__, __FUNCTION__);
        try{
            $push = null;
            $push = new ApnsPHP_Push(
                ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
                APPLICATION_PATH.'/library/server_certificates_bundle_production.pem'
            );
            $push->setLogger(new ApnsPHP_Log_Simple());
            $push->setRootCertificationAuthority(APPLICATION_PATH.'/library/entrust_root_certification_authority.pem');
            $push->connect();

            $apns = new ApnsPHP_Message($token);
            $apns->setText($alert);
            $apns->setCustomData(
                array('thread_type' => $thread_type, 'thread_subtype' => $thread_sub_type, 'thread_id' => $thread_id)
            );
            $apns->setBadge(1);
            $apns->setSound();
            $apns->setExpiry(3600);
            $push->add($apns);
            $logger->info('deviceToken:'.$token);
            $push->send();
            $push->disconnect();
            $aErrorQueue = $push->getErrors();
            if (empty($aErrorQueue)) {
                $logger->info('OK');
            }else{
                $logger->info('NG['.count($aErrorQueue).']');
                foreach($aErrorQueue as $key => $queue){
                    $apnsErrObject = $queue['MESSAGE'];
                    $errDeviceTokenArr  = $apnsErrObject->getRecipients();
                    $errDeviceToken = $errDeviceTokenArr[0];
                    $m = $apnsErrObject->getMaidoData();
                    $errorMsg = $queue['ERRORS'][0]['statusMessage'];
                    $logger->error($errorMsg);
                }
            }
        }catch(ApnsPHP_Exception $e){
            $logger->error($e->getMessage());
        }
    }
}