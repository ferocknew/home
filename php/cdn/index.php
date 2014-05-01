<?php

// php-cdn
// dynamic file caching pseudo cdn
/////////////////////////////////////////////////////////////////////////
// cdn root path   : http://cdn.com/
// cdn example url : http://cdn.com/path/to/resource.css?d=12345
// maps the uri    : /path/to/resource.css?d=12345
// to the origin   : http://yoursite.com/path/to/resource.css?d=12345
// caches file to  : ./cache/[base64-encoded-uri].css
// returns local cached copy or issues 304 not modified
/////////////////////////////////////////////////////////////////////////
// error_reporting(E_ERROR | E_PARSE);

// print_r($_SERVER['REQUEST_URI']);
// exit;
// cache for N seconds (default 1 day)
$f_expires = 86400;

// the source that we intend to mirror
$f_origin = 'http://blog.ferock.net';
// 不要和域名重名
$cdn_dir = '';
// encode as filename-safe base64
if ($_SERVER['REQUEST_URI'] == '/')
  exit ;

$f_dir = '';
$f_temp = explode("/", $_SERVER['REQUEST_URI']);
array_pop($f_temp);
$f_dir = implode("/", $f_temp);
$f_dir_path = dirname(__FILE__) . '/cache' . $f_dir;
// echo dirname(__FILE__) . '/cache' . $f_dir;
// exit ;
if (!file_exists($f_dir_path))
  mkdirs($f_dir_path);

$f_name = strtr(sha1($_SERVER['REQUEST_URI']), '+/=', '-_,');
// $f_dir
// parse the file extension
$f_ext = strrchr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '.');

// construct usable file path
$f_path = $f_dir_path . "/{$f_name}{$f_ext}";
$curlURL = str_replace($cdn_dir, "", $f_origin . $_SERVER['REQUEST_URI']);

// assign the correct mime type
switch ($f_ext) {
  // images
  case '.gif' :
    $f_type = 'image/gif';
    break;
  case '.jpg' :
    $f_type = 'image/jpeg';
    break;
  case '.png' :
    $f_type = 'image/png';
    break;
  case '.ico' :
    $f_type = 'image/x-icon';
    break;
  // documents
  case '.js' :
    $f_type = 'application/x-javascript';
    break;
  case '.css' :
    $f_type = 'text/css';
    break;
  case '.xml' :
    $f_type = 'text/xml';
    break;
  case '.json' :
    $f_type = 'application/json';
    break;
  case '.php' :
    $f_type = 'text';
    break;
  // no match
  default :
    // extension is not supported, issue *404*
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    header('Cache-Control: private');
    exit ;
}

// check the local cache
if (file_exists($f_path)) {
  // get last modified time
  $f_modified = filemtime($f_path);
  // validate the client cache

  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $f_modified)) {
    // client has a valid cache, issue *304*
    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
  } else {
    // send all requisite cache-me-please! headers
    header('Pragma: public');
    header("Cache-Control: max-age={$f_expires}");
    header("Content-type: $f_type");
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $f_modified));
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $f_expires));

    // stream the file
    readfile($f_path);
    exit ;
  }
} else {

  // http *HEAD* request
  // verify that the image exists

  // $headInf = get_headers($curlURL, 1);
  // var_dump($headInf);
  // exit;
  $file = get_url_content($curlURL, $f_path);
  if ($file !== FALSE) {
    header('Pragma: public');
    header("Cache-Control: max-age={$f_expires}");
    header("Content-type: $f_type");
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $f_modified));
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $f_expires));
    readfile($f_path);
    exit ;
    // header($_SERVER['SERVER_PROTOCOL'] . ' 302 Not Modified');
    // header('HTTP/1.1 301 Moved Permanently');
    // header('Location: ' . $curlURL);
    exit ;
  } else {
    // the file doesn't exist, issue *404*
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    header('Cache-Control: private');
  }

  // finished
  curl_close($ch);
}
function httpcopy($url, $file = "", $timeout = 60) {
  $file = empty($file) ? pathinfo($url, PATHINFO_BASENAME) : $file;
  $dir = pathinfo($file, PATHINFO_DIRNAME);
  !is_dir($dir) && @mkdir($dir, 0755, true);
  $url = str_replace(" ", "%20", $url);

  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $temp = curl_exec($ch);
    if (@file_put_contents($file, $temp) && !curl_error($ch)) {
      return $file;
    } else {
      return false;
    }
  } else {
    $opts = array("http" => array("method" => "GET", "header" => "", "timeout" => $timeout));
    $context = stream_context_create($opts);
    if (@copy($url, $file, $context)) {
      //$http_response_header
      return $file;
    } else {
      return false;
    }
  }
}

function get_url_content($url, $filePath) {
  $user_agent = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)";
  $ch = curl_init();
  //curl_setopt ($ch, CURLOPT_PROXY, $proxy);
  curl_setopt($ch, CURLOPT_URL, $url);
  //设置要访问的IP
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
  //模拟用户使用的浏览器
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  // 使用自动跳转
  curl_setopt($ch, CURLOPT_TIMEOUT, 60);
  //设置超时时间
  curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
  // 自动设置Referer
  // curl_setopt ($ch, CURLOPT_COOKIEJAR, 'c:\cookie.txt');
  curl_setopt($ch, CURLOPT_HEADER, 0);
  //显示返回的HEAD区域的内容
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $result = curl_exec($ch);
  curl_close($ch);
  if ($result !== FALSE) {
    file_put_contents($filePath, $result);
    return TRUE;
  } else {
    return FALSE;
  }
}

function mkdirs($dir) {
  if (!is_dir($dir)) {
    if (!mkdirs(dirname($dir))) {
      return false;
    }
    if (!mkdir($dir, 0777)) {
      return false;
    }
  }
  return true;
}
?>