<?php
class AnswerCurlTools
{

    /**
     * http请求入口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlRequest($url, $data = [], $method, $timeout = 30)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'http';
        $isHttp = strtolower($scheme) == 'https' ? true : false;

        $method = strtoupper($method);

        switch ($method) {
            case 'GET':
                return self::curlGetRequest($url, $data, 'GET', $isHttp, $timeout);
            case 'POST':
                return self::curlPostRequest($url, $data, 'POST', $isHttp, $timeout);
            case 'PATCH':
                return self::curlPatchRequest($url, $data, 'PATCH', $isHttp, $timeout);
            case 'PUT':
                return self::curlPutRequest($url, $data, 'PUT', $isHttp, $timeout);
            case 'DELETE':
                return self::curlDeleteRequest($url, $data, 'DELETE', $isHttp, $timeout);
            default:
                return false;
        }
    }

    /**
     * GET请求接口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [boolean]        $isHttps   [是否https]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlGetRequest($url, $data = [], $method = "GET", $isHttps = false, $timeout = 30)
    {
        //初始化
        $ch = curl_init();
        // 设置请求url地址
        curl_setopt($ch, CURLOPT_URL, $url);
        //去除响应头和行
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //不直接输出结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间 单位：秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //不检查ssl证书
        if ($isHttps) {
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        // 返回值
        return $data;
    }

    /**
     * POST请求接口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [boolean]        $isHttps   [是否https]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlPostRequest($url, $data = [], $method = "POST", $isHttps = false, $timeout = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if (strtoupper($method) == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        if ($isHttps) {
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //\Log::error('Curl error:' . curl_error($ch));
            return false;
        }
        curl_close($ch);

        return $result;
    }

    /**
     * PATCH请求接口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [boolean]        $isHttps   [是否https]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlPatchRequest($url, $data = [], $method = "PATCH", $isHttps = false, $timeout = 30)
    {
        //$data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type:application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($isHttps) {
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            //\Log::error('Curl error:' . curl_error($ch));
            return false;
        }
        curl_close($ch);

        return $result;

    }

    /**
     * PUT请求接口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [boolean]        $isHttps   [是否https]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlPutRequest($url, $data = [], $method = "PUT", $isHttps = false, $timeout = 30)
    {
        // 创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间设置
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type:application/json"]);
        //设置请求方式和提交的字符串
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($isHttps) {
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            //\Log::error('Curl error:' . curl_error($ch));
            return false;
        }

        curl_close($ch);

        return $result;
    }

    /**
     * DELETE请求接口
     *
     * @param    [String]         $url       [请求地址]
     * @param    [String]         $method    [请求方法]
     * @param    [array]          $data      [请求数据]
     * @param    [boolean]        $isHttps   [是否https]
     * @param    [timeout]        $timeout   [超时时间]
     * @return   [mixed]
     */
    public static function curlDeleteRequest($url, $data = [], $method = "DELETE", $isHttps = false, $timeout = 30)
    {
        // 创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间设置
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type:application/json"]);
        //设置请求方式和提交的字符串
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($isHttps) {
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            //\Log::error('Curl error:' . curl_error($ch));
            return false;
        }

        curl_close($ch);

        return $result;
    }


}