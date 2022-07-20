CREATE TABLE tx_deepl_settings (
		languages_assigned text,
);

CREATE TABLE tx_deepl_glossaries (
		definition varchar(255),
		term  varchar(255),
		description text,
);

CREATE TABLE tx_deepl_glossaries_sync (
		glossary_id varchar(60),
		source_lang char(2),
		target_lang char(2),
		entries text,
);
