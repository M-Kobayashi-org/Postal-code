<?php
mb_language('Japanese');
mb_internal_encoding('UTF-8');

$zip_file = 'ken_all_rome.zip';
$zip_url = 'http://www.post.japanpost.jp/zipcode/dl/roman/'.$zip_file;
$tmp_dir = sys_get_temp_dir();
$tmp_file = tempnam($tmp_dir, 'postal');

$zip = null;
$tmp = null;
$cmp = null;
try {
	// ZIPファイルダウンロード
	if (!($zip = fopen($zip_url, 'rb'))) throw new Exception('File can not be opened. :'.$zip_url);
	if (!($tmp = fopen($tmp_file, 'wb'))) throw new Exception('File can not be opened. :'.$tmp_file);
	while (!feof($zip)) {
		fwrite($tmp, fread($zip, 8192));
	}
	fclose($zip);
	fclose($tmp);
	$zip = null;
	$tmp = null;
	// ZIPファイル展開
	$cmp = new ZipArchive();
	if ($cmp->open($tmp_file) === true) {
		for ($i = 0; $i < $cmp->numFiles; $i++) {
			$fp = null;
			$ofp = null;
			try {
				$entry = $cmp->getNameIndex($i);
				if ( substr( $entry, -1 ) == '/' ) continue;
				$fp = $cmp->getStream( $entry );
				if (!$fp) throw new Exception('Unable to extract the file.');
				if (!stream_filter_prepend($fp, 'convert.iconv.cp932/utf-8', STREAM_FILTER_READ))
					throw new Exception('Counld not apply stream filter.');
				$ofp = fopen( $tmp_dir.'/'.$entry, 'wb' );
				if (!$ofp) throw new Exception('File can not be opened. :'.$entry);

				while (!feof($fp))
					fwrite($ofp, fread($fp, 8192));
				fclose($fp);
				fclose($ofp);
			} catch (Exception $e) {
				if (!is_null($fp)) fclose($fp);
				if (!is_null($ofp)) fclose($ofp);
				throw $e;
			}
		}
		if (!is_null($cmp))
			$cmp->close();
	}
	else
		throw new Exception('Can not open ZIP file.');
} catch (Exception $e) {
	fputs(STDERR, $e->getMessage()."\n");
	if (!is_null($zip)) fclose($zip);
	if (!is_null($tmp)) fclose($tmp);
	if (!is_null($cmp)) $cmp->close();
}

unlink($tmp_file);
