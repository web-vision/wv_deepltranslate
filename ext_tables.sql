#
# Table structure for table 'tx_deepl_settings'
#
CREATE TABLE tx_deepl_settings (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    languages_assigned text,
    crdate int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid)
);