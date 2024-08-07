..  include:: /Includes.rst.txt

..  _housekeeping`:

Housekeeping
============

Three CLI commands are available for Cleanup, Sync and Overview.

Overview
--------

To get an overview of how many glossaries are registered with DeepL, you can use
the following:

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:list

This will give you an overview of the API connected glossaries, number of
entries, creation date and Glossary DeepL ID.

Cleanup
-------

Due to sync failures, it is useful to delete all DeepL glossaries.

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:cleanup --all

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:cleanup --glossaryId 123-123

This command retrieves information about all glossaries or one glossary registered
in the DeepL API and deletes them from the API. In addition, each glossary ID is
checked against the database and if found, the database record is updated.

The command then checks the local database to see if any glossaries still have
sync information, and cleans them up too.

At the end you will get a table with all deleted glossary IDs and the information
if the database has been updated with this glossary.

This command does not delete your glossaries in TYPO3.

After this, you are able to sync your glossaries with DeepL again.

..  _sync-cli:

Synchronisation
---------------

Synchronisation is performed by CLI command or as a scheduled task (as configured
CLI command).

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:sync

Accepts pageId as option. If not given, syncs all available glossaries.

..  _typo3_console: https://extensions.typo3.org/extension/typo3_console
