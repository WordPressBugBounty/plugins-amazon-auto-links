<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Resource_post_meta_box extends AmazonAutoLinks_AdminPageFramework_Resource_Base {
    protected function _enqueueSRCByCondition($aEnqueueItem)
    {
        $_sCurrentPostType = isset($_GET[ 'post_type' ]) ? $this->getHTTPQueryGET('post_type') : (isset($GLOBALS[ 'typenow' ]) ? $GLOBALS[ 'typenow' ] : null);
        if (empty($aEnqueueItem[ 'aPostTypes' ])) {
            $this->_enqueueSRC($aEnqueueItem);
            return;
        }
        if (in_array($_sCurrentPostType, $aEnqueueItem[ 'aPostTypes' ], true)) {
            $this->_enqueueSRC($aEnqueueItem);
        }
    }
}
