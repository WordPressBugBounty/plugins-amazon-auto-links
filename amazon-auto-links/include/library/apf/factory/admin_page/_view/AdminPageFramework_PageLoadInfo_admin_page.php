<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_PageLoadInfo_admin_page extends AmazonAutoLinks_AdminPageFramework_PageLoadInfo_Base {
    private static $_oInstance;
    private static $aClassNames = array();
    public static function instantiate($oProp, $oMsg)
    {
        if (in_array($oProp->sClassName, self::$aClassNames)) {
            return self::$_oInstance;
        }
        self::$aClassNames[] = $oProp->sClassName;
        self::$_oInstance = new AmazonAutoLinks_AdminPageFramework_PageLoadInfo_admin_page($oProp, $oMsg);
        return self::$_oInstance;
    }
    public function _replyToSetPageLoadInfoInFooter()
    {
        if ($this->oProp->isPageAdded()) {
            add_filter('update_footer', array( $this, '_replyToGetPageLoadInfo' ), 999);
        }
    }
}
