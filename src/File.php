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


use Stringy\StaticStringy;

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

    /**
     * @Author 皮泽培
     * @Created 2019/9/24 17:11
     * @param string $path
     * @title  获取文件大小
     * @explain 获取文件大小
     * @return string
     * @router get
     */
    public function getFileSize(string $path)
    {
        return number_format(filesize($path) / (1024 * 1024), 2);//去小数点后两位
    }
    /**
     * @Author 皮泽培
     * @Created 2019/9/24 16:46
     * @param string $path 不包括base路径的 文件路径(包括文件名)
     * @param string $name 下载时显示的名称包括扩展名的文件名称
     * @param int $buffer  下载速度
     * @param string $base 基础路径
     * @return string
     * @title  提供下载
     * @explain 对外通过简单的下载
     * @throws \Exception
     */
    public function provideDownloads(string $path,string $name,int $buffer=1024,string $base='..'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR)
    {
        # 安全起见 路径有一个base路径  $path路径不能包含. ..
        if (strpos($path,'..'.DIRECTORY_SEPARATOR) !== false || strpos($path,'.'.DIRECTORY_SEPARATOR)!== false ){
            return 'Speed illegal path';
        }
        $filePath = $base.$path;
        if(!file_exists($filePath)){
            return $name.' There is no';
        }
        $fileSize = $this->getFileSize($filePath);
        //打开文件
        $file = fopen($filePath, "r");
        //返回的文件类型
        Header("Content-type: application/octet-stream");
        //按照字节大小返回
        Header("Accept-Ranges: bytes");
        //返回文件的大小
        Header("Accept-Length: ".filesize($filePath));
        //这里对客户端的弹出对话框，对应的文件名
        Header("Content-Disposition: attachment; filename=".$name);
        //修改之前，一次性将数据传输给客户端
        echo fread($file, filesize($filePath));
        //修改之后，一次只传输1024个字节的数据给客户端
        //向客户端回送数据
        //判断文件是否读完
        while(!feof($file)){
            //将文件读入内存
            $file_data = fread($file, $buffer);
            //每次向客户端回送$buffer个字节的数据
            echo $file_data;
        }
        fclose($file);
    }

}