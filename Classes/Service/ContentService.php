<?php
namespace WapplerSystems\FluxTranslationfix\Service;


use FluidTYPO3\Flux\Service\ContentService as OriginalContentService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;


class ContentService extends OriginalContentService
{

    public function fixOrderInLocalization(DataHandler $reference)
    {

        if (\is_array($reference->cmdmap['tt_content'])) {
            $doLocalize = false;

            foreach ($reference->cmdmap['tt_content'] as $uid => $commands) {
                if (array_key_exists('localize', $commands)) {
                    $doLocalize = true;
                }
            }

            if ($doLocalize) {
                $uids = array_keys($reference->cmdmap['tt_content']);
                $newCmdmap = [];

                $items = [];
                foreach ($reference->cmdmap['tt_content'] as $uid => $commands) {
                    $items[$uid] = BackendUtility::getRecord('tt_content', $uid, 'uid,pid,tx_flux_parent');
                }
                foreach ($items as $uid => $item) {
                    if (!\in_array($item['tx_flux_parent'], $uids)) {
                        $newCmdmap[$uid] = $reference->cmdmap['tt_content'][$uid];
                        unset($reference->cmdmap['tt_content'][$uid]);
                    }
                }
                while (\count($reference->cmdmap['tt_content']) > 0) {
                    foreach ($items as $uid => $item) {
                        if (\in_array($item['tx_flux_parent'], $uids)) {
                            $newCmdmap[$uid] = $reference->cmdmap['tt_content'][$uid];
                            unset($reference->cmdmap['tt_content'][$uid]);
                        }
                    }
                }
                $reference->cmdmap['tt_content'] = $newCmdmap;
            }
        }
    }


    public function fixRelationInLocalization($uid, $languageUid, &$sourceRecord, DataHandler $reference)
    {

        $previousLocalizedRecordUid = $this->getPreviousLocalizedRecordUid($uid, $languageUid, $reference);

        if (!empty($sourceRecord['l18n_parent'])) {
            $defaultRecordUid = $sourceRecord['l18n_parent'];
        } else {
            $defaultRecordUid = $uid;
        }
        $localizedRecord = BackendUtility::getRecordLocalization('tt_content', $defaultRecordUid, $languageUid);

        $parentLocalizedRecord = BackendUtility::getRecordLocalization('tt_content', $localizedRecord[0]['tx_flux_parent'], $languageUid);


        $localizedRecord[0]['tx_flux_parent'] = $parentLocalizedRecord[0]['uid'];

        $this->updateRecordInDataMap($localizedRecord[0], null, $reference);

        $this->recordService->update('tt_content', $localizedRecord[0]);
    }

}