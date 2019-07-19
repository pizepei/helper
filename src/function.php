<?php
/**
 * 自定义函数
 */
declare (strict_types = 1);
use \pizepei\helper\Helper;

if (!function_exists('Helper')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param bool $new  是否强制实例化
     * @return  Helper
     */
    function Helper(bool $new = false):Helper
    {
        return Helper::init($new);
    }
}
if (!function_exists('Helper')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param bool $new  是否强制实例化
     * @return  \app\HelperClass
     */
    function HelperClass(bool $new = false):Helper
    {
        /**
         * 使用容器返回需要的来的实例
         * 函数的:Helper用了强制控制返回的实例
         * 注解中的 @return  \app\HelperClass 用来适配IDE并不会实例化HelperClass类，和方便注册服务
         * 并不会实例化HelperClass类，Helper类会在实例化时获取到HelperClass的private $childContainer属性用来注册服务
         */
        return Helper::init($new);
    }
}