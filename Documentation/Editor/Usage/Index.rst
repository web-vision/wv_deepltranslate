.. include:: /Includes.rst.txt

.. _basic-usage:

Basic Usage
============

Translating content elements
----------------------------

Once the extension is installed and API key provided, we are good to go for
translating content elements. On translating content element, there appears
additional four options apart from normal translate and copy.

* DeepL Translate(auto detect).
* DeepL Translate.
* Google Translate(auto detect).
* Google Translate.

.. figure:: /Images/Editor/deepl.png
    :height: 450px
    :alt: DeepL Options

    DeepL translate options

.. figure:: /Images/Editor/google.png
    :height: 450px
    :alt: Google Options

    Google translate options


Translating a page
------------------

*wv_deepltranslate* adds a separate dropdown for DeepL translation of a page
to list and web module. The dropdown is filtered to not translated pages
and against DeepL API possible translation languages.

.. figure:: /Images/Editor/translation-dropdown.png
    :alt: Dropdown for DeepL translation

Translating a single element
----------------------------

In list view, you are able to translate single elements by clicking the DeepL
translate button for the language you want.

.. figure:: /Images/Editor/translation-buttons-page.png
    :alt: Translation buttons in list view

Languages not available will have no DeepL button. In this case use normal
translation.

.. figure:: /Images/Editor/translation-button-news.png
    :alt: Translation button for tx_news, one language not available in DeepL
