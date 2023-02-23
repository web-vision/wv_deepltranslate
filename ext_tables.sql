CREATE TABLE tx_deepl_settings (
		languages_assigned text
);

CREATE TABLE tx_wvdeepltranslate_glossaryentry (
		source varchar(255) default '',
		target varchar(255) default '',
		glossary int(11) unsigned default 0
);

CREATE TABLE tx_wvdeepltranslate_glossary (
		glossary_id varchar(60) default '',
		glossary_name varchar(255) default '',
		source_lang char(2),
		target_lang char(2),
		entries int(11) default '0',
		glossary_ready int(2) unsigned default '0',
		glossary_lastsync int(11) unsigned default '0' not null
);

CREATE TABLE pages (
		tx_wvdeepltranslate_content_not_checked tinyint,
		tx_wvdeepltranslate_translated_time int(10) NOT NULL DEFAULT 0
);
