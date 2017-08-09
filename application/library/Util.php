<?php
require_once APPLICATION_PATH.'/library/Constant.php';

class Util {
    public static function getPagenator($groupno, $totalcnt, $category='common'){
        $result = array();
        if($category == 'album'){
            $maxPageno = ceil($totalcnt / Constant::ALBUMPAGESIZE);
        }else{
            $maxPageno = ceil($totalcnt / Constant::PAGESIZE);
        }
        $maxGroupno = ceil($maxPageno / Constant::GROUPSIZE);
        $pagenoArr = array();
        for($i=1;$i<=$maxPageno;$i++){
            if(ceil($i / Constant::GROUPSIZE) == $groupno){
                array_push($pagenoArr, $i);
            }
        }
        $result['pagenoArr'] = $pagenoArr;
        $result['firstPageFlag'] = '0';
        $result['preGroupFlag'] = '0';
        $result['lastPageFlag'] = '0';
        $result['nextGroupFlag'] = '0';
        if($groupno > 1){
            $result['firstPageFlag'] = '1';
            $result['preGroupFlag'] = '1';
        }
        if($groupno < $maxGroupno){
            $result['lastPageFlag'] = '1';
            $result['nextGroupFlag'] = '1';
        }

        return $result;
    }
    public static function getReplyPagenator($pageno, $totalcnt){
        $result = array('show' => '0');
        if($totalcnt > Constant::PAGESIZE){
            $result['show'] = '1';
        }
        $maxReplyCnt = $pageno * Constant::PAGESIZE;
        if($totalcnt > $maxReplyCnt){
            $result['nextGroupFlag'] = '1';
            $result['preGroupFlag'] = '0';
        }else{
            $result['nextGroupFlag'] = '0';
            $result['preGroupFlag'] = '1';
        }
        return $result;
    }
    public static function getGroupno($pageno){
        return ceil($pageno / Constant::GROUPSIZE);
    }
    public static function getPageno($groupno, $totalcnt){
        $pageno = ($groupno - 1) * Constant::GROUPSIZE + 1;
        if($pageno > $totalcnt){
            $pageno = $totalcnt;
        }
        return $pageno;
    }
    public static function getLastPageno($totalcnt, $category='common'){
        if($category == 'album'){
            return $maxPageno = ceil($totalcnt / Constant::ALBUMPAGESIZE);
        }else{
            return $maxPageno = ceil($totalcnt / Constant::PAGESIZE);
        }
    }
    public static function getLastGroupno($maxPageno){
        return ceil($maxPageno / Constant::GROUPSIZE);
    }
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
    /**
     * 文字コードをUTF-8からSJISに変更
     * @param unknown_type $val
     */
    public static function convertUTF8_SJIS($val){
        return mb_convert_encoding($val, 'SJIS', 'UTF-8');
    }

   /**
     * 文字コードをUTF-8からSJIS-winに変更
     * @param unknown_type $val
     */
    public static function convertUTF8_SJISwin($val){
        return mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
    }

    /**
     * 文字コードをSJISからUTF-8に変更
     * @param unknown_type $val
     */
    public static function convertSJIS_UTF8($val){
        return mb_convert_encoding($val, 'UTF-8', 'SJIS');
    }

    /**
     * 文字コードをSJIS-winからUTF-8に変更
     * @param unknown_type $val
     */
    public static function convertSJISwin_UTF8($val){
        return mb_convert_encoding($val, 'UTF-8', 'SJIS-win');
    }

    public static function convertIBM_EXT_STR($org){
        $tmp = self::convertSJISwin_UTF8($org);
        //$ibm1 = array('髙','﨑','桒');
        //$ibm2 = array('高','崎','桑');
        $ibm1 = array('髙','﨑');
        $ibm2 = array('高','崎');
        for($i=0;$i<count($ibm1);$i++){
            $tmp = str_replace($ibm1[$i],$ibm2[$i],$tmp);
        }

        return $tmp;
    }

    public static function xmlToArray($xml, &$return, $path='', $root=false)
    {
        $children = array();
        if ($xml instanceof SimpleXMLElement) {
            $children = $xml->children();
            if ($root){
                $path .= '/'.$xml->getName();
            }
        }
        if ( count($children) == 0 ){
            $return[$path] = (string)$xml;
            return;
        }
        $seen = array();
        foreach ($children as $child => $value) {
            $childname = ($child instanceof SimpleXMLElement)?$child->getName():$child;
            /*if ( !isset($seen[$childname])){
                $seen[$childname] = 0;
            }
            $seen[$childname]++;
            XMLToArrayFlat($value, $return, $path.'/'.$child.'['.$seen[$childname].']');*/
            Util::xmlToArray($value, $return, $child);
        }
    }

    public static function arrToStr( $glue, $pieces )
    {
        if( is_array( $pieces ) )
        {
            foreach( $pieces as $key => $r_pieces )
            {
                if( is_array( $r_pieces ) )
                {
                    $retVal[] = Util::arrToStr( $glue, $r_pieces );
                }
                else
                {
                    $retVal[] = "[$key]=".$r_pieces;
                }
            }
        }
        return implode( $glue, $retVal );
    }

