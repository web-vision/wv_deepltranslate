.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users manual
============


Installation
---------------
* You can install the extension using extension manager.
* Once installed ,there appears a Deepl back end module with a settings tab.

Requirements
-------------------
* Typo3 9.5 to  9.5.xx

Extension Configuartion
--------------------------------
* Once you installed the extension, you have to set the Deepl API Key under extension configuration section

.. figure:: ../Images/UserManual/configuration.png
    :width: 700px
    :alt: Extension configuration


Translating content elements
---------------------------------------
Once the extension is installed and Api key provided we are good to go for translating content elements.On translating content element,There appears additional four options apart from normal tranlate and copy.

* Deepl Translate(auto detect).
* Deepl Translate.
* Google Translate(auto detect).
* Google Translate.



.. figure:: ../Images/UserManual/deepl.png
    :height: 450px
    :alt: Deepl Options


    Deepl translate options

.. figure:: ../Images/UserManual/google.png
    :height: 450px
    :alt: Google Options


    Google translate options

Deepl Module Settings
-------------------------------

The settings module helps to assign the sytem languages to either deepl supported languages or to Google supported languages.

For example you can assign German to Austrian German sys language if you wish. For assigning a language to a sys language you must enter it's isocode(ISO 639-1).

.. figure:: ../Images/UserManual/settings.png
    :width: 800px
    :alt: Settings


    Module Settings

Translating TCA Records
---------------------------------------
Deepltranslate supports translation of specific fields of TCA records.It understands fields which need to be translated, only if their ``l10n_mode`` is set to ``prefixLangTitle``.

For example if you need translation of some fields of ``tx_news`` (say ``teaser`` and ``bodytext``),You need to override those fields like follows:

Add it to TCA/Overrides: Example : ``typo3conf/ext/theme/Configuration/TCA/Overrides/tx_news_domain_model_news.php``

``$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['bodytext']['l10n_mode'] = 'prefixLangTitle';``

``$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['teaser']['l10n_mode'] = 'prefixLangTitle';``
