..  include:: /Includes.rst.txt

..  _glossaries:

Glossaries
==========

You can define glossaries for your translations. Each glossary has a name, a source
language and a target language. The name is made up of the page title and the target
and source languages.

..  figure:: /Images/Editor/glossaries-list-view.png
    :alt: List view of glossary items

On pages with Doktype 254 (Folder) and "Use Container" set to "DeepL Glossary",
a synchronise button appears for easy synchronisation of the glossary terms listed in this page.

Possible glossary combinations in multiple language translation modes are built
on the fly, so your glossary can be used from any target to source, **except** the
default system language.

Each glossary shows you the current sync status to the DeepL API in the page settings.

..  figure:: /Images/Editor/glossary-sync-tab-not-synced.png
    :alt: Page settings tab *DeepL Translate* with not synced glossary

Adding terms is done through the list module.

..  figure:: /Images/Editor/glossary-add-term.png
    :alt: Add entry via add record

To generate a glossary translation, simply translate the page into the required
glossary language using the TYPO3 translation dropdown. A custom translation dropdown
will be displayed, which will only accept languages in which glossary entries can be created.

..  figure:: /Images/Editor/glossary-page-translation.png
    :alt: Custom translation dropdown in list view

..  figure:: /Images/Editor/glossary-page-translation-select.png
    :alt: Possible translations based on source language EN

After that you can make translations with the *Translate to* button.
As the glossary entries are made for not using DeepL standard wording, the
ability of translating entries by DeepL is disabled.

..  figure:: /Images/Editor/glossary-entry-list.png
    :alt: Backend list view of glossary entries, original language english, translated to German

You can retrieve the current sync information of this glossary to the API in the
page settings, tab **DeepL Translate**.

..  note::

    Current **NO** Automatic sync is performed on save. Save and trigger sync manually.

In each glossary directory, a button is enabled to synchronise that glossary.

After sync the tab *DeepL Translate* should look like this:

..  figure:: /Images/Editor/glossary-sync-tab-synced.png
    :alt: Tab "DeepL Translate" with set ID, time of last sync and ready status
