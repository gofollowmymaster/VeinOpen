<?php


return [
    // 应用调试模式
    'app_debug'      => env('app_debug',false),
    // 应用Trace调试
    'app_trace'      => env('app_debug',false),
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 1,
    'exception_handle'       => '\\app\\common\\exception\\ExceptionHandle',
    'url_route_must'		=>  env('app.route_must',false),
    'route_check_cache'	=>	true,
    'url_html_suffix'=>'',


];
