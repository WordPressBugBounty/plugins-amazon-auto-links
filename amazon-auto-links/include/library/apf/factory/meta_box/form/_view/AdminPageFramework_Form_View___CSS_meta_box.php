<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class AmazonAutoLinks_AdminPageFramework_Form_View___CSS_meta_box extends AmazonAutoLinks_AdminPageFramework_Form_View___CSS_Base {
    protected function _get()
    {
        return $this->_getRules();
    }
    private function _getRules()
    {
        return <<<CSSRULES
.postbox .title-colon{margin-left:.2em}.postbox .amazon-auto-links-section .form-table>tbody>tr>td,.postbox .amazon-auto-links-section .form-table>tbody>tr>th{display:inline-block;width:100%;padding:0;float:right;clear:right}.postbox .amazon-auto-links-field{width:auto}.postbox .amazon-auto-links-field{max-width:100%}.postbox .sortable .amazon-auto-links-field{max-width:84%;width:auto}.postbox .amazon-auto-links-section .form-table>tbody>tr>th{font-size:13px;line-height:1.5;margin:1em 0;font-weight:700}#poststuff .metabox-holder .postbox-container .amazon-auto-links-section-title h3{border:none;font-weight:700;font-size:1.12em;margin:1em 0;padding:0;font-family:'Open Sans',sans-serif;cursor:inherit;-webkit-user-select:inherit;-moz-user-select:inherit;user-select:inherit;text-shadow:none;-webkit-box-shadow:none;box-shadow:none;background:none}#poststuff .metabox-holder .postbox-container .amazon-auto-links-collapsible-title h3{margin:0}#poststuff .metabox-holder .postbox-container h4{margin:1em 0;font-size:1.04em}#poststuff .metabox-holder .postbox-container .amazon-auto-links-section-tab h4{margin:0}@media screen and (min-width:783px){#poststuff #post-body.columns-2 #side-sortables .postbox .amazon-auto-links-section .form-table input[type=text]{width:98%}}
CSSRULES;
    }
}
