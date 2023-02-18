CREATE TABLE tx_deepl_settings (
		languages_assigned text,
);

CREATE TABLE tx_wvdeepltranslate_domain_model_glossaries (
		term varchar(255),
		description text,
);

CREATE TABLE tx_wvdeepltranslate_domain_model_glossariessync (
		glossary_id varchar(60),
		source_lang char(2),
		target_lang char(2),
		entries text,
);

CREATE TABLE pages (
		tx_wvdeepltranslate_content_not_checked tinyint,
		tx_wvdeepltranslate_translated_time int(10) NOT NULL DEFAULT 0
);
