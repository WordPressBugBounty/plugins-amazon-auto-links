<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Form_View___Section_Base extends AmazonAutoLinks_AdminPageFramework_Form_Base {
    public function isSectionsetVisible($aSectionset)
    {
        if (empty($aSectionset)) {
            return false;
        }
        return $this->callBack($this->aCallbacks[ 'is_sectionset_visible' ], array( true, $aSectionset ));
    }
    public function isFieldsetVisible($aFieldset)
    {
        if (empty($aFieldset)) {
            return false;
        }
        return $this->callBack($this->aCallbacks[ 'is_fieldset_visible' ], array( true, $aFieldset ));
    }
    public function getFieldsetOutput($aFieldset)
    {
        if (! $this->isFieldsetVisible($aFieldset)) {
            return '';
        }
        $_oFieldset = new AmazonAutoLinks_AdminPageFramework_Form_View___Fieldset($aFieldset, $this->aSavedData, $this->aFieldErrors, $this->aFieldTypeDefinitions, $this->oMsg, $this->aCallbacks);
        $_sFieldOutput = $_oFieldset->get();
        return $_sFieldOutput;
    }
}
