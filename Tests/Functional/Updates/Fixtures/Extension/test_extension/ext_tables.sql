CREATE TABLE tx_wvdeepltranslate_domain_model_glossaries
(
	uid              int unsigned auto_increment,
	pid              int unsigned      default 0  not null,
	tstamp           int unsigned      default 0  not null,
	crdate           int unsigned      default 0  not null,
	cruser_id        int unsigned      default 0  not null,
	deleted          smallint unsigned default 0  not null,
	hidden           smallint unsigned default 0  not null,
	sys_language_uid int               default 0  not null,
	l10n_parent      int unsigned      default 0  not null,
	l10n_source      int unsigned      default 0  not null,
	l10n_state       text                         null,
	l10n_diffsource  mediumblob                   null,
	starttime int unsigned      default 0 not null,
	endtime int unsigned      default 0 not null,

	term        varchar(255),
	description text,

	PRIMARY KEY (uid)
);

CREATE TABLE tx_wvdeepltranslate_domain_model_glossariessync
(
	uid         int unsigned auto_increment,
	pid         int unsigned      default 0 not null,
	tstamp      int unsigned      default 0 not null,
	crdate      int unsigned      default 0 not null,
	cruser_id   int unsigned      default 0 not null,
	deleted     smallint unsigned default 0 not null,

	glossary_id varchar(60),
	source_lang char(2),
	target_lang char(2),
	entries     text,

	PRIMARY KEY (uid)
);

