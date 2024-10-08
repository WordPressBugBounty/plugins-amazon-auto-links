<?php
/**
 * Auto Amazon Links
 * 
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno
 * 
 */

/**
 * Handles ad_widget_search unit options.
 * 
 * @since 5.0.0
 */
class AmazonAutoLinks_UnitOption_ad_widget_search extends AmazonAutoLinks_UnitOption_Base {

    /**
     * @var string
     */
    public $sUnitType = 'ad_widget_search';

    /**
     * Stores the default structure and key-values of the unit.
     * @remark Accessed from the base class constructor to construct a default option array.
     */
    static public $aStructure_Default = array(
        'Keywords'    => '',
        'sort'        => 'raw', // title, title_descending
        'SearchIndex' => 'All',
        'BrowseNode'  => null,
    );

    /**
     * @return array
     * @since  5.0.0
     */
    protected function getDefaultOptionStructure() {
        return parent::getDefaultOptionStructure()
            + AmazonAutoLinks_UnitOption_search::$aStructure_Default
            + AmazonAutoLinks_UnitOption_item_lookup::$aStructure_Default;
    }

    /**
     * @param  array $aUnitOptions
     * @param  array $aDefaults
     * @param  array $aRawOptions
     * @return array
     * @since  5.0.0
     */
    protected function _getUnitOptionsFormatted( array $aUnitOptions, array $aDefaults, array $aRawOptions ) {

        // Shortcode arguments
        if ( isset( $aUnitOptions[ 'asin' ] ) ) {
            $_aKeywords = $this->getStringIntoArray( $aUnitOptions[ 'asin' ], ',' );
            $aUnitOptions[ 'Keywords' ] = isset( $aUnitOptions[ 'Keywords' ] ) && $aUnitOptions[ 'Keywords' ]
                ? array_unique( array_merge( $this->getStringIntoArray( $aUnitOptions[ 'Keywords' ], ',' ), $_aKeywords ) )
                : $_aKeywords;
        }
        if ( isset( $aUnitOptions[ 'search' ] ) ) {
            $_aKeywords = $this->getStringIntoArray( $aUnitOptions[ 'search' ], ',' );
            $aUnitOptions[ 'Keywords' ] = isset( $aUnitOptions[ 'Keywords' ] ) && $aUnitOptions[ 'Keywords' ]
                ? array_unique( array_merge( $this->getStringIntoArray( $aUnitOptions[ 'Keywords' ], ',' ), $_aKeywords ) )
                : $_aKeywords;
        }

        $_aUnitOptions = $this->_getShortcodeArgumentKeysSanitized(
            $aUnitOptions,
            AmazonAutoLinks_UnitOption_search::$aShortcodeArgumentKeys + AmazonAutoLinks_UnitOption_item_lookup::$aShortcodeArgumentKeys
        );

        $_aUnitOptions = parent::_getUnitOptionsFormatted( $_aUnitOptions, $aDefaults, $aRawOptions );

        // [5.0.6] The shortcode key sanitizing method converts the key name to have the capital S and drop the lower case key element.
        // But missing the lower-case element causes the meta-box value not to be saved. So keep it.
        $_aUnitOptions[ 'sort' ] = $_aUnitOptions[ 'Sort' ];

        // [5.3.4] The new sort order 'asin' is introduced and it is the default sort order for the shortcode with the `asin` argument
        // This is needed because ad-widget API returns results in irregular order ignoring the keyword (ASIN) order.
        if ( isset( $aUnitOptions[ 'asin' ] ) && 'raw' === $_aUnitOptions[ 'sort' ] ) {
            $_aUnitOptions[ 'sort' ] = 'asin';
        }

        return $_aUnitOptions;

    }

}