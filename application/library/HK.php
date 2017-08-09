<?php

require_once 'Zend/Validate/Abstract.php';

class CrmWebService_Regex_Util_HK extends Zend_Validate_Abstract {
    const TEXT = 'text';

    protected $_messageTemplates = array(
        self::TEXT => "'%value%' is not hankaku katakana"
    );

    public function isValid($value){
        $value = mb_convert_kana($value,"s");
        $value = preg_replace('/\s+/', '', $value);
        $this->_setValue($value);
        if (mb_ereg('^[｡-ﾟ]+$', $value)) {
            return TRUE;
        } else {
            $this->_error(self::TEXT);
            return FALSE;
        }
    }
}