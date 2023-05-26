# Changelog

## 3.0.4

* [BUGFIX] Access to undefined array keys in list view by @bigahuna in https://github.com/web-vision/wv_deepltranslate/pull/240
* [BUGFIX] Guard `LocalizationUtility::translate` with `(string)` cast by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/255
* [BUGFIX] Avoid undefined array key warning in `LocalizationController` by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/268
* [BUGFIX] Avoid undefined array key warning in `AllowLanguageSynchronizationHook` by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/246
* [BUGFIX] Determine correct Preview mode in TYPO3 v9 by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/270
* [BUGFIX] Determine glossary correct on current page by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/271

## 3.0.3

* [TASK] Set branch alias for 3.0 by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/200
* [TASK] Avoid `rowCount()` for select query by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/233
* [BUGFIX] Avoid doctrine/dbal `fetchAssociative()` by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/236
* [BUGFIX] Fix Problem with translation no glossary given by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/235

## 3.0.2

[BUGFIX] Glossary is not used by @calien666 in #218
[TASK] Change Ext Icon by @calien666 in #220

## 3.0.1

* [BUGFIX] Fix runtime deprecation notice by @peterkraume in https://github.com/web-vision/wv_deepltranslate/pull/210
* [BUGFIX] Fix missing configuration object for localization wizard by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/211
* [BUGFIX] Update ButtonBarHook.php by @bigahuna in https://github.com/web-vision/wv_deepltranslate/pull/213
* [BUGFIX] Exclude fields in upgrade wizards by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/215
* [TASK] Add auto detect source language by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/214
* [BUGFIX] Glossary entries by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/212

## 3.0.0

* [BUGFIX] Prevent TypeError by adding string cast before explode() by @spoonerWeb in https://github.com/web-vision/wv_deepltranslate/pull/126
* [BUGFIX] v11: Allow pages always getting localized in RecordList by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/142
* [BUGFIX] Hide DeepL controls for not supported languages by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/143
* [BUGFIX] Backend module is always loaded, even if setting is false by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/157
* [BUGFIX] Fix active old backend module condition type handling by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/161
* [BUGFIX] Require PHP version in ext_emconf.php by @andreasfernandez in https://github.com/web-vision/wv_deepltranslate/pull/171
* [BUGFIX] language translate button and select by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/140
* [BUGFIX] DeepL Translation for fields with behaviour->allowLanguageSynchronization by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/141
* [BUGFIX] Use correct POST body for submitting the glossary by @koehnlein in https://github.com/web-vision/wv_deepltranslate/pull/172
* [BUGFIX] Fix choose language step skip by @philip-hartmann in https://github.com/web-vision/wv_deepltranslate/pull/178
* [BUGFIX] decode html special characters by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/187
* [BUGFIX] Fix ext_emconf version by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/186
* [BUGFIX] Trim source and target strings by @LimeUwe in https://github.com/web-vision/wv_deepltranslate/pull/175
* [BUGFIX] errors/warnings with PHP 8.1 by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/155
* [TASK] Move html utility function by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/122
* [TASK] Update extension dev dependencies by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/123
* [TASK] Change behaviour of translation button by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/134
* [TASK] Translation Check/Flag for Page Properties by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/137
* [TASK] Code refactoring of Hooks and Language behaviour by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/138
* [TASK] add news extension as dev-require by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/136
* [DOC] Restructuring documentation by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/127
* [DOC] Documentation by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/149
* [TASK] Introduce GitHub actions powered workflow by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/147
* [TASK] Improve variable types by @koehnlein in https://github.com/web-vision/wv_deepltranslate/pull/169
* [TASK] Remove stripTag function in translateHook by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/163
* [TASK] Add conflicts to recordlist_thumbnail by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/156
* [TASK] Introduce issue templates by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/145
* [TASK] Use `core-testing-*` images from `ghcr.io` by @sbuerk in https://github.com/web-vision/wv_deepltranslate/pull/181
* [TASK] Add sponsor to Documentation by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/183
* [TASK] Make glossary sync more tolerant of outdated IDs by @koehnlein in https://github.com/web-vision/wv_deepltranslate/pull/173
* [TASK] Create docs translate prefix by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/184
* [TASK] Refactoring glossary handling by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/165
* [TASK] task template by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/190
* [TASK] Introduce Upgrade instructions by @calien666 in https://github.com/web-vision/wv_deepltranslate/pull/194
* [FEATURE] Rework and centralize api client operation by @NarkNiro in https://github.com/web-vision/wv_deepltranslate/pull/189

## 2.3.1

* [BUGFIX] Fix exception due to missing use statement by @sypets in #108

## 2.3.0

* [BUGFIX] Add php doc blocks by @spoonerWeb in #94
* [BUGFIX] Fix glossary sync persistance by @sypets in #100
* [BUGFIX] Fix undefined array key in DataHandlerHook by @sypets in #101
* [TASK] Run CGL fix by @sypets in #103
* [FEATURE] add supported languages automatically from API by @calien666 in #107

## v2.2.2

* [TASK] Update supported deepl api languages by @ayacoo in #64
* [BUGFIX] localization wizard with EXT:container by @achimfritz in #56
* [BUGFIX] do not try to log expected exception by @achimfritz in #80
* [TASK] #84 fix translate request return type
* [TASK] Set google-translate service deprecated
* [TASK] #89 Update pagerender hook method for css resources and set ext prefix
* [BUGFIX] #90 move inline settings js to requireJS

## v2.2.1

- Fix third party translations

## v2.2.0

- Glossary Feature (https://support.deepl.com/hc/en-us/sections/360005269340-Glossary)

## v2.0.1

- TYPO3 v11.5.5 compatibility added
- Fixed broken translation from list module

## v2.0.0

- TYPO3 v11 compatibility added
- An issue regarding translation of inline elements in container fixed (https://github.com/b13/container/issues/188)
- Added formality option into deepl settings (https://github.com/web-vision/wv_deepltranslate/issues/21)
