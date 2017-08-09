<?php
class LangPack {
    private static $message_array = array(
        'ja' => array (
            '2' => 'パラメータエラーです。',
            '3' => 'システムエラー',
            '4' => 'システムエラー',
            '5' => 'システムエラー',
            '6' => 'システムエラー',
            '7' => 'システムエラー',
            '8' => 'システムエラー',
            '12' => 'システムエラー',
            '13' => 'システムエラー',
            '14' => 'システムエラー',
            '-1' => 'システムエラー',
            '-2' => 'システムエラー',
            '-99' => 'システムエラー',
            '101' => 'パスワード再発行の申請情報がありません。',
            '102' => 'パスワード再発行の有効期限が過ぎました。',
            '103' => 'パスワードは既に再発行されました。<br>アプリからのパスワード再発行をもう一度行って下さい。',
            '104' => '再発行されたパスワードは<b><font color=\'red\'>%_NEW_PW_%</font></b>です。<br>この再発行結果の表示は１回のみになりますのでご注意下さい。',
            '105' => 'キッズアップサービスの承認が正常に完了し、ご利用可能になりました。<br>アプリから再ログインして下さい。',
            '106' => '既に承認済でご利用出来ます。',
            '107' => '承認が拒否されております。',
        ),
        'en' => array (
            '2' => 'parameter error',
            '3' => 'SystemError',
            '4' => 'SystemError',
            '5' => 'SystemError',
            '6' => 'SystemError',
            '7' => 'SystemError',
            '8' => 'SystemError',
            '12' => 'SystemError',
            '13' => 'SystemError',
            '14' => 'SystemError',
            '-1' => 'SystemError',
            '-2' => 'SystemError',
            '-99' => 'SystemError',
            '101' => 'There is no request information of password re-issued.',
            '102' => 'Expiration of password re-issue has passed.',
            '103' => 'Password has already been re-issued.<br>Make the password re-issue from the app again.',
            '104' => 'Re-issued password is <b><font color=\'red\'>%_NEW_PW_%</font></b><br>Please note that this reissue results displayed will be only once.',
            '105' => 'Approval of the KidsUp service is successfully completed, it is now available.<br>Please re-login at the KidsUp mobile application.',
            '106' => 'It is already available. We would like you to enjoy KidsUp service.',
            '107' => 'Approval is refused.',
        ),
        'ko' => array (
            '2' => '파라미터 에러입니다.',
            '3' => '시스템에러',
            '4' => '시스템에러',
            '5' => '시스템에러',
            '6' => '시스템에러',
            '7' => '시스템에러',
            '8' => '시스템에러',
            '12' => '시스템에러',
            '13' => '시스템에러',
            '14' => '시스템에러',
            '-1' => '시스템에러',
            '-2' => '시스템에러',
            '-99' => '시스템에러',
            '101' => '비밀번호 재발행 정보가 없습니다.',
            '102' => '비밀번호 재발행 유효기간이 지났습니다.',
            '103' => '이미 비밀번호가 재발행되었습니다.<br>앱에서 비밀번호 재발행을 신청하신후 다시 이용해 주십시오.',
            '104' => '재발행된 비밀번호는 <b><font color=\'red\'>%_NEW_PW_%</font></b>입니다.<br>본 비밀번호 재발행 페이지는 1회에 한해 확인할 수 있으니 주의 바랍니다.',
            '105' => '키즈업 서비스의 인증이 정상적으로 완료되었습니다.<br>앱에서 다시 로그인하면 서비스를 이용할 수 있습니다.',
            '106' => '이미 승인완료되어있으므로 서비스를 정상적으로 이용가능합니다.',
            '107' => '승인이 거부되었습니다.',
        ),
    );

    public static function getMessage($locale, $status){
        if(array_key_exists($locale, self::$message_array)){
            if(array_key_exists($status, self::$message_array[$locale])){
                return self::$message_array[$locale][$status];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }
}