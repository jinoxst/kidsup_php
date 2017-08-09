<?php
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log/Formatter/Simple.php';
require_once 'Zend/Date.php';
require_once APPLICATION_PATH.'/library/Constant.php';
require_once APPLICATION_PATH.'/library/Util.php';

class Logger{
    private $logger;
    private $className;
    private $functionName;
    private $fileTail;
    private $config;
    public function __construct($className=NULL, $functionName=NULL, $tail='WEB'){
        $cache = Zend_Cache::factory('File','File',
            array(
                'lifetime' => 7200,
                'automatic_serialization' => TRUE,
                'master_files' => array(APPLICATION_PATH.'/configs/application.ini')
            ),
            array(
                'cache_dir' => APPLICATION_PATH.'/../data/cache'
            )
        );
        if(!($this->config = $cache->load('log'))){
            $this->config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'log');
            $cache->save($this->config, 'log');
        }

        if($className != NULL){
            if(Util::endsWith($className,Constant::CONTROLLER)){
                $this->className = '['.substr($className,0,(strlen($className)-strlen(Constant::CONTROLLER))).']';
            }else{
                $this->className = '['.$className.']';
            }
        }
        if($functionName != NULL){
            if(Util::endsWith($functionName,Constant::ACTION)){
                $this->functionName = '['.substr($functionName,0,(strlen($functionName)-strlen(Constant::ACTION))).']';
            }else{
                $this->functionName = '['.$functionName.']';
            }
        }
        $this->fileTail = $tail;
        $this->common();
    }

    public function common(){
        $now = new Zend_Date();
        $log_file_format = $now->toString('yyyyMMdd').'-'.$this->fileTail;
        $writer = new Zend_Log_Writer_Stream(Constant::LOG_DIR.$log_file_format.'.log');
        $log_format = date('Y/m/d H:i:s').'[%priorityName%]%message%'.PHP_EOL;
        $format = new Zend_Log_Formatter_Simple($log_format);
        $writer->setFormatter($format);
        $this->logger = new Zend_Log($writer);
        if($this->config && $this->config->level) {
            $this->logger->addFilter(new Zend_Log_Filter_Priority((int)$this->config->level));
        }else{
            $this->logger->addFilter(new Zend_Log_Filter_Priority(Zend_Log::DEBUG));
        }
    }

    public function debug($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::DEBUG);
    }

    public function info($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::INFO);
    }

    public function notice($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::NOTICE);
    }

    public function warn($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::WARN);
    }

    public function error($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::ERR);
    }

    public function crit($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::CRIT);
    }

    public function alert($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::ALERT);
    }

    public function emerge($msg=''){
        $this->common();
        $this->logger->log($this->className.''.$this->functionName.' '.$msg.' ['.Util::getClientIp(false).']', Zend_Log::EMERG);
    }
}