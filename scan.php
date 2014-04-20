<?php
include 'workflows.php';

$media = array(
  'avi', 'mp4', 'mkv', 'rmvb', 'flv'
);

$wf = new Workflows();

$dir = rtrim($argv[1], '/') . '/';
foreach (scandir($dir, 1) as $name) {
  $file = $dir . $name;
  if (is_dir($file)) continue;
  $pieces = explode(".", $file);
  $ext = array_pop($pieces);
  if (!in_array($ext, $media)) continue;
  $wf->result(crc32($file), $file, $name, $file, 'icons/' . strtoupper($ext) . ".ico"); 
}
if (!count($wf->results())) {
  $wf->result(0, '', "没有找到视频文件", $dir, 'icon.png' );
}
echo $wf->toxml();
