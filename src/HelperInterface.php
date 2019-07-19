<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/07/15
 * Time: 16:24
 * @title Helper基础接口类
 */
declare(strict_types=1);

namespace pizepei\helper;


interface HelperInterface
{
    /**
     * 对象
     */
    const object = null;

    public static function init(bool $new = false);


}