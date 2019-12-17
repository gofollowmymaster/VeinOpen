<?php


/* 定义会话路径 */
$_path_ = env('runtime_path') . 'sess' . DIRECTORY_SEPARATOR;
file_exists($_path_) || mkdir($_path_, 0755, true);
$_name_ = 's' . substr(md5(__DIR__), -8);

/* 配置会话参数 */
return [
    'prefix'         => 'qn',
    'path'           => $_path_,
    'name'           => $_name_,
    'var_session_id' => $_name_,
];