    public static function chg5CStr($org, $utf8=true){
        if($utf8){
            for($i=0;$i<count($org);$i++){
                $org[$i] = self::convertSJIS_UTF8($org[$i]);
            }
        }
        $src = array('表','予','能','申','ソ','十','構','暴','圭','貼',
                     '噂','浬','欺','蚕','曾','箪','禄','兔','喀','媾',
                     '―' ,'彌','拿','杤','歃','濬','畚','秉','綵','臀',
                     '藹','觸','軆','鐔','饅','鷭');
        for($i=0;$i<count($org);$i++){
            $lastStr = mb_substr($org[$i], -1);
            for($j=0;$j<count($src);$j++){
                if($lastStr == $src[$j]){
                    $org[$i] = $org[$i].' ';
                }
            }
            $org[$i] = self::convertUTF8_SJIS($org[$i]);
        }

        return $org;
    }

    public static function chg5CStr_win($org, $utf8=true){
        if($utf8){
            for($i=0;$i<count($org);$i++){
                $org[$i] = self::convertSJISwin_UTF8($org[$i]);
            }
        }
        $src = array('表','予','能','申','ソ','十','構','暴','圭','貼',
                     '噂','浬','欺','蚕','曾','箪','禄','兔','喀','媾',
                     '―' ,'彌','拿','杤','歃','濬','畚','秉','綵','臀',
                     '藹','觸','軆','鐔','饅','鷭');
        for($i=0;$i<count($org);$i++){
            $lastStr = mb_substr($org[$i], -1);
            for($j=0;$j<count($src);$j++){
                if($lastStr == $src[$j]){
                    $org[$i] = $org[$i].' ';
                }
            }
            $org[$i] = self::convertUTF8_SJISwin($org[$i]);
        }

        return $org;
    }

    public static function chgDateFormat($date){
        return substr($date,0,4).'年'.substr($date,4,2).'月'.substr($date,6,2).'日';
    }

    public static function getDayOfWeekFromDate($date){
        $datetime = new DateTime($date);
        $week = array("日", "月", "火", "水", "木", "金", "土");
        $w = (int)$datetime->format('w');
        return $week[$w];
    }

    public static function getOrgImageWidth($w, $h){
        if($w > $h){
            return $w > Constant::ORG_IMG_WIDTH ? round(Constant::ORG_IMG_WIDTH * $w / $h) : $w;
        }else{
            return $w > Constant::ORG_IMG_WIDTH ? Constant::ORG_IMG_WIDTH : $w;
        }
    }

    public static function getChgImageWidth($w, $h){
        if($w > $h){
            return $w > Constant::CHG_IMG_WIDTH ? round(Constant::CHG_IMG_WIDTH * $w / $h) : $w;
        }else{
            return $w > Constant::CHG_IMG_WIDTH ? Constant::CHG_IMG_WIDTH : $w;
        }
    }

    public static function getThmImageWidth($w, $h){
        if($w > $h){
            return $w > Constant::THM_IMG_WIDTH ? round(Constant::THM_IMG_WIDTH * $w / $h) : $w;
        }else{
            return $w > Constant::THM_IMG_WIDTH ? Constant::THM_IMG_WIDTH : $w;
        }
    }

    public static function rmDir($dir){
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    public static function mkDir($dir, $permission=0777, $recursive=true){
        if (!file_exists($dir)) {
            mkdir($dir, $permission, $recursive);
        }
    }

    public static function getClientIp($checkProxy = true)
    {
        if ($checkProxy && self::getServer('HTTP_CLIENT_IP') != null) {
            $ip = self::getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && self::getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = self::getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = self::getServer('REMOTE_ADDR');
        }

        return $ip;
    }

    public static function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    public static function escapeJsonChar($v){
        $result = str_replace("\\n", "\\\\n", $v);
        $result = str_replace("'", "&#39;", $result);
        $result = str_replace('"', '\"', $result);
        return $result;
    }

    public static function getCalendarFirstLastDay($ym){
        $y = substr($ym, 0, 4);
        $m = substr($ym, 4, 6);
        $calendar = Calendar::factory($m, $y);
        $cnt = 1;
        $d1 = 0;
        $d2 = 0;
        $first_day = '';
        $last_day = '';
        $year = '';
        $month = '';
        $day = '';
        foreach ($calendar->weeks() as $week){
            foreach ($week as $day){
                if($cnt == 1){
                    $d1 = $day[0];
                }
                if($cnt == 35){
                    $d2 = $day[0];
                }
                $cnt++;
            }
        }
        if($d1 > 1){
            $month = (substr($m,0,1) == '0') ? intval(substr($m,1,2)) : intval($m);
            if($month == 1){
                $year = intval($y) - 1;
                $month = 12;
            }else{
                $month = $month - 1;
                $year = $y;
            }
            $month = ($month < 10) ? '0'.$month : $month;
            $first_day = $year.''.$month.''.$d1;
        }else{
            $first_day = $y.''.$m.'01';
        }
        if($d2 == 28 || $d2 == 29 || $d2 == 30 || $d2 == 31){
            $last_day = $y.''.$m.''.$d2;
        }else{
            $month = (substr($m,0,1) == '0') ? intval(substr($m,1,2)) : intval($m);
            if($month == 12){
                $year = intval($y) + 1;
                $month = 1;
            }else{
                $month = $month + 1;
                $year = $y;
            }
            $month = ($month < 10) ? '0'.$month : $month;
            $day = ($d2 < 10) ? '0'.$d2 : $d2;
            $last_day = $year.''.$month.''.$day;
        }
        return array($first_day, $last_day);
    }

    public static function encrypt_decrypt($action, $string) {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = '5A13249346AA6054';
        $secret_iv = '2C314D1AF5B81CB9';

        // hash
        $key = hash('sha256', $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }
}