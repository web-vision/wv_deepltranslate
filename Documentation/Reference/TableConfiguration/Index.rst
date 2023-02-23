.. include:: /Includes.rst.txt

.. _tableConfiguration:

===================
Table Configuration
===================

*wv_deepltranslate* supports translation of specific fields of TCA records. It
understands fields which need to be translated, only if their ``l10n_mode``
is set to ``prefixLangTitle``.

For detecting translatable fields, *wv_deepltranslate* uses a DataHandler hook.

The following setup is needed, to get *wv_deepltranslate* work on your table:

.. code-block:: php
    :caption: <extension_key>/Configuration/TCA/Overrides/<table_name>.php

    $GLOBALS['TCA']['<table_name>']['columns']['<field_name>']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['<table_name>']['columns']['<field_name>']['l10n_mode'] = 'prefixLangTitle';
