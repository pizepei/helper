<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper基础类
 */

namespace pizepei\helper;

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
     * @Author 皮泽培
     * @Created 2019/7/17 15:07
     * @param bool $new
     * @title  常用系统函数
     * @explain 常用系统函数
     * @return Helper
     * @throws \Exception
     */
    public static function init($new = false)
    {
        /**
         * 实现本身这个类
         */
        if (!self::$Helper || $new){
            self::$Helper = new static();
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
    public static function file($new = false)
    {
        if (!self::$File || $new){
            self::$File = new File();
        }
        return self::$File;
    }
    /**
     * @Author 皮泽培
     * @Created 2019/7/17 15:07
     * @param bool $new
     * @title  文件函数
     * @explain 文件类函数
     * @return Str|null
     * @throws \Exception
     */
    public static function str($new = false)
    {
        if (!self::$Str || $new){
            self::$Str = new Str();
        }
        return self::$Str;
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
    public  function getUuid($strtoupper=false,$separator=45,$parameter=false)
    {
        $charid = md5(($parameter?$parameter:mt_rand(10000,99999)).uniqid(mt_rand(), true));
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
    public function is_empty($data,$name='')
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
                if (empty($data[$name]) || $data[$name] === 0 || $data[$name] === '0' || $data[$name] === ''|| $data[$name] === []){
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
     * @param array $data 请求的主体数据
     * @param array $parameter 参数 ssl[1、2]默认2验证https ssl       type [get、put、post、delete] 默认get，有$data自动设置为post        timeout  超时单位秒
     * @return array info 请求信息  result 获取的请求body  error 错误数据
     */
    public function httpRequest($url,array $data=[],array $parameter=[])
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
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        $getinfo = curl_getinfo($curl); //获取请求信息
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'info'  =>  $getinfo,
            'result'=>  $output,
            'error' =>  $error,
        ];
    }
}