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
