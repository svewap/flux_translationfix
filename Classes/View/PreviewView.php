<?php
namespace WapplerSystems\FluxTranslationfix\View;


use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use FluidTYPO3\Flux\View\PreviewView as OriginalPreviewView;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


class PreviewView extends OriginalPreviewView
{



    /**
     * @param array $row
     * @param Column $column
     * @return string
     */
    protected function drawGridColumn(array $row, Column $column)
    {
        $colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;

        $columnName = $column->getName();
        $dblist = $this->getInitializedPageLayoutView($row);
        $this->configurePageLayoutViewForLanguageMode($dblist);
        $records = $this->getRecords($dblist, $row, $columnName);
        $content = '';
        if (is_array($records)) {
            foreach ($records as $record) {
                $content .= $this->drawRecord($row, $column, $record, $dblist);
            }
        }

        // Add localize buttons for flux container elements
        if (isset($row['l18n_parent']) && 0 < $row['l18n_parent']) {
            if (true === empty($dblist->defLangBinding)) {
                $partialOriginalRecord = ['uid' => $row['l18n_parent'], 'pid' => $row['pid']];
                $childrenInDefaultLanguage = $this->getRecords($dblist, $partialOriginalRecord, $columnName);
                $childrenUids = [];
                foreach ($childrenInDefaultLanguage as $child) {
                    $childrenUids[] = $child['uid'];
                }
                $langPointer = $row['sys_language_uid'];
                $localizeButton = $dblist->newLanguageButton(
                    $dblist->getNonTranslatedTTcontentUids($childrenUids, $dblist->id, $langPointer),
                    $langPointer,
                    (count($childrenInDefaultLanguage) > 0) ? $colPosFluxContent : null
                );
                $content .= $localizeButton;
            }
        }
        $pageUid = $row['pid'];
        if ($GLOBALS['BE_USER']->workspace) {
            $placeholder = BackendUtility::getMovePlaceholder('tt_content', $row['uid'], 'pid');
            if ($placeholder) {
                $pageUid = $placeholder['pid'];
            }
        }
        $id = 'colpos-' . $colPosFluxContent . '-page-' . $pageUid . '--top-' . $row['uid'] . '-' . $columnName;
        $target = $this->registerTargetContentAreaInSession($row['uid'], $columnName);

        $column->setColumnPosition($colPosFluxContent);

        return $this->parseGridColumnTemplate($row, $column, $target, $id, $content);
    }



    /**
     * @param array $row
     * @param Column $column
     * @param string $target
     * @param string $id
     * @param string $content
     * @return string
     */
    protected function parseGridColumnTemplate(
        array $row,
        Column $column,
        $target,
        $id,
        $content
    ) {
        // this variable defines if this drop-area gets activated on drag action
        // of a ce with the same data-language_uid
        $templateClassJsSortableLanguageId = $row['sys_language_uid'];

        // this variable defines which drop-areas will be activated
        // with a drag action of this element
        $templateDataLanguageUid = $row['sys_language_uid'];

        // but for language mode all (uid -1):
        if ((integer) $row['sys_language_uid'] === -1) {
            /** @var PageLayoutController $pageLayoutController */
            $pageLayoutController = $GLOBALS['SOBE'];
            $isColumnView = ((integer) $pageLayoutController->MOD_SETTINGS['function'] === 1);
            $isLanguagesView = ((integer) $pageLayoutController->MOD_SETTINGS['function'] === 2);
            if ($isColumnView) {
                $templateClassJsSortableLanguageId = $pageLayoutController->current_sys_language;
                $templateDataLanguageUid = $pageLayoutController->current_sys_language;
            } elseif ($isLanguagesView) {
                // If this is a language-all (uid -1) grid-element in languages-view
                // we use language-uid 0 for this elements drop-areas.
                // This can be done because a ce with language-uid -1 in languages view
                // is in TYPO3 7.6.4 only displayed in the default-language-column (maybe a bug atm.?).
                // Additionally there is no access to the information which
                // language column is currently rendered from here!
                // ($lP in typo3/cms/typo3/sysext/backend/Classes/View/PageLayoutView.php L485)
                $templateClassJsSortableLanguageId = 0;
                $templateDataLanguageUid = 0;
            }
        }

        $label = $column->getLabel();
        if (strpos($label, 'LLL:EXT') === 0) {
            $label = LocalizationUtility::translate($label, $column->getExtensionName());
        }

        $pageUid = $row['pid'];
        if ($GLOBALS['BE_USER']->workspace) {
            $placeholder = BackendUtility::getMovePlaceholder('tt_content', $row['uid'], 'pid');
            if ($placeholder) {
                $pageUid = $placeholder['pid'];
            }
        }

        return sprintf(
            $this->templates['gridColumn'],
            $column->getColspan(),
            $column->getRowspan(),
            $column->getStyle(),
            $label,
            $target,
            $templateClassJsSortableLanguageId,
            $templateDataLanguageUid,
            $pageUid,
            $id,
            $this->hasConnectedTranslationMode(PageLayoutView::$staticLanguageHasTranslationsCache,$templateDataLanguageUid) ? '' : $this->drawNewIcon($row, $column),
            CompatibilityRegistry::get(static::class . '->drawPasteIcon') ? $this->drawPasteIcon($row, $column) : '',
            $this->drawPasteIcon($row, $column, true),
            $content
        );
    }


    private function hasConnectedTranslationMode($staticLanguageHasTranslationsCache, $languageUid)
    {
        return $staticLanguageHasTranslationsCache[$languageUid]['mode'] === 'connected';
    }


}