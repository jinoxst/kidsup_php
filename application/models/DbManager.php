<?php
require_once 'Zend/Cache.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Db.php';
require_once APPLICATION_PATH.'/library/Logger.php';

class DbManager {
    public static function getConnection($flag) {
        $db = NULL;
        $config = NULL;
        try {
            if($flag == 0){
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
                if(!($config = $cache->load('database_slave'))){
                    $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'database_slave');
                    $cache->save($config, 'database_slave');
                }
            }else if ($flag == 1){
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
                if(!($config = $cache->load('database_main'))){
                    $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'database_main');
                    $cache->save($config, 'database_main');
                }
            }
            $db = Zend_Db::factory($config);
        } catch (Zend_Exception $e) {
            $logger = new Logger(__CLASS__, __FUNCTION__,'DB');
            $logger->error($e->getMessage());
        }
        return $db;
    }
}
