.. include:: /Includes.rst.txt

.. _configuration:

Configuration
=============

Detecting target language
-------------------------

From TYPO3 11.5 LTS on the detection of the target language works as following:

#. Set up DeepL Translation language in SiteConfiguration
   * Target languages detected from DeepL will only appear
#. Check hreflang against DeepL supported languages
   * Needed for detecting EN-GB, EN-US, PT-PT or PT-BR
#. Fallback to Language ISO code

If none of these match against DeepL API, translation for this language
is disabled for usage within DeepL. Translation buttons and dropdowns
respect this setting.

.. _DeepL conform language key: https://www.deepl.com/de/docs-api/general/get-languages/
