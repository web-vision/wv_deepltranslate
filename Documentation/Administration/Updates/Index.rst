..  include:: /Includes.rst.txt

..  _updates:

=======
Updates
=======

Version 3.x > 4.x
=================

If you are upgrading from 3.x on TYPO3 11 LTS to 12 LTS and you used site config
setup of translations, you simply can update.

Upgrade with Core Upgrade
-------------------------

If you upgrade fom TYPO3 below v11 you have to define the target languages in
the site configuration. See the :ref:`sitesetup<Site Setup section>` in this
documentation.

Version 2.x > 3.x
=================

..  note:: This Upgrade is only needed, if you are using glossary functionality.

Run the Upgrade wizard shipped with version 3. The wizard only appears, if necessary:

..  figure:: /Images/Administration/upgrade-wizard-v3.png
    :alt: Screenshot ob backend Upgrade wizard

This wizard moves your glossaries to the new structure, fixes backend group
rights and changes the module name.

After this, you have to run a GlossarySync update, either by CLI or by backend

#.  :ref:`sync-cli`
#.  :ref:`glossaries`
