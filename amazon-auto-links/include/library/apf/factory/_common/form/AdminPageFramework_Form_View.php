<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Form_View extends AmazonAutoLinks_AdminPageFramework_Form_Model {
    public function __construct()
    {
        parent::__construct();
        new AmazonAutoLinks_AdminPageFramework_Form_View__Resource($this);
    }
    public function get()
    {
        $this->sCapability = $this->callBack($this->aCallbacks[ 'capability' ], '');
        if (! $this->canUserView($this->sCapability)) {
            return '';
        }
        $this->_formatElementDefinitions($this->aSavedData);
        $_oFormTables = new AmazonAutoLinks_AdminPageFramework_Form_View___Sectionsets(array( 'capability' => $this->sCapability, ) + $this->aArguments, array( 'field_type_definitions' => $this->aFieldTypeDefinitions, 'sectionsets' => $this->aSectionsets, 'fieldsets' => $this->aFieldsets, ), $this->aSavedData, $this->callBack($this->aCallbacks[ 'field_errors' ], array( $this->getFieldErrors() )), $this->aCallbacks, $this->oMsg);
        return $this->___getNoScriptMessage() . $_oFormTables->get();
    }
    private function ___getNoScriptMessage()
    {
        if ($this->hasBeenCalled($this->aArguments[ 'caller_id' ] . '::' . __METHOD__)) {
            return '';
        }
        return "<noscript>" . "<div class='error'>" . "<p class='amazon-auto-links-form-warning'>" . $this->oMsg->get('please_enable_javascript') . "</p>" . "</div>" . "</noscript>";
    }
    public function printSubmitNotices()
    {
        $this->oSubmitNotice->render();
    }
}
