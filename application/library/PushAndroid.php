<?php

class PushAndroid {
	private $url = 'https://android.googleapis.com/gcm/send';

	public function send($token, $alert, $thread_type, $thread_sub_type, $thread_id){
		$logger = new Logger(__CLASS__, __FUNCTION__);
        $fields = array(
            'registration_ids' => array($token),
            'data' => array(
                'alert' => $alert,
                'custom' => array(
                    'thread_type' => $thread_type,
                    'thread_subtype' => $thread_sub_type,
                    'thread_id' => $thread_id
                )
            ),
        );
 
        $headers = array(
            'Authorization: key='.Constant::GOOGLE_API_ACCESS_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields)); 
        $result = curl_exec($ch);
        if ($result === FALSE) {
            $logger->error('Curl failed: ' . curl_error($ch));
        }else{
            $logger->info('OK');
        }
        curl_close($ch);

        return $result;
	}
}