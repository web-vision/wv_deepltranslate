
## What does it do?
This extension provides option to translate content elements and tca record fields to desired language(supported by deepl). As a fallback, Google translate option is also provided as they provide support for many languages that deepl isn’t providing.

For both Deepl translate and Google translate, there are two modes-normal and autodetect, where the later autodetects source language and translates it to the desired language.

## Installation
You can install the extension using: 
- Extension manager or 
- composer  ``` composer req pits/deepltranslate ```

Once installed ,there appears a Deepl back end module with a settings tab.
## Requirements
- TYPO3 8.5 to 8.7.99 (V1.0.0 - 1.0.1)
- TYPO3 9.5.1 to 9.5.99 (V1.0.2 onwards)

## Extension Configuartion

Once you installed the extension, you have to set the Deepl API Key under extension configuration section


## Translating content elements

Once the extension is installed and Api key provided we are good to go for translating content elements.On translating content element,There appears additional four options apart from normal tranlate and copy.

- Deepl Translate(auto detect).
- Deepl Translate.
- Google Translate(auto detect).
- Google Translate.

## Translating TCA records

Deepltranslate supports translation of specific fields of TCA records.It understands fields which need to be translated, only if their ``` l10n_mode ``` is set to ``` prefixLangTitle ```.

For example if you need translation of fields of tx_news (teaser and bodytext),You need to override those fields like follows:

Add it to TCA/Overrides: 
Example : ``` typo3conf/ext/theme/Configuration/TCA/Overrides/tx_news_domain_model_news.php ```

```
<?php

defined('TYPO3_MODE') or die();

$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['bodytext']['l10n_mode'] = 'prefixLangTitle';
$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['teaser']['l10n_mode'] = 'prefixLangTitle';

```

## Deepl Module Settings
The settings module helps to assign the sytem languages to either deepl supported languages or to Google supported languages.

For example you can assign German under Austrian German sys language if you wish. For assigning a language to a sys language you must enter it’s isocode(ISO 639-1).

## FAQ

See faq [here](https://docs.typo3.org/typo3cms/extensions/deepltranslate/Faq/Index.html) 
