<?php


class Curl {
    public static function send($url) {
        $curlObject = curl_init();
        curl_setopt($curlObject, CURLOPT_URL, $url);
        curl_setopt($curlObject, CURLOPT_PORT, 5000);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curlObject);

        if (curl_error($curlObject)) {
            if (isset($_REQUEST['debug'])) {
                var_dump(curl_error($curlObject));
            }
            return false;
        }
        
        curl_close($curlObject);
        return $output;
    }
}