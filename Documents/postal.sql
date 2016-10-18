SET SESSION FOREIGN_KEY_CHECKS=0;

/* Drop Indexes */

DROP INDEX import_kana_postal_codes_IX1 ON import_kana_postal_codes;
DROP INDEX import_kana_postal_codes_IX2 ON import_kana_postal_codes;
DROP INDEX import_large_office_individually_postal_codes_IX1 ON import_large_office_individually_postal_codes;
DROP INDEX postal_codes_IX1 ON postal_codes;



/* Drop Tables */

DROP TABLE IF EXISTS import_kana_postal_codes;
DROP TABLE IF EXISTS import_large_office_individually_postal_codes;
DROP TABLE IF EXISTS town_areas;
DROP TABLE IF EXISTS postal_codes;




/* Create Tables */

-- インポートカタカナ表記郵便番号
CREATE TABLE import_kana_postal_codes
(
	local_government_code char(5) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '全国地方公共団体コード',
	old_postal_code char(5) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '（旧）郵便番号',
	postal_code char(7) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '郵便番号',
	state_name_kana varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '都道府県名（カタカナ）',
	city_name_kana varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '市区町村名（カタカナ）',
	town_area_name_kana varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '町域名（カタカナ）',
	state_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '都道府県名（漢字）',
	city_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '市区町村名（漢字）',
	town_area_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '町域名（漢字）',
	two_or_more_of_the_postal_code_flag tinyint COMMENT '一町域が二以上の郵便番号で表される場合の表示',
	each_sublocality_flag tinyint COMMENT '小字毎に番地が起番されている町域の表示',
	town_area_with_chome_flag tinyint COMMENT '丁目を有する町域の場合の表示',
	two_or_more_of_the_town_area_flag tinyint COMMENT '一つの郵便番号で二以上の町域を表す場合の表示',
	update_status_code char(1) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '更新の表示',
	reason_for_change_code char(1) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '変更理由'
) ENGINE = InnoDB COMMENT = 'インポートカタカナ表記郵便番号' DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;


-- インポート大口事業所個別郵便番号
CREATE TABLE import_large_office_individually_postal_codes
(
	local_government_code char(5) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '全国地方公共団体コード',
	large_office_name_kana varchar(100) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '大口事業所名（カナ）',
	large_office_name_kanji varchar(80) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '大口事業所名（漢字）',
	state_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '都道府県名（漢字）',
	city_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '市区町村名（漢字）',
	town_area_name_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '町域名（漢字）',
	chome_etc_kanji varchar(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '小字名、丁目、番地等（漢字）',
	postal_code char(7) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '郵便番号',
	old_postal_code char(5) BINARY NOT NULL COMMENT '（旧）郵便番号',
	handling_post_office_kanji varchar(20) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '取扱局（漢字）',
	individual_postal_code_type_code char(1) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '個別番号の種別の表示',
	multiple_individual_postal_code int NOT NULL COMMENT '複数番号の有無',
	reason_for_change_code char(1) BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT '変更理由'
) ENGINE = InnoDB COMMENT = 'インポート大口事業所個別郵便番号' DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;


-- 郵便番号
CREATE TABLE postal_codes
(
	postal_code_id bigint unsigned NOT NULL COMMENT '郵便番号ID',
	postal_code char(7) BINARY NOT NULL COMMENT '郵便番号',
	local_government_code char(5) BINARY NOT NULL COMMENT '全国地方公共団体コード',
	state_name_kanji varchar(255) BINARY NOT NULL COMMENT '都道府県名（漢字）',
	city_name_kanji varchar(255) BINARY NOT NULL COMMENT '市区町村名（漢字）',
	state_name_kana varchar(255) BINARY NOT NULL COMMENT '都道府県名（カタカナ）',
	city_name_kana varchar(255) BINARY NOT NULL COMMENT '市区町村名（カタカナ）',
	state_name_roman varchar(255) BINARY NOT NULL COMMENT '都道府県名（ローマ字）',
	city_name_roman varchar(255) BINARY NOT NULL COMMENT '市区町村名（ローマ字）',
	PRIMARY KEY (postal_code_id),
	UNIQUE (postal_code)
) COMMENT = '郵便番号';


-- 町域
CREATE TABLE town_areas
(
	town_area_id bigint unsigned NOT NULL COMMENT '町域ID',
	postal_code_id bigint unsigned NOT NULL COMMENT '郵便番号ID',
	town_area_name_kanji varchar(255) BINARY NOT NULL COMMENT '町域名（漢字）',
	chome_etc_kanji varchar(255) BINARY COMMENT '小字名、丁目、番地等（漢字）',
	large_office_name_kanji varchar(80) BINARY COMMENT '大口事業所名（漢字）',
	town_area_name_kana varchar(255) BINARY NOT NULL COMMENT '町域名（カタカナ）',
	large_office_name_kana varchar(100) BINARY COMMENT '大口事業所名（カナ）',
	town_area_name_roman varchar(255) BINARY NOT NULL COMMENT '町域名（ローマ字）',
	large_office_name_roman varchar(255) BINARY COMMENT '大口事業所名（ローマ字）',
	PRIMARY KEY (town_area_id)
) COMMENT = '町域';



/* Create Foreign Keys */

ALTER TABLE town_areas
	ADD FOREIGN KEY (postal_code_id)
	REFERENCES postal_codes (postal_code_id)
	ON UPDATE RESTRICT
	ON DELETE RESTRICT
;



/* Create Indexes */

CREATE INDEX import_kana_postal_codes_IX1 ON import_kana_postal_codes (local_government_code ASC);
CREATE INDEX import_kana_postal_codes_IX2 ON import_kana_postal_codes (postal_code ASC);
CREATE INDEX import_large_office_individually_postal_codes_IX1 ON import_large_office_individually_postal_codes (postal_code ASC);
CREATE INDEX postal_codes_IX1 ON postal_codes (local_government_code ASC);



