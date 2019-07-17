<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper字符串来
 */
namespace pizepei\helper;


class Str
{
    /**
     * 判断大小写
     * @param $str
     */
    public function checkcase($str)
    {
        if (preg_match('/^[a-z]+$/', $str)) {
            //echo '小写字母';
            return false;
        } elseif (preg_match('/^[A-Z]+$/', $str)) {
            //echo '大写字母';
            return true;
        }
    }

    /**
     * 获取随机数字 数字
     * @param $length
     * @param $crypto_strong
     */
    public  function int_rand($length,$one='')
    {
        $str = $this->random_pseudo_bytes(32,10,$one);
        $strlen = strlen($str)-1;
        $results = '';
        for($i=1;$i<=$length;$i++){
            $results  .= $str{mt_rand(0,$strlen)};
        }


        return $results;
    }
    /**
     * 获取随机字符串
     * @param $length
     * @throws \Exception
     */
    public  function str_rand($length,$one='')
    {
        $str = $this->random_pseudo_bytes(32,16,$one);
        $strlen = strlen($str)-1;
        $results = '';
        for($i=1;$i<=$length;$i++){
            $results  .= $str{mt_rand(0,$strlen)};
        }
        return $results;
    }

    /**
     * 随机
     * @param int    $length
     * @param int    $tobase
     * @param string $one
     * @return string
     * @throws \Exception
     */
    public  function random_pseudo_bytes($length=32,$tobase=16,$one='')
    {
        if(function_exists('openssl_random_pseudo_bytes')){
            $str = openssl_random_pseudo_bytes($length,$crypto_strong);
            if(!$crypto_strong){ throw new \Exception('请检测系统环境');}
            return $tobase==16?md5(bin2hex($one.$str)):base_convert(md5(bin2hex($one.$str)),16,$tobase);
        }else{
            $str = md5($one.str_replace('.', '', uniqid(mt_rand(), true)));
            return $tobase==16?$str:base_convert($one.$str,16,$tobase);
        }
    }


}