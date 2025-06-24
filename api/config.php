<?php
//error_reporting(E_ALL ^ E_DEPRECATED);
//服务器配置

//数据库配置
define('DBHOST', '127.0.0.1');//数据库地址
define('DBPORT', 3306);//数据库端口
define('DBUSER', 'root');//数据库用户名
define('DBPASS', 'yourpassword');//数据库密码
define('DBNAME', 'ldap_manage');//数据库名
//LDAP配置
define('LDAP_SERVER', "ldap://localhost:389"); //服务器地址
define('LDAP_ADMIN_DN', 'cn=admin,dc=example,dc=net'); //管理员DN
define('LDAP_ADMIN_PASSWORD', 'ldappassword'); //管理员密码
define('LDAP_BASE_DN', 'ou=demo,dc=example,dc=net'); //搜索DN
define('LDAP_FILTER', '(uid=%s)'); //搜索过滤器

//邮箱服务配置
define('MAIL_HOST', 'smtp.exmail.qq.com'); //SMTP服务器
define('MAIL_PORT', 465); //SMTP端口
define('MAIL_USER', 'it@example.com'); //SMTP账号
define('MAIL_PASS', 'emailpassword'); //SMTP密码
define('RESET_LINK', 'http://localhost/');//填写默认服务器地址
//RESET_LINK为重置密码的链接，需要在服务器上配置好，否则邮件收到的链接不对。


