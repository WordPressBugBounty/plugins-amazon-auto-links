<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Resource_admin_page extends AmazonAutoLinks_AdminPageFramework_Resource_Base {
    protected function _printClassSpecificStyles($sIDPrefix)
    {
        static $_aLoaded = array();
        if (isset($_aLoaded[ $this->oProp->sClassName ])) {
            parent::_printClassSpecificStyles($sIDPrefix);
            return;
        }
        $_aLoaded[ $this->oProp->sClassName ] = true;
        $_oCaller = $this->oProp->oCaller;
        $_sPageSlug = $this->_getCurrentPageSlugForFilter();
        $_sTabSlug = $this->_getCurrentTabSlugForFilter($_sPageSlug);
        if ($_sPageSlug && $_sTabSlug) {
            $this->oProp->sStyle = $this->addAndApplyFilters($_oCaller, "style_{$_sPageSlug}_{$_sTabSlug}", $this->oProp->sStyle);
        }
        if ($_sPageSlug) {
            $this->oProp->sStyle = $this->addAndApplyFilters($_oCaller, "style_{$_sPageSlug}", $this->oProp->sStyle);
        }
        parent::_printClassSpecificStyles($sIDPrefix);
    }
    private function _getCurrentPageSlugForFilter()
    {
        $_sPageSlug = $this->oProp->getCurrentPageSlug();
        return $this->oProp->isPageAdded($_sPageSlug) ? $_sPageSlug : '';
    }
    private function _getCurrentTabSlugForFilter($sPageSlug)
    {
        $_sTabSlug = $this->oProp->getCurrentTabSlug($sPageSlug);
        return isset($this->oProp->aInPageTabs[ $sPageSlug ][ $_sTabSlug ]) ? $_sTabSlug : '';
    }
    protected function _printClassSpecificScripts($sIDPrefix)
    {
        static $_aLoaded = array();
        if (isset($_aLoaded[ $this->oProp->sClassName ])) {
            parent::_printClassSpecificScripts($sIDPrefix);
            return;
        }
        $_aLoaded[ $this->oProp->sClassName ] = true;
        $_oCaller = $this->oProp->oCaller;
        $_sPageSlug = $this->_getCurrentPageSlugForFilter();
        $_sTabSlug = $this->_getCurrentTabSlugForFilter($_sPageSlug);
        if ($_sPageSlug && $_sTabSlug) {
            $this->oProp->sScript = $this->addAndApplyFilters($_oCaller, "script_{$_sPageSlug}_{$_sTabSlug}", $this->oProp->sScript);
        }
        if ($_sPageSlug) {
            $this->oProp->sScript = $this->addAndApplyFilters($_oCaller, "script_{$_sPageSlug}", $this->oProp->sScript);
        }
        parent::_printClassSpecificScripts($sIDPrefix);
    }
    protected function _enqueueSRCByCondition($aEnqueueItem)
    {
        $sCurrentPageSlug = $this->oProp->getCurrentPageSlug();
        $sCurrentTabSlug = $this->oProp->getCurrentTabSlug($sCurrentPageSlug);
        $sPageSlug = $aEnqueueItem['sPageSlug'];
        $sTabSlug = $aEnqueueItem['sTabSlug'];
        if (! $sPageSlug && $this->oProp->isPageAdded($sCurrentPageSlug)) {
            $this->_enqueueSRC($aEnqueueItem);
        }
        if (($sPageSlug && $sCurrentPageSlug == $sPageSlug) && ($sTabSlug && $sCurrentTabSlug == $sTabSlug)) {
            $this->_enqueueSRC($aEnqueueItem);
        }
        if (($sPageSlug && ! $sTabSlug) && ($sCurrentPageSlug == $sPageSlug)) {
            $this->_enqueueSRC($aEnqueueItem);
        }
    }
}
