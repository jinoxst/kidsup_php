<?php
require_once APPLICATION_PATH.'/library/Constant.php';
class Crypto {
	private function hex2bin($hex_string) {
        return pack('H*', $hex_string);
    }

    public function encrypt($data, $key=Constant::CRYPTO_KEY, $iv=Constant::CRYPTO_IV) {
        $blocksize = 16;// AES-128
        $pad = $blocksize - (strlen($data) % $blocksize);
        $data = $data . str_repeat(chr($pad), $pad);
        return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv));
    }

    public function decrypt($data, $key=Constant::CRYPTO_KEY, $iv=Constant::CRYPTO_IV) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $this->hex2bin($data), MCRYPT_MODE_CBC, $iv));
    }
}