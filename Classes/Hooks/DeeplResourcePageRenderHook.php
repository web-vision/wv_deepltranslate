<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

class DeeplResourcePageRenderHook
{
    /**
     * Execute PreRenderHook for possible manipulation:
     * Add deepl.css and add custom javascript for recordlist
     *
     * @param array[] $hook
     */
    public function executePreRenderHook(array &$hook): void
    {
        //assets are only needed in BE context
        if (TYPO3_MODE !== 'BE') {
            return;
        }

        $hook['cssFiles']['EXT:wv_deepltranslate/Resources/Public/Css/deepl-min.css'] = [
            'rel' => 'stylesheet',
            'media' => 'all',
            'title' => '',
            'compress' => true,
            'forceOnTop' => false,
            'allWrap' => '',
            'excludeFromConcatenation' => false,
            'splitChar' => '|',
        ];

        //inline js for adding deepl button on records list.
        $deeplButton = "function deeplTranslate(a,b){ $('#deepl-translation-enable-' + b).parent().parent().siblings().each(function() { var testing = $( this ).attr( 'href' ); if(document.getElementById('deepl-translation-enable-' + b).checked == true){ var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } else { var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } }); }";
        if (isset($hook['jsInline']['RecordListInlineJS']['code'])) {
            $hook['jsInline']['RecordListInlineJS']['code'] .= $deeplButton;
        } else {
            $hook['jsInline']['RecordListInlineJS']['code'] = $deeplButton;
        }
    }
}
