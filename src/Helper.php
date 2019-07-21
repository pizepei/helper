<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper基础类
 */
declare(strict_types=1);

namespace pizepei\helper;

use Closure;
use mysql_xdevapi\CrudOperationBindable;

/**
 * Class Helper
 * @package pizepei\helper
 * @property Helper           $helper
 * @property Str              $str
 * @property arrayList        $array
 * @method static File file(bool $new = false) 文件类
 * @method static File google(string $question) 向谷歌提问，返回答案内容
 */
class Helper implements  HelperInterface
{
    /**
     * 当前类
     * @var null
     */
    protected static $Helper = null;
    /**
     * 文件类
     * @var null
     */
    protected static $File = null;
    /**
     * 字符串类
     * @var null
     */
    protected static $Str = null;
    /**
     * 容器绑定标识(父)
     * @var array
     */
    private $parentBind = [
        'file'                     => File::class,
        'str'                      => Str::class,
        'helper'                   => Helper::class,
        'arrayList'                => ArrayList::class,
    ];
    /**
     * 容器绑定标识(子)
     * @var array
     */
    protected $childBind = [
    ];
    /**
     * 容器(父)
     * @var array
     */
    private $parentContainer = [

    ];
    /**
     * 容器(子)
     * @var array
     */
    private $childContainer = [

    ];

    /**
     * 数组类
     * @var null
     */
    protected static $arrayList = null;

    //在类外调用一个不存在的普通方法时，调用此方法
    public function __call($name,$value) { //参数为：类外调用的方法名称以及调用此方法时传递的参数
        echo $name."这个普通方法不存在，你调用这个不存在方法传递的值为".'<br/>';
        var_dump($value).'<br/>';
    }
    public static function __callStatic($name,$arguments)
    {
        #parent
        static::init();
        if (isset(self::$Helper->parentBind[$name])){
            # 容器中有服务
            if (!isset(self::$Helper->parentContainer[$name])){
                self::$Helper->parentContainer[$name] = new  self::$Helper->parentBind[$name];
            }
            return self::$Helper->parentContainer[$name];
        }else if (isset(self::$Helper->childBind[$name])) {
            # 容器中有服务
            if (!isset(self::$Helper->childContainer[$name])){
                self::$Helper->childContainer[$name] = new  self::$Helper->childBind[$name];
            }
            return self::$Helper->childContainer[$name];
        }
        throw new \Exception('The container does not exist');
    }




    public function __get($name)
    {
        #parent
        if (isset(self::$Helper->parentBind[$name])){
            # 容器中有服务
            if (!isset(self::$Helper->parentContainer[$name])){
                self::$Helper->parentContainer[$name] = new  self::$Helper->parentBind[$name];
            }
            return self::$Helper->parentContainer[$name];
        }else if (isset(self::$Helper->childBind[$name])) {
            # 容器中有服务
            if (!isset(self::$Helper->childContainer[$name])){
                self::$Helper->childContainer[$name] = new  self::$Helper->childBind[$name];
            }
            return self::$Helper->childContainer[$name];
        }
        throw new \Exception('The container does not exist');
    }

