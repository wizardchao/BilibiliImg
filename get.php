<?php
/*
 * 批量下载B站壁纸站壁纸
 * Auther Qiang Ge
 * Date 2018-1-27 19:06
 * E-Mail 2962051004@qq.com
 * Example SHell : php get.php count=10 path="images/"
 * Option
 * count：     轮训次数       默认10
 * path： 图片保存目录 /结尾   默认images/
 *
 */

if ('cli' !== PHP_SAPI) {
    exit('请在<Cli>模式下执行！');
}
if(!is_file('data.json')) file_put_contents('data.json','[]');
$param = getClientArgs();
$count = !empty($param['count']) ? $param['count'] : 10;
$path  = !empty($param['path']) ? $param['path'] : 'images/';
for ($o = 0; $o < $count; $o++) {
    $data = get('http://api.vc.bilibili.com/link_draw/v2/Doc/home');
    $data = json_decode($data, true);
    foreach ($data['data']['items'] as $url) {
        download($url['img_src'], $path);
        echo $url['img_src'] . PHP_EOL;
    }
    if($o+1 == $count){
    echo '下载完毕';
    }
}

/**
 * cli模式下取得命令行中的参数
 *
 * @param null
 * @return array
 * @Auther http://www.04007.cn/article/381.html
 */
function getClientArgs()
{
    global $argv;
    array_shift($argv);
    $args = array();
    array_walk($argv, function($v, $k) use (&$args)
    {
        @list($key, $value) = @explode('=', $v);
        $args[$key] = $value;
    });
    return $args;
}

/**
 * Curl
 *
 * @param string $url
 * @return string
 */
function get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    return $file;
}
/**
 * 下载图片到服务器
 *
 * @param string $url $path
 * @return null
 */
function download($url, $path)
{
    if (!is_dir($path)) { //判断目录是否存在
        mkdir($path); //不存在则创建目录
    }
    $filename = pathinfo($url, PATHINFO_BASENAME);
    if (!is_file($path . $filename)) { 
    //如果下载的文件不存在才开始执行下载
        $k = json_decode(file_get_contents('data.json'),true);
        $k[] = $filename;
        file_put_contents('data.json', json_encode($k));
        $file = get($url);
        $resource = fopen($path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
    }
}

/**
 * PHP 非递归实现查询该目录下所有文件
 * @param unknown $dir
 * @return multitype:|multitype:string
 */

function scanfiles($dir)
{
    if (!is_dir($dir)) {
        return array();
    }
    // 兼容各操作系统
    $dir  = rtrim(str_replace('\\', '/', $dir), '/') . '/';
    // 栈，默认值为传入的目录
    $dirs = array(
        $dir
    );
    // 放置所有文件的容器
    $rt   = array();
    do {
        // 弹栈
        $dir = array_pop($dirs);
        // 扫描该目录
        $tmp = scandir($dir);
        foreach ($tmp as $f) {
            // 过滤. ..
            if ($f == '.' || $f == '..') {
                continue;
            }
            // 组合当前绝对路径
            $path = $dir . $f;
            // 如果是目录，压栈。
            if (is_dir($path)) {
                array_push($dirs, $path . '/');
            } else {
                if (is_file($path)) {
                    // 如果是文件，放入容器中
                    $rt[] = $path;
                }
            }
        }
    } while ($dirs);
    // 直到栈中没有目录
    return $rt;
}
