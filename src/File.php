<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper文件类
 */
declare(strict_types=1);

namespace pizepei\helper;


class File
{

    /**
     *  判断目录是否存在
     * 不存在创建
     * @param $dir
     * @param int $mode
     * @return bool
     */
    public  function createDir(string $dir, int $mode = 0777):bool
    {
        if (is_dir($dir) || @mkdir($dir, $mode,true)) return TRUE;
        if (!$this->createDir(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode,true);
    }

    protected $findFileArr = array();

    /**
     * @Author 皮泽培
     * @Created 2019/7/17 15:03
     * @param $flodername  在指定的目录查找
     * @param $filename 需要查找的文件
     * @title  在指定的目录查找指定的文件
     * @explain 路由功能说明
     * @return array
     * @throws \Exception
     */
    public function findFile($flodername, $filename)
    {
        if (!is_dir($flodername)) {
            throw new \Exception('Not a directory');
        }
        if ($fd = opendir($flodername)) {
            while($file = readdir($fd)) {
                if ($file != "." && $file != "..") {
                    $newPath = $flodername.'/'.$file;
                    if (is_dir($newPath)) {
                        $this->findFile($newPath, $filename);
                    }
                    if ($file == $filename) {
                        $this->findFileArr[] = $newPath;
                    }
                }
            }
        }
        return $this->findFileArr;
    }

    /**
     * @Author pizepei
     * @Created 2019/7/7 8:58
     * @param $path
     * @title  删除文件夹以及文件夹下的所有文件
     * @explain 清空文件夹函数和清空文件夹后删除空文件夹函数的处理
     */
    public function deldir(string $path)
    {
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        $this->deldir($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }
}