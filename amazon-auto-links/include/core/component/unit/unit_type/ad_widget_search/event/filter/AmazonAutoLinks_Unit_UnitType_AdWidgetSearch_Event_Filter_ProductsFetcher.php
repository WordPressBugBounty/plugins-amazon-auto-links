<?php
/**
 * Auto Amazon Links
 *
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno
 *
 */

/**
 * Fetches product data from outside source.
 *
 * @since 5.0.0
 */
class AmazonAutoLinks_Unit_UnitType_AdWidgetSearch_Event_Filter_ProductsFetcher extends AmazonAutoLinks_Unit_UnitType_Common_Event_Filter_ProductsFetcher_Base {

    public $sUnitType = 'ad_widget_search';

    /**
     * @var AmazonAutoLinks_UnitOutput_ad_widget_search
     */
    public $oUnitOutput;

    /**
     * @param  array $aProducts
     * @return array
     * @since  5.0.0
     */
    protected function _getItemsFromSource( $aProducts ) {

        $_sLocale      = ( string ) $this->oUnitOutput->oUnitOption->get( 'country' );
        $_asKeywords   = $this->oUnitOutput->oUnitOption->get( array( 'Keywords' ), array() );
        $_aKeywords    = is_array( $_asKeywords )
            ? $_asKeywords
            : explode( ',', $_asKeywords );

        // 5.3.4 The keywords are used for sorting with the `asin` sort order
        $this->oUnitOutput->oUnitOption->set( array( '_keywords' ), $_aKeywords );

        $_oAdWidgetAPI = new AmazonAutoLinks_AdWidgetAPI_Search(
            $_sLocale,
            ( integer ) $this->oUnitOutput->oUnitOption->get( 'cache_duration' )
        );
        $_aResponse    = $_oAdWidgetAPI->get(
            $_aKeywords,
            array(
                'SearchIndex' => $this->oUnitOutput->oUnitOption->get( array( 'SearchIndex' ), null ),
                'BrowseNode'  => $this->getEmptyConvertedToNull( trim( $this->oUnitOutput->oUnitOption->get( array( 'BrowseNode' ), null ) ) ),   // 5.3.0
            ),
            ( integer ) $this->oUnitOutput->oUnitOption->get( 'count' )
        );

        // 5.1.0 Set the last modified time
        $this->oUnitOutput->iLastModified = $this->getElement( $_aResponse, array( '_ModifiedDate' ), 0 );
        return $this->getElementAsArray( $_aResponse, array( 'results' ) );

    }

}