..  include:: /Includes.rst.txt

.. _editor-autotranslateprefix:

Auto-translate-prefix
=====================

To enable tagging for Automatically Translated Page and Content
the Page Turned On of translated pages was extended to implement a control
possibility to implement.


.. figure:: /Images/Editor/AutoTranslatePrefix/page-translation-properties.png
    :alt: Page Property DeepL Translate

With each translation process of content the fields are updated.

----

The field information "Last translation date" and "DeepL Translated content has not been checked".
are always transferred to the page object and can be queried in Fluid.

Thus, information and notes in Fluid template can be controlled if necessary.
This must be added to the template by a TYPO3 administrator or developer beforehand.

.. figure:: /Images/Editor/AutoTranslatePrefix/page-translation-prefix.png
    :alt: Page Frontend prefix

When an editor previews DeepL translated hidden page,
a DeepL badge is displayed in addition to the "Preview" badge in the upper right corner.

.. figure:: /Images/Editor/AutoTranslatePrefix/page-translation-preview.png
    :alt: Page frontend preview
