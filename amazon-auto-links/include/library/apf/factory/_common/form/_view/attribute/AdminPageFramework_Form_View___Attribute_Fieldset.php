<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Form_View___Attribute_Fieldset extends AmazonAutoLinks_AdminPageFramework_Form_View___Attribute_FieldContainer_Base {
    public $sContext = 'fieldset';
    protected function _getAttributes()
    {
        return array( 'id' => $this->sContext . '-' . $this->aArguments[ 'tag_id' ], 'class' => implode(' ', array( 'amazon-auto-links-' . $this->sContext, $this->_getSelectorForChildFieldset() )), 'data-field_id' => $this->aArguments[ 'tag_id' ], 'style' => $this->_getInlineCSS(), );
    }
    private function _getInlineCSS()
    {
        return (1 <= $this->aArguments[ '_nested_depth' ]) && $this->aArguments[ 'hidden' ] ? 'display:none' : null;
    }
    private function _getSelectorForChildFieldset()
    {
        if ($this->aArguments[ '_nested_depth' ] == 0) {
            return '';
        }
        if ($this->aArguments[ '_nested_depth' ] == 1) {
            return 'child-fieldset nested-depth-' . $this->aArguments[ '_nested_depth' ];
        }
        return 'child-fieldset multiple-nesting nested-depth-' . $this->aArguments[ '_nested_depth' ];
    }
}
