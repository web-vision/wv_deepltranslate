..  include:: /Includes.rst.txt

..  _housekeeping`:

Housekeeping
============

For cleanup, sync and overview, three CLI commands are available.

Overview
--------

To get an overview, how many glossaries are registered to DeepL,
you can use:

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:list

This will give you an overview of API connected glossaries,
number of entries, creation date and Glossary DeepL ID.

Cleanup
-------

Due to sync failures it is useful, to delete all DeepL glossaries.

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:cleanup --all

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:cleanup --glossaryId 123-123

This command retrieves information about all glossaries or one glossary in DeepL API
registered and deletes them from API. Additionally, each glossary ID
is checked against the database and if found, the database record is
updated.

Then the command checks local database, if any glossary has sync
information left and cleans up, too.

At the end you will get a table with all deleted glossary IDs and
the information, if database was updated to this glossary.

Your glossaries in TYPO3 are not deleted with this command.

After this, you are able to sync your glossaries again to DeepL.

..  _sync-cli:

Synchronisation
---------------

Synchronisation is done by CLI command or as scheduled task (as configured
CLI Command).

..  code-block:: bash

    vendor/bin/typo3 deepl:glossary:sync

Accepts pageId as option. If not given, syncs all available glossaries.

..  _typo3_console: https://extensions.typo3.org/extension/typo3_console
