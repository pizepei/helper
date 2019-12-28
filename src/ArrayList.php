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

    /**
     * @Author 皮泽培
     * @Created 2019/12/23 11:53
     * @param $arr1
     * @param $arr2
     * @title  深层合并数组
     * @explain 深层合并数组(两个)
     * @return array
     * @throws \Exception
     */
    public function array_merge_deep($arr1,$arr2){
        $merged	= $arr1;

        foreach($arr2 as $key => &$value){
            if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                $merged[$key]	= $this->array_merge_deep($merged[$key], $value);
            }elseif(is_numeric($key)){
                if(!in_array($value, $merged)) {
                    $merged[]	= $value;
                }
            }else{
                $merged[$key]	= $value;
            }
        }

        return $merged;
    }

    /**
     * @Author 皮泽培
     * @Created 2019/12/28 9:30
     * @param mixed ...$arr1
     * @return array [json] 定义输出返回数据
     * @title  多数组批量合并
     * @throws \Exception
     */
    public function array_merge_deep_more(...$arr)
    {
        if (count($arr) <=1){ throw new \Exception('至少两个array');}
        $count = count($arr);
        for ($i=1;$i<$count;$i++)
        {
            $merged = array_shift($arr);
            $merged = $this->array_merge_deep($merged,$arr[0]);
        }
        return $merged;
    }
    /**
     * @Author 皮泽培
     * @Created 2019/12/26 14:54
     * @title  深层数组排序
     * @param $data 需要排序的array
     * @param $condition ['key'=>'SORT_DESC',...]   SORT:SORT_DESC,SORT_ASC
     * @return array
     */
    public function sortMultiArray(&$data, $condition):array
    {
        if (count($data) <= 0 || empty($condition)) {
            return $data;
        }
        $dimension = count($condition);
        $fileds = array_keys($condition);
        $types = array_values($condition);
        switch ($dimension) {
            case 1:
                $data = $this->sort1Dimension($data, $fileds[0], $types[0]);
                break;
            case 2:
                $data = $this->sort2Dimension($data, $fileds[0], $types[0], $fileds[1], $types[1]);
                break;
            default:
                $data = $this->sort3Dimension($data, $fileds[0], $types[0], $fileds[1], $types[1], $fileds[2], $types[2]);
                break;
        }
        return $data;
    }
    public function sort1Dimension(&$data, $filed, $type)
    {
        if (count($data) <= 0) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $temp[$key] = $value[$filed];
        }
        array_multisort($temp, $type, $data);
        return $data;
    }
    public function sort2Dimension(&$data, $filed1, $type1, $filed2, $type2)
    {
        if (count($data) <= 0) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $sort_filed1[$key] = $value[$filed1];
            $sort_filed2[$key] = $value[$filed2];
        }
        array_multisort($sort_filed1, $type1, $sort_filed2, $type2, $data);
        return $data;
    }
    public function sort3Dimension(&$data, $filed1, $type1, $filed2, $type2, $filed3, $type3)
    {
        if (count($data) <= 0) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $sort_filed1[$key] = $value[$filed1];
            $sort_filed2[$key] = $value[$filed2];
            $sort_filed3[$key] = $value[$filed3];
        }
        array_multisort($sort_filed1, $type1, $sort_filed2, $type2, $sort_filed3, $type3, $data);
        return $data;
    }
}