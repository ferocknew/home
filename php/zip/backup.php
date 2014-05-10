<?php
include_once ('pclzip.lib.php');
$dir = dirname(__FILE__);
$fileName = "archive.zip";
$filePath = $dir . "/" . $fileName;
$maxAcTime = 900;

if (file_exists($filePath))
	unlink($filePath);

$zip = new PclZip($fileName);

set_time_limit($maxAcTime);
$v_list = $zip -> create($dir, PCLZIP_OPT_REMOVE_PATH, $dir);
if ($v_list == 0) {
	exit('异常：' . $z -> errorInfo(true));
} else {
	// echo '备份成功';
}
header('HTTP/1.1 301 Moved Permanently');
header('Location:  ' . detect_uri() . $fileName);
/**
 * 返回当前执行的uri
 */
function detect_uri() {
	$http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
	$port = $_SERVER["SERVER_PORT"] == 80 ? '' : ':' . $_SERVER["SERVER_PORT"];
	$tempStr = explode("/", $_SERVER["REQUEST_URI"]);
	array_pop($tempStr);
	$tempStr[] = '';
	$url = $http . $_SERVER["SERVER_NAME"] . $port . implode("/", $tempStr);
	return $url;
}