    public function exists($name,$type='parent')
    {

    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/17 15:07
     * @param bool $new
     * @title  常用系统函数
     * @explain 常用系统函数
     * @return Helper
     * @throws \Exception
     */
    public static function init(bool $new = false):self
    {
        /**
         * 实现本身这个类
         */
        if (!self::$Helper || $new){
            self::$Helper = new self();
        }
        return self::$Helper;
    }
    /**
     * @Author 皮泽培
     * @Created 2019/7/17 15:07
     * @param bool $new
     * @title  文件函数
     * @explain 文件类函数
     * @return File|null
     * @throws \Exception
     */
//    public static function file(bool $new = false):File
//    {
//        if (!self::$File || $new){
//            self::$File = new File();
//        }
//        return self::$File;
//    }
    /**
     * @Author 皮泽培
     * @Created 2019/7/17 15:07
     * @param bool $new
     * @title  文件函数
     * @explain 文件类函数
     * @return Str|null
     * @throws \Exception
     */
    public static function str(bool$new = false):Str
    {
        if (!self::$Str || $new){
            self::$Str = new Str();
        }
        return self::$Str;
    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/19 15:07
     * @param bool $new
     * @title  文件函数
     * @explain 文件类函数
     * @return ArrayList|null
     * @throws \Exception
     */
    public static function arrayList(bool$new = false):ArrayList
    {
        if (!self::$arrayList || $new){
            self::$arrayList = new ArrayList();
        }
        return self::$arrayList;
    }

    /***********************************函数方法*****************************************************/

    /**
     *
     * @title  生成uuid方法
     * @param bool $strtoupper 是否大小写 true为大写
     * @param int  $separator 分隔符  45 -       0 空字符串
     * @param bool $parameter true 是否使用空间配置分布式时不同机器上使用不同的值
     * @return string
     */
    public  function getUuid(bool $strtoupper=false,int $separator=45,string $parameter=''):string
    {
        $charid = md5(($parameter==''?$parameter:mt_rand(10000,99999)).uniqid((string)mt_rand(), true));
        if($strtoupper){$charid = strtoupper($charid);}
        $hyphen = chr($separator);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/17 16:10
     * @param $data
     * @param string $name
     * @title  判断是否为空
     * @explain 如果为int 0  string 0 array [] 都会返回 true
     * @return bool
     * @throws \Exception
     */
    public function is_empty($data,string $name=''):bool
    {
        if ($name == ''){
            if (empty($data) || $data === 0 || $data === '0' || $data === '' || $data===[]){
                return true;
            }
        }else if (is_array($data) && $name !== ''){
            /**
             * 判断是否存在
             */
            if (isset($data[$name])){
                if (empty($data[$name]) || $data[$name] === 0 || $data[$name] === '0' || $data[$name] === '' || $data[$name] === []){
                    return true;
                }
            }else{
                return true;
            }
        }else{
            throw new \Exception('参数错误');
        }
        return false;
    }

    /**
     * @title http请求方法
     * @param $url 请求地址（get参数拼接上）
     * @param dtring $data 请求的主体数据
     * @param array $parameter 参数 ssl[1、2]默认2验证https ssl       type [get、put、post、delete] 默认get，有$data自动设置为post        timeout  超时单位秒
     * @return array info 请求信息  body 获取的请求body  error 错误数据   header  响应方的响应header
     */
    public function httpRequest(string $url,string $data='',array $parameter=[]):array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $parameter['ssl']??2);//是否忽略证书 默认不忽略
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,$parameter['ssl']??2);//是否忽略证书 默认不忽略
        #如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件
        #curl_setopt($curl,CURLOPT_CAINFO,dirname(__FILE__).'/cacert.pem');//这是根据http://curl.haxx.se/ca/cacert.pem 下载的证书，添加这句话之后就运行正常了
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $parameter['redirect']??false); // 使用自动跳转 默认否
        /**
         * 设置超时
         */
        curl_setopt($curl, CURLOPT_TIMEOUT, $parameter['timeout']??30);//单位 秒，也可以使用
        #curl_setopt($ch, CURLOPT_NOSIGNAL, 1);     //注意，毫秒超时一定要设置这个
        #curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200); //超时毫秒，cURL 7.16.2中被加入。从PHP 5.2.3起可使用
        /**
         * 请求类型
         */
        if (isset($parameter['type'])){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $parameter['type']); //定义请求类型
            if (!empty($data)){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }else if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        /**
         * 判断是否需要设置header
         */
        if (isset($parameter['header'])){
            //定义header
            curl_setopt($curl, CURLOPT_HTTPHEADER, $parameter['header']);
        }
        /**
         * 设置COOKIE
         */
        if (isset($parameter['cookie'])){
            foreach ($parameter['cookie'] as $value){
                curl_setopt($curl, CURLOPT_COOKIE, $value);
            }
        }
        curl_setopt($curl, CURLOPT_HEADER, 1);//获取头部信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        $getinfo = curl_getinfo($curl); //获取请求信息
        $error = curl_error($curl);
        /**
         * 分类$output 获取获取头部信息和主体信息
         */
        list($header, $body) = explode("\r\n\r\n", $output);
        $header = explode("\n", $header);
        curl_close($curl);
        return [
            'RequestInfo'  =>  $getinfo??[],//请求详情
            'body' =>$body??'',//响应主体
            'header'=>$header??[],//响应方的响应header
            'error' =>  $error??'',//curl请求错误
            'result'=>  $output??'',//全部结果
        ];
//        json_decode()

    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/19 14:17
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @title  json解码 默认不处理大int数据，自动处理可能的失败问题
     * @explain json解码 默认不处理大int数据，自动处理可能的失败问题
     * @return array
     * @throws \Exception
     */
    public function json_decode(string $json,$assoc = true,int $depth=512,$options = JSON_BIGINT_AS_STRING):array
    {
        # 正常解析
        $data = json_decode($json,$assoc,$depth,$options);
        if ($data){
            # json字符串必须是utf8编码
            $encode = mb_detect_encoding($json, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            if ($encode !== 'UTF-8'){
                $json = mb_convert_encoding($json, 'UTF-8', $encode);
                $data = json_decode($json,$assoc,$depth,$options);
            }
        }
        /**
         * 正常解析失败  编码解析失败
         */
        if (!$data){
            #清除bom头
            $jsonUrl = urlencode($json);
            $data = json_decode(urldecode(trim($jsonUrl, "\xEF\xBB\xBF")),$assoc,$depth,$options);
        }
        #清除bom头失败
        if (!$data){
            #替换' 为"
            $data = json_decode(str_replace("'", '"', $json),$assoc,$depth,$options);
        }
        #替换'失败
        if (!$data){
            #不能有多余的逗号 如：[1,2,]
            $json = preg_replace('/,\s*([\]}])/m', '$1', $json);
            $data = json_decode(str_replace("'", '"', $json),$assoc,$depth,$options);
        }
        #可能是开启get_magic_quotes_gpc  这里就不做处理
        return $data?$data:[];
    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/19 14:19
     * @param array $array
     * @title  array转json字符串 默认不编码中文
     * @explain array转json字符串 默认不编码中文
     * @return string
     * @throws \Exception
     */
    public function json_encode(array $array):string
    {
        return json_encode($array,JSON_UNESCAPED_UNICODE);
    }
}