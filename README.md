VeinOpen
--
1.异常处理Handle在配置文件中修改为
```
  'exception_handle'       => '\\app\\common\\exception\\ExceptionHandle',
```
 ExceptionHandle将全面接管系统的异常/错误,全部返回json信息,见render方法
 report会将异常报告给指定单位,默认为钉钉,可在环境变量中配置相关配置项
2.对DB--Connection进行了少量优化(加入异常日志相关功能)

Documentation
--
认真看看文档可能会对你的开发有所帮助哦！

3.加入事件系统,触发时间只需要调用助手函数`triggerEvent`
4.


Environment
---
>1. PHP 版本不低于 PHP5.6，推荐使用 PHP7 以达到最优效果；
>2. 需开启 PATHINFO，不再支持 ThinkPHP 的 URL 兼容模式运行（源于如何优雅的展示）。


* Nginx

```
server {
	listen 80;
	server_name wealth.demo.cuci.cc;
	root /home/wwwroot/ThinkAdmin;
	index index.php index.html index.htm;
	
	add_header X-Powered-Host $hostname;
	fastcgi_hide_header X-Powered-By;
	
	if (!-e $request_filename) {
		rewrite  ^/(.+?\.php)/?(.*)$  /$1/$2  last;
		rewrite  ^/(.*)$  /index.php/$1  last;
	}
	
	location ~ \.php($|/){
		fastcgi_index   index.php;
		fastcgi_pass    127.0.0.1:9000;
		include         fastcgi_params;
		set $real_script_name $fastcgi_script_name;
		if ($real_script_name ~ "^(.+?\.php)(/.+)$") {
			set $real_script_name $1;
		}
		fastcgi_split_path_info ^(.+?\.php)(/.*)$;
		fastcgi_param   PATH_INFO               $fastcgi_path_info;
		fastcgi_param   SCRIPT_NAME             $real_script_name;
		fastcgi_param   SCRIPT_FILENAME         $document_root$real_script_name;
		access_log      /home/wwwlog/domain_access.log    access;
		error_log       /home/wwwlog/domain_error.log     error;
	}
	
	location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
		access_log  off;
		error_log   off;
		expires     30d;
	}
	
	location ~ .*\.(js|css)?$ {
		access_log   off;
		error_log    off;
		expires      12h;
	}
}
```
