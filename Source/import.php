<?php
$zip_file = 'ken_all_rome.zip';
$zip_url = 'http://www.post.japanpost.jp/zipcode/dl/roman/'.$zip_file;
$tmp_dir = sys_get_temp_dir();
$tmp_file = tempnam($tmp_dir, 'postal');

$zip = null;
$tmp = null;
try {
	if (!($zip = fopen($zip_url, 'rb'))) throw 'File can not be opened. :'.$zip_url;
	if (!($tmp = fopen($tmp_file, 'wb'))) throw 'File can not be opened. :'.$tmp_file;
	while (!feof($zip)) {
		fwrite($tmp, fread($zip, 8192));
	}
	fclose($zip);
	fclose($tmp);
} catch (Exception $e) {
	fputs(STDERR, $e->getMessage()."\n");
	if (!is_null($zip)) fclose($zip);
	if (!is_null($tmp)) fclose($tmp);
}

unlink($tmp_file);
