.. include:: /Includes.rst.txt

.. _configuration:

Configuration
=============

.. attention::

   This section is only needed for TYPO3 v9 and v10, as since v11 all
   translation configuration is done in the SiteConfiguration instead.

This extension ships a backend module for configuring translations.
Inside the backend module all available local languages are listed and you have
to assign a :ref:`DeepL conform language key <_DeepL>`

.. figure:: /Images/Administration/settings.png
    :width: 800px
    :alt: Settings

    Module Settings

.. _DeepL: https://www.deepl.com/de/docs-api/general/get-languages/


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
