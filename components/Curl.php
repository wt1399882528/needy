<?php
/**
 * curl
 */
namespace app\components;

use Yii;
use yii\base\Component;

class Curl
{
    /**
     * GET请求接口
     *
     * @param $url string url
     * @param $timeout int 超时时间/秒
     *
     * @throws \Exception
     *
     * @return false|array
     */
    public static function get($url, $cookie = '', $timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $cookie && curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        \Yii::info("curl get url: {$url}");
        \Yii::info("curl get cookie: {$cookie}");
        return self::handler($curl, $url);
    }

    /**
     * POST请求接口
     *
     * @param $url    string       url
     * @param $data   array|string post数据
     * @param $header array        header
     * @param $timeout int 超时时间/秒
     *
     * @throws \Exception
     *
     * @return false|array
     */
    public static function post($url, $data, $header = array(), $cookie = "", $timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if (is_array($data)) {
            $_data = array();
            foreach ($data as $k => $v) {
                $_data[] = "$k=" . urlencode($v);
            }
            $data = join('&', $_data);
        }
        $cookie && curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        \Yii::info("curl post url: {$url}");
        \Yii::info("curl post data: {$data}");
        \Yii::info("curl post header: " . var_export($header, true));
        \Yii::info("curl post cookie: {$cookie}");
        return self::handler($curl, $url);
    }

    /**
     * @param $curl
     * @param $url
     * @return bool|mixed
     * @throws \Exception
     */
    protected static function handler($curl, $url)
    {
        $cache_id = md5($url);
        $begin = microtime(true);
        try {
            $data = curl_exec($curl);
            if ($data === false) {
                $error_msg = curl_error($curl) ?: self::$error_codes[curl_errno($curl)] ?: '未知错误';
                throw new \Exception($error_msg, curl_errno($curl));
            }
            $info = curl_getinfo($curl);
            if ($info['http_code'] != 200) {
                throw new \Exception('http_code:' . $info['http_code'], $info['http_code']);
            }
        } catch (\Exception $e) {
            $data = false;
            \Yii::warning(sprintf('%s %s %s', 'interface fail:', $e->getMessage(), $url));
            throw $e;
            /* $data = \Yii::$app->cache->get($cache_id);
             \Yii::info("memcache get id: {$cache_id}");
             
             if (empty($data)) {
             throw $e;
             } else {
             return $data;
             } */
        }
        \Yii::info('curl time:' . (microtime(true) - $begin));
        \Yii::info('curl result:' . json_encode($data));
        \Yii::$app->cache->set($cache_id, $data, 0);
        curl_close($curl);
        return $data;
    }

    /**
     * @var array
     */
    protected static $error_codes = array(
        1 => 'CURLE_UNSUPPORTED_PROTOCOL',
        2 => 'CURLE_FAILED_INIT',
        3 => 'CURLE_URL_MALFORMAT',
        4 => 'CURLE_URL_MALFORMAT_USER',
        5 => 'CURLE_COULDNT_RESOLVE_PROXY',
        6 => 'CURLE_COULDNT_RESOLVE_HOST',
        7 => 'CURLE_COULDNT_CONNECT',
        8 => 'CURLE_FTP_WEIRD_SERVER_REPLY',
        9 => 'CURLE_REMOTE_ACCESS_DENIED',
        11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
        13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
        14 => 'CURLE_FTP_WEIRD_227_FORMAT',
        15 => 'CURLE_FTP_CANT_GET_HOST',
        17 => 'CURLE_FTP_COULDNT_SET_TYPE',
        18 => 'CURLE_PARTIAL_FILE',
        19 => 'CURLE_FTP_COULDNT_RETR_FILE',
        21 => 'CURLE_QUOTE_ERROR',
        22 => 'CURLE_HTTP_RETURNED_ERROR',
        23 => 'CURLE_WRITE_ERROR',
        25 => 'CURLE_UPLOAD_FAILED',
        26 => 'CURLE_READ_ERROR',
        27 => 'CURLE_OUT_OF_MEMORY',
        28 => 'CURLE_OPERATION_TIMEDOUT',
        30 => 'CURLE_FTP_PORT_FAILED',
        31 => 'CURLE_FTP_COULDNT_USE_REST',
        33 => 'CURLE_RANGE_ERROR',
        34 => 'CURLE_HTTP_POST_ERROR',
        35 => 'CURLE_SSL_CONNECT_ERROR',
        36 => 'CURLE_BAD_DOWNLOAD_RESUME',
        37 => 'CURLE_FILE_COULDNT_READ_FILE',
        38 => 'CURLE_LDAP_CANNOT_BIND',
        39 => 'CURLE_LDAP_SEARCH_FAILED',
        41 => 'CURLE_FUNCTION_NOT_FOUND',
        42 => 'CURLE_ABORTED_BY_CALLBACK',
        43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
        45 => 'CURLE_INTERFACE_FAILED',
        47 => 'CURLE_TOO_MANY_REDIRECTS',
        48 => 'CURLE_UNKNOWN_TELNET_OPTION',
        49 => 'CURLE_TELNET_OPTION_SYNTAX',
        51 => 'CURLE_PEER_FAILED_VERIFICATION',
        52 => 'CURLE_GOT_NOTHING',
        53 => 'CURLE_SSL_ENGINE_NOTFOUND',
        54 => 'CURLE_SSL_ENGINE_SETFAILED',
        55 => 'CURLE_SEND_ERROR',
        56 => 'CURLE_RECV_ERROR',
        58 => 'CURLE_SSL_CERTPROBLEM',
        59 => 'CURLE_SSL_CIPHER',
        60 => 'CURLE_SSL_CACERT',
        61 => 'CURLE_BAD_CONTENT_ENCODING',
        62 => 'CURLE_LDAP_INVALID_URL',
        63 => 'CURLE_FILESIZE_EXCEEDED',
        64 => 'CURLE_USE_SSL_FAILED',
        65 => 'CURLE_SEND_FAIL_REWIND',
        66 => 'CURLE_SSL_ENGINE_INITFAILED',
        67 => 'CURLE_LOGIN_DENIED',
        68 => 'CURLE_TFTP_NOTFOUND',
        69 => 'CURLE_TFTP_PERM',
        70 => 'CURLE_REMOTE_DISK_FULL',
        71 => 'CURLE_TFTP_ILLEGAL',
        72 => 'CURLE_TFTP_UNKNOWNID',
        73 => 'CURLE_REMOTE_FILE_EXISTS',
        74 => 'CURLE_TFTP_NOSUCHUSER',
        75 => 'CURLE_CONV_FAILED',
        76 => 'CURLE_CONV_REQD',
        77 => 'CURLE_SSL_CACERT_BADFILE',
        78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
        79 => 'CURLE_SSH',
        80 => 'CURLE_SSL_SHUTDOWN_FAILED',
        81 => 'CURLE_AGAIN',
        82 => 'CURLE_SSL_CRL_BADFILE',
        83 => 'CURLE_SSL_ISSUER_ERROR',
        84 => 'CURLE_FTP_PRET_FAILED',
        84 => 'CURLE_FTP_PRET_FAILED',
        85 => 'CURLE_RTSP_CSEQ_ERROR',
        86 => 'CURLE_RTSP_SESSION_ERROR',
        87 => 'CURLE_FTP_BAD_FILE_LIST',
        88 => 'CURLE_CHUNK_FAILED'
    );
}
