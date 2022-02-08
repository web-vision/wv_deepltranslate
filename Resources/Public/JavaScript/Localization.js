/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/Backend/Localization
 * UI for localization workflow.
 */
define('TYPO3/CMS/Backend/Localization', [
    'jquery',
    'TYPO3/CMS/Backend/AjaxDataHandler',
    'TYPO3/CMS/Backend/Wizard',
    'TYPO3/CMS/Backend/Icons',
    'TYPO3/CMS/Backend/Severity',
    'bootstrap',
], function ($, DataHandler, Wizard, Icons, Severity) {
    'use strict'

    /**
     * @type {{identifier: {triggerButton: string}, actions: {translate: $, copy: $}, settings: {}, records: []}}
     * @exports TYPO3/CMS/Backend/Localization
     */
    var Localization = {
        identifier: {
            triggerButton: '.t3js-localize',
        },
        actions: {
            translate: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-translate',
            })
                .html('<br>Translate')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_translate',
                        value: 'localize',
                        style: 'display: none',
                    }),
                ),
            copy: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-copy',
            })
                .html('<br>Copy')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_copy',
                        value: 'copyFromLanguage',
                        style: 'display: none',
                    }),
                ),
            deepltranslate: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-translate',
            })
                .html('<br>Translate<br>(deepl)')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_deepltranslate',
                        value: 'localizedeepl',
                        style: 'display: none',
                    }),
                ),
            deepltranslateAuto: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-translate',
            })
                .html('<br>Translate<br>(deepl)<br>(autodetect)')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_deepltranslateauto',
                        value: 'localizedeeplauto',
                        style: 'display: none',
                    }),
                ),
            googletranslateAuto: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-translate',
            })
                .html('<br>Translate<br>(Google)<br>(autodetect)')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_googletranslateauto',
                        value: 'localizegoogleauto',
                        style: 'display: none',
                    }),
                ),
            googletranslate: $('<label />', {
                class: 'btn btn-block btn-default t3js-option',
                'data-helptext': '.t3js-helptext-translate',
            })
                .html('<br>Translate<br>(Google)')
                .prepend(
                    $('<input />', {
                        type: 'radio',
                        name: 'mode',
                        id: 'mode_googletranslate',
                        value: 'localizegoogle',
                        style: 'display: none',
                    }),
                ),
        },
        settings: {},
        records: [],
        labels: {
            deeplSettingsFailure: 'Please complete missing deepl configurations.',
        },
    }

    Localization.initialize = function () {
        Icons.getIcon('actions-localize', Icons.sizes.large).done(function (
            localizeIconMarkup,
        ) {
            Icons.getIcon('actions-edit-copy', Icons.sizes.large).done(function (
                copyIconMarkup,
            ) {
                Icons.getIcon('actions-localize-deepl', Icons.sizes.large).done(
                    function (localizeDeeplIconMarkup) {
                        Icons.getIcon('actions-localize-deepl', Icons.sizes.large).done(
                            function (localizeDeeplIconMarkup) {
                                Icons.getIcon(
                                    'actions-localize-google',
                                    Icons.sizes.large,
                                ).done(function (localizeGoogleIconMarkup) {
                                    Icons.getIcon(
                                        'actions-localize-google',
                                        Icons.sizes.large,
                                    ).done(function (localizeGoogleIconMarkup) {
                                        Localization.actions.translate.prepend(localizeIconMarkup)
                                        Localization.actions.copy.prepend(copyIconMarkup)
                                        Localization.actions.deepltranslate.prepend(
                                            localizeDeeplIconMarkup,
                                        )
                                        Localization.actions.deepltranslateAuto.prepend(
                                            localizeDeeplIconMarkup,
                                        )
                                        Localization.actions.googletranslate.prepend(
                                            localizeGoogleIconMarkup,
                                        )
                                        Localization.actions.googletranslateAuto.prepend(
                                            localizeGoogleIconMarkup,
                                        )
                                        $(Localization.identifier.triggerButton).removeClass(
                                            'disabled',
                                        )
                                    })
                                })
                            },
                        )
                    },
                )
            })
        })

        $(document).on('click', Localization.identifier.triggerButton, function () {
            var $triggerButton = $(this),
                actions = [],
                slideStep1 = ''

            if ($triggerButton.data('allowTranslate')) {
                actions.push(
                    '<div class="row">' +
                    '<div class="btn-group col-sm-3">' +
                    Localization.actions.translate[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9">' +
                    '<p class="t3js-helptext t3js-helptext-translate text-muted">' +
                    TYPO3.lang['localize.educate.translate'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
                actions.push(
                    '<div class="row" id="deeplTranslateAuto">' +
                    '<div class="btn-group col-sm-3">' +
                    Localization.actions.deepltranslateAuto[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9" id="deeplTextAuto">' +
                    '<p class="t3js-helptext t3js-helptext-translate text-muted">' +
                    TYPO3.lang['localize.educate.deepltranslateAuto'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
                actions.push(
                    '<div class="row" id="deeplTranslate">' +
                    '<div class="btn-group col-sm-3">' +
                    Localization.actions.deepltranslate[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9" id="deeplText">' +
                    '<p class="t3js-helptext t3js-helptext-translate text-muted">' +
                    TYPO3.lang['localize.educate.deepltranslate'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
                actions.push(
                    '<div class="row" id="googleTranslate">' +
                    '<div class="btn-group col-sm-3">' +
                    Localization.actions.googletranslate[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9" id="googleText">' +
                    '<p class="t3js-helptext t3js-helptext-translate text-muted">' +
                    TYPO3.lang['localize.educate.googleTranslate'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
                actions.push(
                    '<div class="row" id="googleTranslateAuto">' +
                    '<div class="btn-group col-sm-3">' +
                    Localization.actions.googletranslateAuto[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9" id="googleTextAuto">' +
                    '<p class="t3js-helptext t3js-helptext-translate text-muted">' +
                    TYPO3.lang['localize.educate.googleTranslateAuto'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
            }

            if ($triggerButton.data('allowCopy')) {
                actions.push(
                    '<div class="row">' +
                    '<div class="col-sm-3 btn-group">' +
                    Localization.actions.copy[0].outerHTML +
                    '</div>' +
                    '<div class="col-sm-9">' +
                    '<p class="t3js-helptext t3js-helptext-copy text-muted">' +
                    TYPO3.lang['localize.educate.copy'] +
                    '</p>' +
                    '</div>' +
                    '</div>',
                )
            }

            slideStep1 +=
                '<div data-toggle="buttons">' + actions.join('<hr>') + '</div>'

            Wizard.addSlide(
                'localize-choose-action',
                TYPO3.lang['localize.wizard.header']
                    .replace('{0}', $triggerButton.data('colposName'))
                    .replace('{1}', $triggerButton.data('languageName')),
                slideStep1,
                Severity.info,
            )
            Wizard.addSlide(
                'localize-choose-language',
                TYPO3.lang['localize.view.chooseLanguage'],
                '',
                Severity.info,
                function ($slide) {
                    Icons.getIcon('spinner-circle-dark', Icons.sizes.large).done(
                        function (markup) {
                            $slide.html($('<div />', { class: 'text-center' }).append(markup))
                            Localization.loadAvailableLanguages(
                                $triggerButton.data('pageId'),
                                $triggerButton.data('languageId'),
                                Localization.settings.mode,
                            ).done(function (result) {
                                if (result.length === 1) {
                                    // We only have one result, auto select the record and continue
                                    Localization.settings.language = result[0].uid + '' // we need a string
                                    Wizard.unlockNextStep().trigger('click')
                                    return
                                }

                                var $languageButtons = $('<div />', {
                                    class: 'row',
                                    'data-toggle': 'buttons',
                                })

                                $.each(result, function (_, languageObject) {
                                    $languageButtons.append(
                                        $('<div />', {
                                            class: 'col-sm-4',
                                            style: 'margin-top: 6px;',
                                        }).append(
                                            $('<label />', {
                                                class: 'btn btn-default btn-block t3js-option option',
                                            })
                                                .text(' ' + languageObject.title)
                                                .prepend(languageObject.flagIcon)
                                                .prepend(
                                                    $('<input />', {
                                                        type: 'radio',
                                                        name: 'language',
                                                        id: 'language' + languageObject.uid,
                                                        value: languageObject.uid,
                                                        style: 'display: none;',
                                                    }),
                                                ),
                                        ),
                                    )
                                })
                                $slide.html($languageButtons)
                            })
                        },
                    )
                },
            )
            Wizard.addSlide(
                'localize-summary',
                TYPO3.lang['localize.view.summary'],
                '',
                Severity.info,
                function ($slide) {
                    Icons.getIcon('spinner-circle-dark', Icons.sizes.large).done(
                        function (markup) {
                            $slide.html($('<div />', { class: 'text-center' }).append(markup))

                            Localization.getSummary(
                                $triggerButton.data('pageId'),
                                $triggerButton.data('languageId'),
                            ).done(function (result) {
                                //
                                $slide.empty()
                                Localization.records = []

                                var columns = result.columns.columns
                                var columnList = result.columns.columnList

                                columnList.forEach(function (colPos) {
                                    if (typeof result.records[colPos] === 'undefined') {
                                        return
                                    }

                                    var column = columns[colPos]
                                    var $row = $('<div />', { class: 'row' })

                                    result.records[colPos].forEach(function (record) {
                                        var label = ' (' + record.uid + ') ' + record.title
                                        Localization.records.push(record.uid)

                                        $row.append(
                                            $('<div />', { class: 'col-sm-6' }).append(
                                                $('<div />', { class: 'input-group' }).append(
                                                    $('<span />', { class: 'input-group-addon' }).append(
                                                        $('<input />', {
                                                            type: 'checkbox',
                                                            class: 't3js-localization-toggle-record',
                                                            id: 'record-uid-' + record.uid,
                                                            checked: 'checked',
                                                            'data-uid': record.uid,
                                                            'aria-label': label,
                                                        }),
                                                    ),
                                                    $('<label />', {
                                                        class: 'form-control',
                                                        for: 'record-uid-' + record.uid,
                                                    })
                                                        .text(label)
                                                        .prepend(record.icon),
                                                ),
                                            ),
                                        )
                                    })

                                    $slide.append(
                                        $('<fieldset />', {
                                            class: 'localization-fieldset',
                                        }).append(
                                            $('<label />')
                                                .text(column)
                                                .prepend(
                                                    $('<input />', {
                                                        class: 't3js-localization-toggle-column',
                                                        type: 'checkbox',
                                                        checked: 'checked',
                                                    }),
                                                ),
                                            $row,
                                        ),
                                    )
                                })
                                Wizard.unlockNextStep()

                                Wizard.getComponent()
                                    .on(
                                        'change',
                                        '.t3js-localization-toggle-record',
                                        function () {
                                            var $me = $(this),
                                                uid = $me.data('uid'),
                                                $parent = $me.closest('fieldset'),
                                                $columnCheckbox = $parent.find(
                                                    '.t3js-localization-toggle-column',
                                                )

                                            if ($me.is(':checked')) {
                                                Localization.records.push(uid)
                                            } else {
                                                var index = Localization.records.indexOf(uid)
                                                if (index > -1) {
                                                    Localization.records.splice(index, 1)
                                                }
                                            }

                                            var $allChildren = $parent.find(
                                                '.t3js-localization-toggle-record',
                                            )
                                            var $checkedChildren = $parent.find(
                                                '.t3js-localization-toggle-record:checked',
                                            )

                                            $columnCheckbox.prop(
                                                'checked',
                                                $checkedChildren.length > 0,
                                            )
                                            $columnCheckbox.prop(
                                                'indeterminate',
                                                $checkedChildren.length > 0 &&
                                                $checkedChildren.length < $allChildren.length,
                                            )

                                            if (Localization.records.length > 0) {
                                                Wizard.unlockNextStep()
                                            } else {
                                                Wizard.lockNextStep()
                                            }
                                        },
                                    )
                                    .on(
                                        'change',
                                        '.t3js-localization-toggle-column',
                                        function () {
                                            var $me = $(this),
                                                $children = $me
                                                    .closest('fieldset')
                                                    .find('.t3js-localization-toggle-record')

                                            $children.prop('checked', $me.is(':checked'))
                                            $children.trigger('change')
                                        },
                                    )
                                //
                            })
                        },
                    )
                },
            )
            Wizard.addFinalProcessingSlide(function () {
                Localization.localizeRecords(
                    $triggerButton.data('pageId'),
                    $triggerButton.data('languageId'),
                    Localization.records,
                ).done(function (result) {
                    if (result.length > 0) {
                        var response = JSON.parse(result)
                        var divFinalSlide = $(
                            "div[data-slide='final-processing-slide']",
                            window.parent.document,
                        )
                        divFinalSlide.append(
                            "<div class='alert alert-danger' id='alertClose'>  <a href='#'' class='close'  data-dismiss='alert' aria-label='close'>&times;</a>" +
                            response.message +
                            '</div>',
                        )
                        $(divFinalSlide)
                            .fadeTo(4500, 500)
                            .slideUp(500, function () {
                                Wizard.dismiss()
                                document.location.reload()
                            })
                    } else {
                        Wizard.dismiss()
                        document.location.reload()
                    }
                })
            }).done(function () {
                Wizard.show()

                Wizard.getComponent().on('click', '.t3js-option', function (e) {
                    var $me = $(this),
                        $radio = $me.find('input[type="radio"]')

                    if ($me.data('helptext')) {
                        var $container = $(e.delegateTarget)
                        $container.find('.t3js-helptext').addClass('text-muted')
                        $container.find($me.data('helptext')).removeClass('text-muted')
                    }
                    if ($radio.length > 0) {
                        if (
                            $radio.val() == 'localizedeepl' ||
                            $radio.val() == 'localizedeeplauto'
                        ) {
                            //checkdeepl settings
                            Localization.deeplSettings(
                                $triggerButton.data('pageId'),
                                $triggerButton.data('languageId'),
                                Localization.records,
                            ).done(function (result) {
                                var responseDeepl = JSON.parse(result)
                                if (responseDeepl.status == 'false') {
                                    if ($radio.val() == 'localizedeepl') {
                                        var divDeepl = $('#deeplText', window.parent.document)
                                    } else {
                                        var divDeepl = $('#deeplTextAuto', window.parent.document)
                                    }
                                    divDeepl.prepend(
                                        "<div class='alert alert-danger' id='alertClose'>  <a href='#'' class='close'  data-dismiss='alert' aria-label='close'>&times;</a>" +
                                        Localization.labels.deeplSettingsFailure +
                                        '</div>',
                                    )
                                    var deeplText = $('#alertClose', window.parent.document)
                                    $(deeplText)
                                        .fadeTo(1600, 500)
                                        .slideUp(500, function () {
                                            $(deeplText).alert('close')
                                        })
                                    Wizard.lockNextStep()
                                }
                            })
                        }
                        Localization.settings[$radio.attr('name')] = $radio.val()
                    }
                    Wizard.unlockNextStep()
                })
            })
        })

        /**
         * deeplSettings
         * @param {Integer} pageId
         * @param {Integer} languageId
         * @param {Array} uidList
         * @return {Promise}
         */
        Localization.deeplSettings = function (pageId, languageId, uidList) {
            return $.ajax({
                url: TYPO3.settings.ajaxUrls['records_localizedeepl'],
                data: {
                    pageId: pageId,
                    srcLanguageId: Localization.settings.language,
                    destLanguageId: languageId,
                    action: Localization.settings.mode,
                    uidList: uidList,
                },
            })
        }

        /**
         * Load available languages from page and colPos
         *
         * @param {Integer} pageId
         * @param {Integer} colPos
         * @param {Integer} languageId
         * @return {Promise}
         */
        Localization.loadAvailableLanguages = function (pageId, languageId, mode) {
            return $.ajax({
                url: TYPO3.settings.ajaxUrls['page_languages'],
                data: {
                    pageId: pageId,
                    languageId: languageId,
                    mode: mode,
                },
            })
        }

        /**
         * Get summary for record processing
         *
         * @param {Integer} pageId
         * @param {Integer} colPos
         * @param {Integer} languageId
         * @return {Promise}
         */
        Localization.getSummary = function (pageId, languageId) {
            return $.ajax({
                url: TYPO3.settings.ajaxUrls['records_localize_summary'],
                data: {
                    pageId: pageId,
                    destLanguageId: languageId,
                    languageId: Localization.settings.language,
                },
            })
        }

        /**
         * Localize records
         *
         * @param {Integer} pageId
         * @param {Integer} languageId
         * @param {Array} uidList
         * @return {Promise}
         */
        Localization.localizeRecords = function (pageId, languageId, uidList) {
            return $.ajax({
                url: TYPO3.settings.ajaxUrls['records_localize'],
                data: {
                    pageId: pageId,
                    srcLanguageId: Localization.settings.language,
                    destLanguageId: languageId,
                    action: Localization.settings.mode,
                    uidList: uidList,
                },
            })
        }
    }

    $(Localization.initialize)

    return Localization
})
