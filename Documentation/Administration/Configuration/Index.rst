..  include:: /Includes.rst.txt

..  _configuration:

Configuration
=============

Set up API and formality
------------------------

.. attention::
    Before using the DeepL API, you need to get an API key from your `DeepL Profile`_.

Go to :guilabel:`Admin Tools > Settings > Extension Configuration`.

Open the settings for :guilabel:`wv_deepltranslate` and add your API key.

.. figure:: /Images/Reference/configuration.png
    :alt: The Extension configuration settings showing two input fields for DeepL API key and default formality

The extension is set up to auto-detect the corresponding DeepL API URL.

.. _sitesetup:
Set up translation language
---------------------------

#. Go to :guilabel:`Site Management > Sites` and edit your site configuration
#. Switch to tab `Languages` and open your target
    .. figure:: /Images/Administration/site-config-deepl-settings-empty.png
        :alt: Site settings for a TYPO3 language showing empty DeepL Target Language dropdown
#. Go to :guilabel:`DeepL Settings` and set up your `Target Language (ISO Code)`
    .. figure:: /Images/Administration/site-config-selected-target.png
        :alt: Selected target now set to German

.. note::
    Although the drop-down list can also be set in the default language, there is
    no point in defining a target language for the source language.

Configure tables
----------------

If not default set, you need to define the `l10n_mode` for the fields you want to
have translatable by `wv_deepltranslate`.

See the :ref:`tableConfiguration<table configuration>` for details.

Detecting target language
-------------------------

The following chain tries to detect the language to translate into:

#.  Set up DeepL Translation language in SiteConfiguration
    * Target languages detected from DeepL will only appear
#.  Check hreflang against DeepL supported languages
    * Needed for detecting EN-GB, EN-US, PT-PT or PT-BR
#.  Fallback to Language ISO code

For currently allowed languages see the `DeepL conform language key`_. As this
extension retrieves available languages from the API, translations are restricted
to the languages listed in the official DeepL API documentation.

If none of these match against DeepL API, translation for this language
is disabled for usage within DeepL. Translation buttons and dropdowns
respect this setting.

..  _DeepL conform language key: https://developers.deepl.com/docs/api-reference/languages
.. _DeepL Profile: https://www.deepl.com/en/your-account/keys
