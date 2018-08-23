<?php
/**
 * 这个文件定义fis模板渲染所需要的公共函数
 */

use Quick\Utils\FISResource;

//////////FIS3函数 start
function scriptStart() {
    ob_start();
}

function scriptEnd() {
    $script = ob_get_clean();
    $reg = "/(<script(?:\s+[\s\S]*?[\"'\s\w\/]>|\s*>))([\s\S]*?)(?=<\/script>|$)/i";
    if (preg_match($reg, $script, $matches)) {
        FISResource::addScriptPool($matches[2]);
    } else {
        FISResource::addScriptPool($script);
    }
}

function styleStart() {
    ob_start();
}

function styleEnd() {
    $style = ob_get_clean();
    $reg = "/(<style(?:\s+[\s\S]*?[\"'\s\w\/]>|\s*>))([\s\S]*?)(?=<\/style>|$)/i";
    if (preg_match($reg, $style, $matches)) {
        FISResource::addStylePool($matches[2]);
    } else {
        FISResource::addStylePool($style);
    }
}

/**
 * 设置前端加载器
 * @param [type] $id [description]
 */
function framework($id) {
    FISResource::setFramework(FISResource::getUri($id));
}

/**
 * 加载某个资源及其依赖
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function import($id) {
    FISResource::load($id);
}

/**
 * 添加标记位
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function placeholder($type) {
    echo FISResource::placeholder($type);
}

/**
 * 加载组件
 * @param  [type] $id   [description]
 * @param  array $args [description]
 * @return [type]       [description]
 */
function widget($id, $args = array()) {
    $uri = FISResource::getUri($id);
    if (is_file($uri)) {
        extract($args);
        include $uri;
        FISResource::load($id);
    }
}

/**
 * 渲染并返回模板
 * @param $id
 * @param $array
 * @return string
 */
function render($id, $array) {
    $path = FISResource::getUri($id);
    if (is_file($path)) {
        extract($array);
        ob_start();
        include $path;
        $html = ob_get_clean();
        FISResource::load($id); //注意模板资源也要分析依赖，否则可能加载不全
        return FISResource::renderResponse($html);
    } else {
        trigger_error($id . ' file not found!');
    }

    return '';
}

/**
 * 渲染页面
 * @param  [type] $id    [description]
 * @param  [type] $array [description]
 * @return [type]        [description]
 */
function display($id, $array) {
    $path = FISResource::getUri($id);
    if (is_file($path)) {
        extract($array);
        ob_start();
        include $path;
        $html = ob_get_clean();
        FISResource::load($id); //注意模板资源也要分析依赖，否则可能加载不全
        echo FISResource::renderResponse($html);
    } else {
        trigger_error($id . ' file not found!');
    }
}
//////////FIS3函数 end