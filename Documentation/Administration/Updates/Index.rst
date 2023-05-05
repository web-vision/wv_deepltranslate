.. include:: /Includes.rst.txt

.. _updates:

Updates
=======

Version 2.x > 3.x
-----------------

.. note:: This Upgrade is only needed, if you are using glossary functionality.

Run the Upgrade wizard shipped with version 3. The wizard only appears, if necessary:

.. figure:: /Images/Administration/upgrade-wizard-v3.png
    :alt: Screenshot ob backend Upgrade wizard

This wizard moves your glossaries to the new structure, fixes backend group
rights and changes the module name.

After this, you have to run a GlossarySync update, either by CLI or by backend

#. :ref:`sync-cli`
#. :ref:`glossaries`
