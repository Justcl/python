<?php

/**
 * Class Http
 * 请求url类
 */
class Http {
    /**
     * @param $url
     * @param int $requestType 1 post 其他 get
     * @param array $dataArr
     * @param int $timeout
     * @param array $header
     * @return array|bool
     */
    static public function request($url, $requestType = 0, $dataArr = [], $timeout = 5, $header = []) {
        if (empty($url)) {
            return false;
        }
        if ($requestType == 1) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataArr);
        } else {
            if (!empty($dataArr)) {
                $url = $url . '?' . http_build_query($dataArr);
            }
            $ch = curl_init($url);
        }
        if (strpos($url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($timeout > 5) $timeout = 5;
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        $return = [];
        $return['httpCode'] = $info['http_code'];
        $return['errCode'] = curl_errno($ch);
        $return['errMsg'] = curl_error($ch);
        if ($data !== false) {
            $return['data'] = $data;
        }
        curl_close($ch);
        return $return;
    }


    /**
     * @param $urls
     * @param int $requestType
     * @param int $timeout
     * @param array $header
     * @return array|bool
     */
    public static function multiRequest($urls, $requestType = 1, $timeout = 10, $header = []) {
        if (!is_array($urls)) {
            return false;
        }

        $mh = curl_multi_init();
        $curl_array = [];
        foreach ($urls as $name => $value) {
            if (isset($value['url']) && isset($value['data']) && is_array($value['data'])) {
                if ($requestType == 1) {
                    $curl_array[$name] = curl_init();
                    curl_setopt($curl_array[$name], CURLOPT_URL, $value['url']);
                    curl_setopt($curl_array[$name], CURLOPT_POST, 1);
                    curl_setopt($curl_array[$name], CURLOPT_POSTFIELDS, $value['data']);
                } else {
                    if (!empty($value['data'])) {
                        $url = $value['url'] . '?' . http_build_query($value['data']);
                    } else {
                        $url = $value['url'];
                    }
                    $curl_array[$name] = curl_init($url);
                }
                curl_setopt($curl_array[$name], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_array[$name], CURLOPT_HTTPHEADER, $header);
                if ($timeout > 10) $timeout = 10;
                curl_setopt($curl_array[$name], CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($curl_array[$name], CURLOPT_TIMEOUT, $timeout);
                curl_multi_add_handle($mh, $curl_array[$name]);
            }

        }

        $running = NULL;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res =  [];
        foreach ($urls as $name => $value) {
            $tmpData = curl_multi_getcontent($curl_array[$name]);
            $tmpInfo = curl_getinfo($curl_array[$name]);
            $tmpRes = [];
            $tmpRes['httpCode'] = $tmpInfo['http_code'];
            $tmpRes['errNo'] = curl_errno($curl_array[$name]);
            $tmpRes['errMsg'] = curl_error($curl_array[$name]);
            if ($tmpData !== false) {
                $tmpRes['data'] = $tmpData;
            }
            $res[$name] = $tmpRes;
            curl_close($curl_array[$name]);
            curl_multi_remove_handle($mh, $curl_array[$name]);
        }
        curl_multi_close($mh);
        return $res;
    }
}
