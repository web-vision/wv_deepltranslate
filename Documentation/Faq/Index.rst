..  include:: /Includes.rst.txt

..  _faq:

FAQ
===

My dropdown in the site configuration is empty
----------------------------------------------

This happens, if TYPO3 cached the request from DeepL API for allowed languages
but no API key was provided. In this case, clear your system cache in TYPO3.
Normally no cache files should be created, when no API key is provided.

If this step does take no effect, delete the cached files manually. The location
is the following:

* composer based installation
    `var/cache/data/wvdeepltranslate`
* legacy installation
    `typo3temp/var/cache/data/wvdeepltranslate`

After deleting the files in this directory and going to Site configuration, the
extension loads the cache again and the dropdown should have all translatable
language keys.

What will be the cost for DeepL API subscription?
-------------------------------------------------

You can find all the details regarding  DeepL API usage here:

*   https://www.deepl.com/pro-pricing.html
