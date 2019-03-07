<?php
defined('IN_IA') or exit('Access Denied');

$config = array();

//$config['db']['host'] = '192.168.1.222';
$config['db']['host'] = 'localhost';
$config['db']['username'] = 'root';
$config['db']['password'] = '';
//$config['db']['password'] = 'geiwo120';
//$config['db']['host'] = 'localhost';
//$config['db']['username'] = 'root';
//$config['db']['password'] = '244616678@2018';

$config['db']['port'] = '3306';
$config['db']['database'] = 'golf';
//$config['db']['database'] = 'db_smart_office_building_20161220';
//$config['db']['database'] = 'wechat_20170828';
$config['db']['charset'] = 'utf8';
$config['db']['pconnect'] = 0;
$config['db']['tablepre'] = 'ims_';

// --------------------------  CONFIG COOKIE  --------------------------- //
$config['cookie']['pre'] = '222a_';
$config['cookie']['domain'] = '';
$config['cookie']['path'] = '/';

// --------------------------  CONFIG SETTING  --------------------------- //
$config['setting']['charset'] = 'utf-8';
$config['setting']['cache'] = 'memcache';
$config['setting']['timezone'] = 'Asia/Shanghai';
$config['setting']['memory_limit'] = '256M';
$config['setting']['filemode'] = 0644;
$config['setting']['authkey'] = 'a09fe620';
$config['setting']['founder'] = '1';
$config['setting']['development'] = 1;
$config['setting']['referrer'] = 0;

// --------------------------  CONFIG UPLOAD  --------------------------- //
$config['upload']['attachdir'] = 'attachment';
$config['upload']['image']['extentions'] = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'gif');
$config['upload']['image']['limit'] = 50000;
$config['upload']['audio']['extentions'] = array('mp3');
$config['upload']['audio']['limit'] = 50000;
$config['upload']['file']['extentions'] = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx','mp4');
$config['upload']['file']['limit'] = 100000;

// --------------------------  CONFIG MEMCACHE  --------------------------- //
$config['setting']['memcache']['server'] = '127.0.0.1';
$config['setting']['memcache']['port'] = 11211;
$config['setting']['memcache']['pconnect'] = 1;
$config['setting']['memcache']['timeout'] = 30;
$config['setting']['memcache']['session'] = 1;