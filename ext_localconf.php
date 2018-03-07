<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


call_user_func(
    function ($extKey) {

        $configurationVariable = 'TYPO3_CONF_VARS';

        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $GLOBALS,
            [
                $configurationVariable => [
                    'SYS' => [
                        'Objects' => [
                            FluidTYPO3\Flux\Backend\TceMain::class => [
                                'className' => WapplerSystems\FluxTranslationfix\Backend\TceMain::class,
                            ],
                            FluidTYPO3\Flux\Service\ContentService::class => [
                                'className' => WapplerSystems\FluxTranslationfix\Service\ContentService::class,
                            ],
                            FluidTYPO3\Flux\View\PreviewView::class => [
                                'className' => WapplerSystems\FluxTranslationfix\View\PreviewView::class,
                            ],
                            FluidTYPO3\Flux\View\PageLayoutView::class => [
                                'className' => WapplerSystems\FluxTranslationfix\View\PageLayoutView::class,
                            ],
                            TYPO3\CMS\Backend\View\PageLayoutView::class => [
                                'className' => WapplerSystems\FluxTranslationfix\View\PageLayoutView::class,
                            ]
                        ],
                    ],
                ],
            ]
        );
    },
    $_EXTKEY
);


