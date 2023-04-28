CREATE TABLE tx_deepl_settings
(
	languages_assigned text
);

CREATE TABLE tx_wvdeepltranslate_glossaryentry
(
	term varchar(255) default ''
);


create table tx_wvdeepltranslate_glossary
(
	glossary_ready    int(2) unsigned  default '0',
	glossary_lastsync int(11) unsigned default '0' not null,
	glossary_id       varchar(60)      default '',
	glossary_name     varchar(255)     default ''  not null,
	source_lang       varchar(10)      default ''  not null,
	target_lang       varchar(10)      default ''  not null
);

CREATE TABLE pages
(
	tx_wvdeepltranslate_content_not_checked tinyint unsigned DEFAULT 0 NOT NULL,
	tx_wvdeepltranslate_translated_time     int(10) NOT NULL DEFAULT 0,
	glossary_information                    int(11) unsigned default '0' not null
);
