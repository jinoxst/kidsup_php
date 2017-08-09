<?php
class Message {
    const OK                       = '0';
    const ParameterInvalid         = '2';
    const NotFound                 = '3';
    const RegistrationError        = '4';
    const XmlParsingError          = '5';
    const UpdateError              = '6';
    const NetworkConnectionError   = '7';
    const XMLSchemaError           = '8';
    const ConnectionTimeOut        = '12';
    const NetworkConnectionTimeOut = '13';
    const DBError                  = '14';
    const SystemError              = '15';
    const NoActionException        = '-1';
    const NoControlException       = '-2';
    const NotSynchError            = '-99';
    const NotExistIssuePw          = '101';
    const OverIssuePwExpireDate    = '102';
    const AlreadyIssuedPw          = '103';
    const IssePwOK_Context         = '104';
    const ServiceStartOk_Context   = '105';

    private static $message_array = array(
        '0' => 'OK',
        '2' => 'Parameter Invalidate',
        '3' => 'Not Found',
        '4' => 'Registration Error',
        '5' => 'XML Parsing Error',
        '6' => 'Update Error',
        '7' => 'Network Connection Error',
        '8' => 'XML Schema Error',
        '12' => 'Connection TimeOut',
        '13' => 'Network Connection TimeOut',
        '14' => 'DB Error',
        '15' => 'System Error',
        '-1' => 'NoActionException',
        '-2' => 'NoControlException',
        '-99' => 'NotSynchError',
        '101' => 'not exist(IssuePw)',
        '102' => 'ExpireDate is over(IssuePw)',
        '103' => 'already issued pw'
    );

    public static function getMessage($result){
        if(isset($result['message']) && $result['message'] != ''){
            return $result['message'];
        }else{
            return isset(self::$message_array[$result['status']]) ? self::$message_array[$result['status']] : '';
        }
    }
}