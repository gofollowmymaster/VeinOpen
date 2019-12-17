<?php


$appRoot = app('request')->root();
$uriRoot = rtrim(preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot, '\\/');

return [
    // 定义模板替换字符串
    'tpl_replace_string' => [
        '__APP__'    => $appRoot,
        '__ROOT__'   => $uriRoot,
        '__STATIC__' => $uriRoot . "/static",
    ],
];
