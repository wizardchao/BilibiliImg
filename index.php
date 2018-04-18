<?php
/* 
 * 随机图片
 * Auther Qiang Ge
 * Date 2018-1-27 20:39
 * E-Mail 2962051004@qq.com
 */
$data   = file_get_contents('data.json');
$data   = json_decode($data, true);
$url    = 'https://api.yya.gs/'; //http://网址
$imgurl = $url . $data[mt_rand(0, count($data))];
header("location: $imgurl");