.. include:: /Includes.rst.txt

.. _extensionConfiguration:

=======================
Extension Configuration
=======================

Some general settings can be configured in the Extension Configuration.

#. Go to :guilabel:`Admin Tools > Settings > Extension Configuration`
#. Choose :guilabel:`wv_deepltranslate`

.. image:: /Images/Reference/configuration.png
    :alt: Screenshot of Extension configuration

.. _deeplApiKey:

DeepL API Key
=============

.. confval:: apiKey

    :type: string

    Add your DeepL API Key here.

.. confval:: apiUrl (deprecated)

    :type: string
    :Default: https://api.deepl.com/v2/translate

    The API URL used for DeepL translations.
    One of the following allowed:

    https://api-free.deepl.com/v2/translate
        The free API. This free DeepL API Key includes 500.000 words per month.
        You can register for new `DeepL Free API`_

    https://api.deepl.com/v2/translate
        Payed API. If you need more than the 500.000 words per month,
        register and buy a package at `DeepL Pro`_

.. confval:: deeplFormality

    :type: string

    Sets whether the translated text should lean towards formal or informal language.
    Possible options:

    default
        The default setting. If formal or informal depends on the language

    less
        Less formal language. Will fail, if no formality support for language

    more
        More formal language. Will fail, if no formality support for language

    prefer_less
        Use less formal language, if possible, otherwise fallback to default

    prefer_more
        Use more formal language, if possible, otherwise fallback to default

.. confval:: googleapiKey (deprecated)

    :type: string

    The API key for Google translation.

.. confval:: googleapiUrl (deprecated)

    :type: string

    The URL providing Google translation API. Normally you don't need to change this.


.. _DeepL Free API: https://www.deepl.com/pro-checkout/account?productId=1200&yearly=false&trial=false
.. _DeepL Pro: https://www.deepl.com/de/pro
