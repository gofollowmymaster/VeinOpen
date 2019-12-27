<?php

use app\common\service\AuthService;
use think\Db;
use think\facade\Log;

/**
 * 打印输出数据到文件
 * @param mixed       $data 输出的数据
 * @param bool        $force 强制替换
 * @param string|null $file
 */
function p($data, $force = false, $file = null) {
    is_null($file) && $file = env('runtime_path') . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data :
            (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
    $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node) {
    return AuthService::checkAuthNode($node);
}


/**
 * 日期格式标准输出
 * @param string $datetime 输入日期
 * @param string $format 输出格式
 * @return false|string
 */
function format_datetime($datetime, $format = 'Y年m月d日 H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string) {
    list($chars, $length) = ['', strlen($string = iconv('utf-8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string) {
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * 下载远程文件到本地
 * @param string $url 远程图片地址
 * @return string
 */
function local_image($url) {
    return \service\FileService::download($url)['url'];
}

/**
 * Cors Options 授权处理
 */
function corsOptionsHandler() {
    if (PHP_SESSION_ACTIVE !== session_status())
        Session::init(config('session.'));
    try {
        $token = request()->header('token', input('token', ''));
        list($name, $value) = explode('=', decode($token) . '=');
        if (!empty($value) && session_name() === $name)
            session_id($value);
    } catch (\Exception $e) {
    }
    if (request()->isOptions()) {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
        header("Access-Control-Allow-Headers:Accept,Referer,Host,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Cookie,token");
        header('Content-Type:text/plain charset=utf-8');
        header('Access-Control-Max-Age:1728000');
        header('HTTP/1.0 204 No Content');
        header('Content-Length:0');
        header('status:204');
        exit;
    }
}

/**
 * Cors Request Header信息
 * @return array
 */
function corsRequestHander() {
    return ['Access-Control-Allow-Origin'  => request()->header('origin', '*'),
            'Access-Control-Allow-Methods' => 'GET,POST,OPTIONS', 'Access-Control-Allow-Credentials' => "true",];
}


/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool   $value 默认是null为获取值，否则为更新
 * @return string|bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function sysconf($name, $value = null) {
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}


/**
 * 返回成功的操作
 * @param mixed   $msg 消息内容
 * @param array   $data 返回数据
 * @param integer $code 返回代码
 */
function success($msg, $data = [], $code = 1) {
    $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'token' => encode(session_name() . '=' . session_id())];
    throw new HttpResponseException(Response::create($result, 'json', 200, corsRequestHander()));
}

/**
 * 返回失败的请求
 * @param mixed   $msg 消息内容
 * @param array   $data 返回数据
 * @param integer $code 返回代码
 */
function error($msg, $data = [], $code = 0) {
    $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'token' => encode(session_name() . '=' . session_id())];
    throw new HttpResponseException(Response::create($result, 'json', 200, corsRequestHander()));
}



/**
 * Emoji原形转换为String
 * @param string $content
 * @return string
 */
function emojiEncode($content) {
    return json_decode(preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
        return addslashes($str[0]);
    }, json_encode($content)));
}

/**
 * Emoji字符串转换为原形
 * @param string $content
 * @return string
 */
function emojiDecode($content) {
    return json_decode(preg_replace_callback('/\\\\\\\\/i', function () {
        return '\\';
    }, json_encode($content)));
}

/**
 * 一维数据数组生成数据树
 * @param array  $list 数据列表
 * @param string $id 父ID Key
 * @param string $pid ID Key
 * @param string $son 定义子数据Key
 * @return array
 */
function arr2tree($list, $id = 'id', $pid = 'pid', $son = 'sub') {
    list($tree, $map) = [[], []];
    foreach ($list as $item)
        $map[$item[$id]] = $item;
    foreach ($list as $item)
        if (isset($item[$pid]) && isset($map[$item[$pid]])) {
            $map[$item[$pid]][$son][] = &$map[$item[$id]];
        } else $tree[] = &$map[$item[$id]];
    unset($map);
    return $tree;
}

/**
 * 一维数据数组生成数据树
 * @param array  $list 数据列表
 * @param string $id ID Key
 * @param string $pid 父ID Key
 * @param string $path
 * @param string $ppath
 * @return array
 */
function arr2tablebak(array $list, $id = 'id', $pid = 'pid', $path = 'path', $ppath = '') {
    $tree = [];
    foreach (arr2tree($list, $id, $pid) as $attr) {
        $attr[$path] = "{$ppath}-{$attr[$id]}";
        $attr['sub'] = isset($attr['sub']) ? $attr['sub'] : [];
        $attr['spt'] = substr_count($ppath, '-');
        $attr['spl'] = str_repeat("&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;", $attr['spt']);
        $sub = $attr['sub'];
        unset($attr['sub']);
        $tree[] = $attr;
        if (!empty($sub))
            $tree = array_merge($tree, arr2table($sub, $id, $pid, $path, $attr[$path]));
    }
    return $tree;
}

/**
 * 一维数据数组生成数据树
 * @param array  $list 数据列表
 * @param string $id ID Key
 * @param string $pid 父ID Key
 * @param string $path
 * @param string $ppath
 * @return array
 */
function arr2table(array $list, $id = 'id', $pid = 'pid', $path = 'path', $ppath = '') {
    $tree = [];
    foreach (arr2tree($list, $id, $pid) as $attr) {
        $attr[$path] = "{$ppath}-{$attr[$id]}";
        $attr['sub'] = isset($attr['sub']) ? $attr['sub'] : [];
        $attr['spt'] = substr_count($ppath, '-');
        $attr['spl'] = str_repeat("&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;", $attr['spt']);
        $sub = $attr['sub'];
        unset($attr['sub']);
//        unset($attr['spt']);
        $tree[] = $attr;
        if (!empty($sub))
            $tree = array_merge($tree, arr2table($sub, $id, $pid, $path, $attr[$path]));
    }
    return $tree;
}

/**
 * 获取数据树子ID
 * @param array  $list 数据列表
 * @param int    $id 起始ID
 * @param string $key 子Key
 * @param string $pkey 父Key
 * @return array
 */
function getArrSubIds($list, $id = 0, $key = 'id', $pkey = 'pid') {
    $ids = [intval($id)];
    foreach ($list as $vo)
        if (intval($vo[$pkey]) > 0 && intval($vo[$pkey]) === intval($id)) {
            $ids = array_merge($ids, getArrSubIds($list, intval($vo[$key]), $key, $pkey));
        }
    return $ids;
}

/**
 * 写入CSV文件头部
 * @param string $filename 导出文件
 * @param array  $headers CSV 头部(一级数组)
 */
function setCsvHeader($filename, array $headers) {
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=" . iconv('utf-8', 'gbk//TRANSLIT', $filename));
    echo @iconv('utf-8', 'gbk//TRANSLIT', "\"" . implode('","', $headers) . "\"\n");
}

/**
 * 写入CSV文件内容
 * @param array $list 数据列表(二维数组或多维数组)
 * @param array $rules 数据规则(一维数组)
 */
function setCsvBody(array $list, array $rules) {
    foreach ($list as $data) {
        $rows = [];
        foreach ($rules as $rule) {
            $item = parseKeyDot($data, $rule);
            $rows[] = $item === $data ? '' : $item;
        }
        echo @iconv('utf-8', 'gbk//TRANSLIT', "\"" . implode('","', $rows) . "\"\n");
        flush();
    }
}

/**
 * 根据数组key查询(可带点规则)
 * @param array  $data 数据
 * @param string $rule 规则，如: order.order_no
 * @return mixed
 */
function parseKeyDot(array $data, $rule) {
    list($temp, $attr) = [$data, explode('.', trim($rule, '.'))];
    while ($key = array_shift($attr))
        $temp = isset($temp[$key]) ? $temp[$key] : $temp;
    return (is_string($temp) || is_numeric($temp)) ? str_replace('"', '""', "\t{$temp}") : '';
}

/**
 * 获取redis连接
 * @return object
 */
function redis(array $config=null) {
    $config = $config?:config('redis.');
    return \app\common\tool\RedisClient::getInstance($config)->getRedis();
}

/**
 * @param $message
 * @throws Exception
 */
function report(string $content) {
    try {
        $config = \think\Container::get('app')->config('reporter.');
        $message=[];
        $message['time']=date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $message['uri']=$_SERVER['REQUEST_URI'];
        $message['error']=$content;
        $message['ip'] = getRealIp();
        $message['serverIp'] = gethostbyname(gethostname());
        $message['project']='veinopen';
        $message['info']='通知'.json_encode(requestInfo());

        \app\common\tool\Reporter::getInstance($config)->Report($message);
    } catch (\Throwable $e) {
        Log::error('report失败:内容='.$content);
    }
}

/**
 * 事件触发
 * @param $event
 */
function triggerEvent(\app\common\event\events\Event $event) {
    app\common\event\EventDispatcher::trigger($event);
}

/**
 * 获取对象类名
 * @param $object
 * @return string
 */
function getClass($object) {
    $object = is_object($object) ? get_class($object) : $object;
    return (string)$object;
}

function exceptionToArray(\Exception $exception) {
    return ['file'    => $exception->getFile(), 'line' => $exception->getLine(), 'code' => $exception->getCode(),
            'message' => $exception->getMessage()];
}

function isEmptyInDb(array $data, string $message) {
    if (empty($data)) {
        $sql = Db::getLastSql();
        throw new \think\db\exception\DataNotFoundException($message . ":\nSQL:" . $sql);
    }
}

function isModelFailed($res, string $message) {
    if ($res === false) {
        $sql = Db::getLastSql();
        throw new \think\exception\DbException($message . ":\nSQL:" . $sql);
    }
}

function requestInfo() {
    return ['request' => request()->param(), 'response' => \app\common\dataset\RequestLog::getInstance()->response, 'user' =>''];
}

function    arrayToStr(array $array) {
    $string = '';
    if(count($array)){
        foreach ($array as $key => $value) {
            $string .= "\n".$key;
            if (is_array($value)) {
                $string.=':'.arrayToStr($value);
            } else {
                $string.= '='.$value;
            }
        }
    }
    return $string;
}

function getRealIp() {
    static $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = false;
        }
        for ($i = 0; $i < count($ips); $i++) {
            if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    $ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
    return $ip;
}

function tcpPost($sendMsg, $ip, $port) {

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    if (!$socket) {
        think\facade\Log::error("TCP create failed", 'tcp-error');
        return false;
    }
    $connection = socket_connect($socket, $ip, $port);
    if (!$connection) {
        think\facade\Log::error("TCP connect failed", 'tcp-error');
        return false;
    }
    socket_write($socket, $sendMsg . "|end|");

    $return = socket_read($socket,1024);

    socket_close($socket);
    return $return;
}


if (!function_exists('logToFile')) {

    function logToFile($msg, $fileName = 'logCenter') {
        $environment=config('app.environment');
        date_default_timezone_set('Asia/Chongqing');
        $logFile = sprintf("/mnt/%s/log/qn-%s.%s.log",$environment, $fileName, date('Y-m-d', strtotime("today")));
        // 判断日志有没有达到2g, 如果达到就用不前时间戳重命名
        $flag = isOutSize($logFile);
        if ($flag) {
            // 重命名文件
            $str = date('Y-m-d', strtotime("today")) . '-' . time();
            $newName = sprintf("/mnt/%s/log/qn-%s.%s.log",$environment,$fileName, $str);
            rename($logFile, $newName);
        }
        $hostName = phpversion() < "5.3.0" ? $_SERVER['HOSTNAME'] : gethostname();

        $fp = fopen($logFile, 'a');
        fwrite($fp, sprintf("%s\t%s\thostname=%s\n", date("H:i:s"), $msg, $hostName));
        fclose($fp);
    }
}
/**
 *判断日志文件是否超过大小，超过2G返回true ,否则返回false,文件不存在return false
 */
if (!function_exists('isOutSize')) {

    function isOutSize($logFile) {
        $config_size = 56200000;
        if (!file_exists($logFile)) {
            return false;
        }
        $size = filesize($logFile);
        if ($size < $config_size) {
            return false;
        } else {
            return true;
        }
    }
}
if (!function_exists('getRealIp')) {
    function getRealIp() {
        static $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        $ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
}
