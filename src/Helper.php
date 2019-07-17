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

}