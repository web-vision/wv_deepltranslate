..  include:: /Includes.rst.txt

.. _administration-autotranslateprefix:

Auto-translate-prefix
=====================

To enable tagging for Automatically Translated Pages and Content
the page activation of translated pages was extended in order to implement a control
possibility to implement.

This information is passed in the Page Context Fluid template and can be used to
and can be used there to enable a page specific appearance.

.. figure:: /Images/Editor/AutoTranslatePrefix/page-translation-prefix.png
    :alt: Page Frontend


----


To make this easy can also be used in the extension Partial.

..  code-block:: html

    <f:if condition="{page.tx_wvdeepltranslate_content_not_checked}" >
        <div style="background: #006494; border: #0000cc 1px solid; color: #fff; padding: 10px; text-align: center">
            <f:translate key="LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:preview.flag" />
            <f:if condition="{page.tx_wvdeepltranslate_translated_time} > 0" >
                <f:format.date format="{dateFormat}">{page.tx_wvdeepltranslate_translated_time}</f:format.date>
            </f:if>
        </div>
    </f:if>
