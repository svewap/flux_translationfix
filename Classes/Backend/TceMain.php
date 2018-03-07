<?php

namespace WapplerSystems\FluxTranslationfix\Backend;

use FluidTYPO3\Flux\Backend\TceMain as OriginalTceMain;
use FluidTYPO3\Flux\Service\ContentService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\DebugUtility;

class TceMain extends OriginalTceMain
{


    /**
     * Method to initialize the command processing map with a single purpose:
     * to re-sort any "swap" operations to put the operation happening to the
     * parent record, after all operations happening to child records, and
     * do so only for the tt_content table.
     *
     * @param DataHandler $reference
     * @return void
     */
    public function processCmdmap_beforeStart(&$reference)
    {
        parent::processCmdmap_beforeStart($reference);
        $this->contentService->fixOrderInLocalization($reference);
    }



    /**
     * Command post processing method
     *
     * Like other pre/post methods this method calls the corresponding
     * method on Providers which match the table/id(record) being passed.
     *
     * In addition, this method also listens for paste commands executed
     * via the TYPO3 clipboard, since such methods do not necessarily
     * trigger the "normal" record move hooks (which we also subscribe
     * to and react to in moveRecord_* methods).
     *
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param string $relativeTo Filled if command is relative to another element
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @param array $pasteUpdate
     * @param array $pasteDataMap
     * @return void
     */
    public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference, &$pasteUpdate, &$pasteDataMap)
    {
        parent::processCmdmap_postProcess($command, $table, $id, $relativeTo, $reference, $pasteUpdate,$pasteDataMap);

        if ($table === 'tt_content') {
            if ('localize' === $command) {

                $record = $this->resolveRecordForOperation($table, $id);

                $this->contentService->fixRelationInLocalization($id, $relativeTo, $record, $reference);
            }

        }
    }


}