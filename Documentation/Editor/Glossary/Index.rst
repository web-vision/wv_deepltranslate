.. include:: /Includes.rst.txt

.. _glossaries:

Glossaries
==========

You are able to define glossaries for your translation. Each glossary has a
name, a source language and a target language defined. The name is built by
the Page title and the target and source language.

.. figure:: /Images/Editor/glossaries-list-view.png
    :alt: List view of glossary items

On pages with Doktype 254 (Folder) and "Use Container" to "DeepL Glossar" set,
a synchronise button appears to easy sync the glossary terms listed in this page.

Possible glossary combinations in multiple language translation modes are built
on the fly, so your glossary can be used from every target to source, **except**
the default system language.

Each glossary shows you the current sync status to DeepL API in page settings.

.. figure:: /Images/Editor/glossary-sync-tab-not-synced.png
    :alt: Page settings tab *DeepL Translate* with not synced glossary

Adding terms is done by list module.

.. figure:: /Images/Editor/glossary-add-term.png
    :alt: Add entry via add record

To generate a glossary translation, simply translate the page via TYPO3
translation dropdown to your needed glossary language. A custom translation
dropdown is shown, which only takes languages, where glossary entries can be
built to.

.. figure:: /Images/Editor/glossary-page-translation.png
    :alt: Custom translation dropdown in list view

.. figure:: /Images/Editor/glossary-page-translation-select.png
    :alt: Possible translations based on source language EN

After that, you are able to make translations with the *Translate to* button.
As the glossary entries are made for not using DeepL standard wording, the
ability of translating entries by DeepL is disabled.

.. figure:: /Images/Editor/glossary-entry-list.png
    :alt: Backend list view of glossary entries, original language english, translated to German

In page settings, Tab **DeepL Translate** you will retrieve current sync
information of this glossary to API.

.. note::

    Currenty **NO** automatic sync is done on save. Save and trigger sync
    manually.

In each glossary directory a button is enabled to sync this glossary.

After sync the tab *DeepL Translate* should look like this:

.. figure:: /Images/Editor/glossary-sync-tab-synced.png
    :alt: Tab "DeepL Translate" with set ID, time of last sync and ready status
