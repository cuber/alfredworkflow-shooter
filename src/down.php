<?php

function sphash($file) {
  $fp = fopen($file, "rb");
  fseek($fp, 0, SEEK_END);
  $len = ftell($fp);
  fseek($fp, 0, SEEK_SET);
  $hash = array();
  foreach (array(4096, intval($len / 3) * 2, intval($len / 3), $len - 8192) as $set) {
    fseek($fp, $set, SEEK_SET);
    $str = fread($fp, 4096);
    $hash[] = md5($str);
  }
  fclose($fp);
  return implode(";", $hash);
}

function request($file) {
  $url  = "https://www.shooter.cn/api/subapi.php";
  $data = http_build_query(array(
    'filehash' => sphash($file),
    'pathinfo' => $file,
    'format'   => 'json',
    'lang'     => 'Chn&Eng'
  ));
  $opts = array(
    'http' => array(
      'method'  => "POST",
      'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                   "Content-Length: " . strlen($data) . "\r\n",
      'content' => $data,
      'timeout' => 10
    )
  );
  $context = stream_context_create($opts);
  $data = file_get_contents($url, false, $context);
  return json_decode($data, 1);
}

function down($file) {
  $subs = array();
  if (!($data = request($file))) die("未找到合适的字幕");
  foreach ($data as $src) {
    foreach ($src['Files'] as $sub) {
      if (empty($sub['Ext']) || empty($sub['Link'])) continue;
      !isset($subs[$sub['Ext']]) && $subs[$sub['Ext']] = array();
      $subs[$sub['Ext']][] = $sub['Link'];
    }
  }
  $down = array();
  foreach ($subs as $ext => $group)
  foreach ($group as $idx => $link)
  {
    if (!($sub  = file_get_contents($link))) continue;
    $name = $file . "." . ($idx + 1) . "." . $ext;
    if (!file_put_contents($name, $sub)) continue;
    $down[] = basename($name);
  }
  if (empty($down)) die("未找到合适的字幕");
  echo "共找到", count($down), "个字幕", PHP_EOL;
  array_map(function($v) { echo $v, PHP_EOL; }, $down);
}
