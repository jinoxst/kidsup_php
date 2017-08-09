<?php

require_once 'Zend/Validate/Abstract.php';

class CrmWebService_Regex_Util_BIRTHYEAR extends Zend_Validate_Abstract {
    const TEXT = 'text';

    protected $_messageTemplates = array(
        self::TEXT => "'%value%' is not birthday format(yyyy)"
    );

    public function isValid($value){
        $this->_setValue($value);
        if ($value >= '1900') {
            return TRUE;
        } else {
            $this->_error(self::TEXT);
            return FALSE;
        }
    }
}