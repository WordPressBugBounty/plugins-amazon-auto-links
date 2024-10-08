<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Form_View___Description extends AmazonAutoLinks_AdminPageFramework_FrameworkUtility {
    public $aDescriptions = array();
    public $sClassAttribute = 'amazon-auto-links-form-element-description';
    public function __construct()
    {
        $_aParameters = func_get_args() + array( $this->aDescriptions, $this->sClassAttribute, );
        $this->aDescriptions = $this->getAsArray($_aParameters[ 0 ]);
        $this->sClassAttribute = $_aParameters[ 1 ];
    }
    public function get()
    {
        if (empty($this->aDescriptions)) {
            return '';
        }
        $_aOutput = array();
        foreach ($this->aDescriptions as $_sDescription) {
            $_aOutput[] = "<p class='" . esc_attr($this->sClassAttribute) . "'>" . "<span class='description'>" . $_sDescription . "</span>" . "</p>";
        }
        return implode(PHP_EOL, $_aOutput);
    }
}
