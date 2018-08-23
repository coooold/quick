<?php
/**
 * 这个文件封装一些全局函数，方便框架使用
 */

use Quick\Quick;

/**
 * @param $class string
 * @return Main|mixed
 */
function di($class) {
    return Quick::di($class);
}

/**
 * 创建排他文件锁
 * @param $name
 * @throws Exception
 */
function singleton($name) {
    Quick::flock($name);
}

/**
 * 根据路由生成url，使用query的形式，如果第一个字符是!，那么不对query内容进行转义
 * @param       $name
 * @return false|string
 */
function url($name) {
    $raw = false;

    // 表示不对query进行转义
    if ($name[0] == '!') {
        $name = substr($name, 1, strlen($name) - 1);
        $raw = true;
    }

    return Quick::makeUrl($name, $raw);
}
