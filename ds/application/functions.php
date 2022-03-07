<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/11/4
 * Time: 15:59
 */
function zipExtract ($src, $dest)
{
$zip = new ZipArchive();
if ($zip->open($src)===true)
{
$zip->extractTo($dest);
$zip->close();
return true;
}
return false;
}
