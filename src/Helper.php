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

use app\HelperClass;
use Closure;
use mysql_xdevapi\CrudOperationBindable;
use pizepei\staging\App;

/**
 * Class Helper
 * @package pizepei\helper
 * @property Helper           $helper
 * @property Str              $str
 * @property arrayList        $array
 * @method static File file(bool $new = false):File 文件类
 * @method static ArrayList arrayList(bool$new = false):ArrayList  数组类
 * @method static Str str(bool$new = false):Str 字符串类
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

    /**
     * @Author 皮泽培
     * @Created 2019/7/23 11:22
     * @param $name
     * @return object
     */
    public function __get($name):object
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
     * @param App $app
     * @title  常用系统函数
     * @explain 常用系统函数
     * @return Helper
     * @throws \Exception
     */
    public static function init(bool $new = false,$app=null):self
    {
        /**
         * 实现本身这个类
         */
        if (!self::$Helper || $new){
            self::$Helper = new self($app);
        }
        return self::$Helper;
    }
    public function __construct(App $app)
    {
        if ($app){
            # 判断是否存在
            $HelperClass = '\\'.$app->__APP__.'\HelperClass';
            if($HelperClass::childBind !== [])
            {
                #合并
                $this->childBind = array_merge($HelperClass::childBind ,$this->childBind);
            }
        }
        self::$Helper = $this;
    }

    /**
     * @Author 皮泽培
     * @Created 2019/7/23 11:49
     * @param \Redis $redis
     * @param array $name  Lock名请自己分类管理 ['name','name',...]不超过10个
     * @param bool $operation 默认设置Lock  false解除Lock
     * @param string $group 分组在设置Lock时设置，如果其他的任务出现异常可以使用分组删除分组下的所有Lock
     * @param int $usleep 默认300毫秒(三分之三秒)     1秒 = 1000毫秒
     * @param int $ttl 默认有效期问120s 超过ttl自动解除Lock 为了系统稳定不可设置0 超过600
     * @title  syncLock 同步Lock
     * @explain Lock默认有效期问120s 超过ttl自动解除Lock  特别注意一般情况设置Lock放在读取缓存前（判断操作前）解除Lock在获取结果后
     * @return bool|int|string
     */
    public  function syncLock(\Redis $redis,array $name,bool $operation=true,string $group='',int $usleep=300,$ttl = 10)
    {
        # 本方法解决的问题：
        # 同步lock 如 在需要请求第三方接口获取一个token缓存到本地30分钟时
        # 当本地缓存的token过期时同时有4个请求触发的业务逻辑都需要使用token时
        #   正常情况下在在第一个请求第三方接口获取到token并缓存到本地前的所有请求都会触发去请求第三方接口导致重复获取第三方token
        #       假如有4个请求触发了，那么就有概率出现最后一个请求的响应以及获取到并且缓存成功，然后第三个请求结果成响应然后再次缓存导致本地缓存token为无效token
        # 同时请不要忽略http请求的时间差

        # 在本地本地缓存是否存在前 使用syncLock($redis,$name,true)
        #   会判断当前syncLock是否锁
        #       如果是就按照配置usleep()
        #       如果不是就设置Lock锁并且不休眠马上return 进行业务逻辑获取信息并且缓存（这个操作后的其他请求会按照配置usleep()）
        # 在获取到对应内并且缓存成功后 使用syncLock($redis,$name,false)
        #
        # 关于多个Lock 任务嵌套 其中一个任务出现异常 导致死Lock 时可以通过group解决

        if ($ttl == 0){throw new \Exception('TTL cannot be 0');}
        if ($ttl >= 600){throw new \Exception('TTL cannot be greater than 600');}
        if ($usleep === 0){throw new \Exception('Usleep cannot be 0');}
        if (count($name) < 2){throw new \Exception('Name must be at least two');};
        if (count($name) > 10){throw new \Exception(' Names cannot exceed 10');}
        $group = $group==''?'':$group.':';

        $name = implode(':',$name);
        # 判断是 解除Lock  还是设置Lock
        if ($operation){
            $microtime = microtime(true);
            $time = $microtime+$ttl+($usleep*1000)+0.2;
            # 先读取是否有缓存
            $syncLock = $redis->get('helper:syncLock:'.$name);
            if ($syncLock == null || $syncLock==false){
                return $redis->setex('helper:syncLock:'.$group.$name,$ttl,20);# 没有Lock通过  可以执行下面的业务逻辑
            }
            if ($syncLock == 20){
                $usleep = $usleep*1000;
                for ($i=1;(time()-$time)<=0;$i++){
                    usleep($usleep);//缓解系统压力进行休眠
                    $syncLock = $redis->get('helper:syncLock:'.$group.$name);
                    if ($syncLock == null || $syncLock==false){
                        return  ['startTime'=>$microtime,'theTime'=>microtime(true),'i'=>$i];# 没有Lock通过
                    }
                }
                # 意外情况
                return false;
            }
        }else{
            # 解锁
            if ($group ==''){
                return $redis->del('helper:syncLock:'.$group.$name);
            }else{
                $keys = $redis->keys('helper:syncLock:'.$group.'*');
                if (!empty($keys)){return $redis->del($keys);
                }
            }

        }
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