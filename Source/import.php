<?php
/*
 * Download the zip code data of Japan Post Holdings Co., Ltd.,
 * deployment, inserted into the DB.
 *
 *      Create 2016-10-16 By Masato Kobayashi
 *
 *
 *   Copyright (C) 2016 Masato Kobayashi. All rights reserved.
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

mb_language('Japanese');
mb_internal_encoding('UTF-8');

$zip_file = 'ken_all_rome.zip';
$zip_url = 'http://www.post.japanpost.jp/zipcode/dl/roman/'.$zip_file;
$tmp_file = tempnam(sys_get_temp_dir(), 'postal');

$zip = null;
$tmp = null;
$cmp = null;
$dbh = null;
try {
	// ZIPファイルダウンロード
	if (!($zip = fopen($zip_url, 'rb'))) throw new Exception('File can not be opened. :'.$zip_url);
	if (!($tmp = fopen($tmp_file, 'wb'))) throw new Exception('File can not be opened. :'.$tmp_file);
	while (!feof($zip)) {
		fwrite($tmp, fread($zip, 8192));
	}
	fclose($zip);
	fclose($tmp);

	// DB接続
	$dbh = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '', array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => true,
	));
	$dbh->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;");
	// INSERT文指定
	$sth = $dbh->prepare('INSERT INTO roman_alphabet_postal_codes (postal_code, name_of_prefectures, '
			.'city_name, town_area_name, roman_alphabet_name_of_prefectures, roman_alphabet_city_name, '
			.'roman_alphabet_town_area_name) VALUES (?, ?, ?, ?, ?, ?, ?)');
	$dbh->beginTransaction();

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
				// ストリームフィルタをセット
				if (!stream_filter_prepend($fp, 'convert.iconv.cp932/utf-8', STREAM_FILTER_READ))
					throw new Exception('Counld not apply stream filter.');
				$ofp = fopen( 'php://temp/maxmemory', 'r+b' );
				if (!$ofp) throw new Exception('File can not be opened. :'.$entry);

				while (!feof($fp))
					fwrite($ofp, fread($fp, 8192));
				fclose($fp);

				rewind($ofp);
				while (($line = fgetcsv($ofp, 1024 * 1024, ',')) !== false) {
					// DBに挿入
					$sth->bindValue(1,$line[0],PDO::PARAM_STR);
					$sth->bindValue(2,$line[1],PDO::PARAM_STR);
					$sth->bindValue(3,$line[2],PDO::PARAM_STR);
					$sth->bindValue(4,$line[3],PDO::PARAM_STR);
					$sth->bindValue(5,$line[4],PDO::PARAM_STR);
					$sth->bindValue(6,$line[5],PDO::PARAM_STR);
					$sth->bindValue(7,$line[6],PDO::PARAM_STR);
					$sth->execute();
				}

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

	$dbh->commit();
	$dbh = null;
} catch (Exception $e) {
	fputs(STDERR, $e->getMessage()."\n");
	if (!is_null($dbh)) $dbh->rollBack();
	if (!is_null($zip)) fclose($zip);
	if (!is_null($tmp)) fclose($tmp);
	if (!is_null($cmp)) $cmp->close();
}

unlink($tmp_file);
