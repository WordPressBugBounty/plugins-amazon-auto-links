<?php
/**
 * Auto Amazon Links
 *
 * Generates links of Amazon products just coming out today. You just pick categories and they appear even in JavaScript disabled browsers.
 *
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno
 */
 
 
class AmazonAutoLinks_UnitOptionConverter_Setting_PageMetaBox_Common extends AmazonAutoLinks_UnitOptionConverter_Setting_PageMetaBox_Base {
        
    /*
     * ( optional ) Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        
        $_aClasses = array(
            'AmazonAutoLinks_FormFields_Unit_Common',
        );
        foreach( $_aClasses as $_sClassName ) {
            $_oFields = new $_sClassName( $this );
            $_aFields = $_oFields->get();
            foreach( $_aFields as $_aField ) {           
                $this->addSettingFields( $_aField );
            }            
        }            
        
    }
    
    /**
     * Validates submitted form data.
     */
    public function validate( $aInputs, $aOriginal, $oFactory ) {    
        return $aInputs;
    }
 
 
}