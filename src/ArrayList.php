<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper文件类  数组函数
 */
declare(strict_types=1);
namespace pizepei\helper;

class ArrayList
{
    /**
     * @Author 皮泽培
     * @Created 2019/7/19 11:26
     * @param array $array
     * @param string $string
     * @param bool $strim  true 时 删除\r\n\r\n和空格
     * @title  把索引数组value用定义的字符串切割成数组集合
     * @explain  把索引数组value用定义的字符串切割成数组集合 相同的key会合并
     * @return array
     * @throws \Exception
     */
    public function array_explode_value(array $array,string $string,bool $strim=false):array
    {
        if (empty($array)){
            return [];
        }
        foreach ($array as $value){
            $explode = explode($string,$value);
            list($k, $v) = isset($explode[1])?$explode:[$value,$value];
            if ($strim){
                $k = trim(rtrim($k,"\r\n\r\n "),"\r\n\r\n ");
                $v = trim(rtrim($v,"\r\n\r\n "),"\r\n\r\n ");
            }
            /**
             * 如果出现重复的
             */
            if (isset($data[$k])){
                $recursive[$k] = $v;
                $data = array_merge_recursive($data,$recursive);
            }else{
                $data[$k] = $v;
            }
        }
        return $data;
    }
}