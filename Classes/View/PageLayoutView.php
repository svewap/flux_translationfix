<?php
namespace WapplerSystems\FluxTranslationfix\View;


class PageLayoutView extends \FluidTYPO3\Flux\View\PageLayoutView
{


    public static $staticLanguageHasTranslationsCache = [];


    /**
     * @return array
     */
    public function getLanguageHasTranslationsCache(): array
    {
        return $this->languageHasTranslationsCache;
    }


    /**
     * @param array $contentElements
     * @param int $language
     * @return bool|void
     *
     * Better: lead through PageLayoutView to get languageHasTranslationsCache array
     */
    protected function checkIfTranslationsExistInLanguage(array $contentElements, $language)
    {
        parent::checkIfTranslationsExistInLanguage($contentElements, $language);

        self::$staticLanguageHasTranslationsCache = $this->languageHasTranslationsCache;
    }




}