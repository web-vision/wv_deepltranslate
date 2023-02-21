.. include:: /Includes.rst.txt

Glossaries
==========

You are able to define glossaries for your translation. Each glossary
has a name, a source language and a target language defined.
Selects are in sync with DeepL API and provide only source and
target pairs available from DeepL.

.. figure:: /Images/Editor/glossaries-list-view.png
    :alt: List view of glossary items

On pages with Doktype 254 (Folder) and "Use Container" to "DeepL Glossar" set,
a synchronise button appears to easy sync all glossaries listed in this page.

Each glossary shows you the number of entries inside and the current sync
status to DeepL API.

.. figure:: /Images/Editor/glossary-list-not-synced.png
    :alt: List view with not synced glossary

Edit glossary and save. Each entry is a simple source -> target match.

.. figure:: /Images/Editor/glossary-entries-list-edit.png
    :alt: Edit view of glossary

In Tab **DeepL Sync** you will retrieve current sync information of this glossary
to API.

.. note::

    Currenty **NO** automatic sync is done on save. Save and trigger sync
    manually.

.. figure:: /Images/Editor/glossary-sync-tab-not-synced.png
    :alt: Tab "DeepL Sync" with information

In each glossary a button is enabled to sync only this glossary.

After sync the tab *DeepL Sync* should look like this:

.. figure:: /Images/Editor/glossary-sync-tab-synced.png
    :alt: Tab "DeepL Sync" with set ID, time of last sync and ready status
