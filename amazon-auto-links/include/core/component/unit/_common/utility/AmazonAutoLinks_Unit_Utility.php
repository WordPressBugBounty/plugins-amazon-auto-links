<?php
/**
 * Auto Amazon Links
 *
 * Generates links of Amazon products just coming out today. You just pick categories and they appear even in JavaScript disabled browsers.
 *
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno
 */

/**
 * A class that provides utility methods for the unit component.
 * @since 3.8.11
 */
class AmazonAutoLinks_Unit_Utility extends AmazonAutoLinks_PluginUtility {

    /**
     * @since  5.0.0
     * @return array The unit post type label arguments
     */
    static public function getUnitPostTypeLabels() {

        // Allow the user to set custom post type name which appears in the breadcrumb.
        $_oOption       = AmazonAutoLinks_Option::getInstance();
        $_sFrontendName = $_oOption->get(
            array( 'unit_preview', 'preview_post_type_label' ),
            AmazonAutoLinks_Registry::NAME
        );
        return is_admin()
            ? array(
                'name'                  => $_sFrontendName,
                'menu_name'             => apply_filters(
                    'aal_filter_admin_menu_name',
                    AmazonAutoLinks_Registry::NAME
                ),
                'all_items'             => __( 'Manage Units', 'amazon-auto-links' ),    // sub menu label
                'singular_name'         => sprintf( __( '%1$s Unit', 'amazon-auto-links' ), AmazonAutoLinks_Registry::NAME ),
                'add_new'               => __( 'Add Unit by Category', 'amazon-auto-links' ),
                'add_new_item'          => __( 'Add New Unit', 'amazon-auto-links' ),
                'edit'                  => __( 'Edit', 'amazon-auto-links' ),
                'edit_item'             => __( 'Edit Unit', 'amazon-auto-links' ),
                'new_item'              => __( 'New Unit', 'amazon-auto-links' ),
                'view'                  => __( 'View', 'amazon-auto-links' ),
                'view_item'             => __( 'View Product Links', 'amazon-auto-links' ),
                'search_items'          => __( 'Search Units', 'amazon-auto-links' ),
                'not_found'             => __( 'No unit found for Auto Amazon Links', 'amazon-auto-links' ),
                'not_found_in_trash'    => __( 'No Unit Found for Auto Amazon Links in Trash', 'amazon-auto-links' ),
                'parent'                => __( 'Parent Unit', 'amazon-auto-links' ),

                // framework specific keys
                'plugin_action_link'    => __( 'Units', 'amazon-auto-links' ),
            )
            : array(
                'name'                  => $_sFrontendName,
                'edit_item'             => __( 'Edit Unit', 'amazon-auto-links' ),
            );

    }

    /**
     * @since  4.3.2
     * @param  array        $aItem  A product array of a PA-API 5 response.
     * @return null|integer The star rating with two digits like 45 for 4.5.
     */
    static public function getRatingFromItem( array $aItem ) {
        $_dnStarRating = self::getElement( $aItem, array( 'CustomerReviews', 'StarRating', 'Value' ) );
        return null === $_dnStarRating
            ? null
            : ( integer ) ( ( double ) $_dnStarRating * 10 );
    }

    /**
     * @since  4.3.2
     * @param  array $aItem  A product array of a PA-API 5 response.
     * @return null|integer The review count.
     */
    static public function getReviewCountFromItem( array $aItem ) {
        $_aCustomerReview = self::getElementAsArray( $aItem, array( 'CustomerReviews' ) );
        $_inReviewCount   = self::getElement( $_aCustomerReview, array( 'Count' ) );
        if ( null === $_inReviewCount ) {
            return null;
        }
        return ( integer ) $_inReviewCount;
    }

    /**
     * Generates a rating star output.
     *
     * There seems to be cases that the response contains rating information.
     *
     * @since  4.3.2
     * @since  4.3.4 Added the `$sAssociateID` parameter.
     * @see    https://stackoverflow.com/a/64002035
     * @param  array  $aItem  A product array of a PA-API 5 response.
     * @param  string $sLocale
     * @param  string $sAssociateID
     * @return string|null
     */
    static public function getFormattedRatingFromItem( array $aItem, $sLocale, $sAssociateID ) {
        $_inReviewCount   = self::getReviewCountFromItem( $aItem );
        $_diStarRating    = self::getRatingFromItem( $aItem );
        if ( ! isset( $_inReviewCount, $_diStarRating ) ) {
            return null;
        }
        $_oLocale         = new AmazonAutoLinks_Locale( $sLocale );
        $_sReviewURL      = $_oLocale->getCustomerReviewURL( $aItem[ 'ASIN' ], $sAssociateID );
        return "<div class='amazon-customer-rating-stars'>"
            . self::getRatingOutput( $_diStarRating, $_sReviewURL, $_inReviewCount )
        . "</div>";
    }

    /**
     * Generates a thumbnail URL from a given ASIN.
     *
     *
     * ## Examples
     * ### HTTP
     * http://images-jp.amazon.com/images/P/{ASIN,ISBN}.{COUNTRY_NUMBER}.{SIZE}.jpg
     * http://images.amazon.com/images/P/{ASIN,ISBN}.{COUNTRY_NUMBER}._{DISCOUNT_RATE}_PI_SC{SIZE}_.jpg
     * http://images.amazon.com/images/P/{ASIN,ISBN}.{COUNTRY_NUMBER}._SCL_SX{PIXELSIZE}_.jpg
     *
     * ### HTTPS (SSL)
     * https://images-na.ssl-images-amazon.com/images/P/{ASIN,ISBN}.{COUNTRY_NUMBER}.{SIZE}.jpg
     *
     * #### COUNTRY_NUMBER (optional)
     * US: 01
     * UK:
     * JP: 09
     *
     * #### SIZE - Image Sizes
     * - THUMBZZZ	tiny	75x75	52x75
     * - TZZZZZZZ	small	110x110	77x110
     * - MZZZZZZZ	medium	160x160	112x160
     * - LZZZZZZZ	large	500x500	349x500
     *
     * ### DISCOUNT_RATE - discount mark
     * - PE{*}
     * e.g. `PE30` for 30% off
     *
     * ### Widget Method
     * @see https://stackoverflow.com/questions/58142293/fetching-amazon-product-image-by-asin
     *
     * @param   string  $sASIN
     * @param   string  $sLocale
     * @param   integer $iImageSize
     * @return  string
     * @since   4.2.2
     * @see     https://www.oreilly.com/library/view/amazon-hacks/0596005423/ch01s07.html
     * @see     https://www.ipentec.com/document/internet-get-amazon-product-image
     * @see     https://stackoverflow.com/a/58921751
     * @remark  This method is not reliable as the locale code is not functioning; even though it is removed or changed, the image is still accessible.
     * This means the image for a product will exist with a particular locale even though the product does not exist for the locale.
     * @deprecated 4.2.2    Not used at the moment.
     */
    static public function getThumbnailURLFromASIN( $sASIN, $sLocale, $iImageSize ) {
        $_oLocale       = new AmazonAutoLinks_Locale( $sLocale );
        $_sLocaleNumber = $_oLocale->getLocaleNumber();
        return is_ssl()
            ? "https://images-na.ssl-images-amazon.com/images/P/{$sASIN}.{$_sLocaleNumber}._SCL_SX{$iImageSize}_.jpg"
            : "http://images.amazon.com/images/P/{$sASIN}.{$_sLocaleNumber}._SCL_SX{$iImageSize}_.jpg";
    }


    /**
     * Extracts ASIN from the given url.
     *
     * ASIN is a product ID consisting of 10 characters.
     *
     * ### Example Regex Patterns
     * ```
     * /http:\/\/(?:www\.|)amazon\.com\/(?:gp\/product|[^\/]+\/dp|dp)\/([^\/]+)/
     * "http://www.amazon.com/([\\w-]+/)?(dp|gp/product)/(\\w+/)?(\\w{10})"
     * ```
     * @since  unknown
     * @since  3.5.0   Renamed from `getASIN()`
     * @since  3.5.0   Moved from `AmazonAutoLinks_UnitOutput_Base_ElementFormat`.
     * @since  3.8.12  Moved from `AmazonAutoLinks_UnitOutput_Utility`
     * @param  string  $sURL
     * @return string  The found ASIN, or an empty string when not found.
     */
    static public function getASINFromURL( $sURL ) {

        $sURL = remove_query_arg(
            array( 'smid', 'pf_rd_p', 'pf_rd_s', 'pf_rd_t', 'pf_rd_i', 'pf_rd_m', 'pf_rd_r' ),
            $sURL
        );

        $sURL = preg_replace(
            array(
                '/[A-Z0-9]{11,}/',  // Remove strings like an ASIN but with more than 10 characters.
            ),
            '',
            $sURL
        );

        preg_match(
            '/(dp|gp|e)\/(.+\/)?([A-Z0-9]{10})\W/', // needle - [A-Z0-9]{10} is the ASIN
            $sURL,  // subject
            $_aMatches // match container
        );
        return isset( $_aMatches[ 3 ] )
            ? $_aMatches[ 3 ]
            : '';

    }

    /**
     * Extracts the price amount from a given formatted readable price string.
     * Passed price string is like:
     *  - 10,40 € (0,43 € / unità)
     * @param  string  $sReadablePrice
     * @return integer|string
     * @since  4.7.8
     * @since  5.0.0   Moved from `AmazonAutoLinks_Unit_Event_Action_UpdateProductsWithAdWidgetAPI`.
     */
    static public function getPriceAmountExtracted( $sReadablePrice ) {
        preg_match( "/[0-9,.]+/", $sReadablePrice, $_aMatches );    // extracts the first occurrence of digits with comma
        return isset( $_aMatches[ 0 ] )
            ? ( integer ) preg_replace("/[^0-9]/", '', $_aMatches[ 0 ] )
            : '';
    }

    /**
     * Generates price output.
     * @param  string       $sPriceFormatted
     * @param  integer|null $inDiscounted
     * @param  integer|null $inLowestNew
     * @param  string       $sDiscountedFormatted
     * @param  string       $sLowestNewFormatted
     * @return string       The price output. If a discount is available, the discounted price is also returned along with the proper price.
     * @since  3.8.11
     */
    static public function getPrice( $sPriceFormatted, $inDiscounted, $inLowestNew, $sDiscountedFormatted, $sLowestNewFormatted ) {
        $_inOffered            = self::___getLowestPrice( $inLowestNew, $inDiscounted );
        $_sLowestFormatted     = $inDiscounted === $_inOffered
            ? $sDiscountedFormatted
            : $sLowestNewFormatted;
        $_bDiscounted          = $_sLowestFormatted && ( $sPriceFormatted !== $_sLowestFormatted );
        return $_bDiscounted
            ? '<span class="amazon-prices"><span class="proper-price"><s>' . $sPriceFormatted . '</s></span> '
                . '<span class="offered-price">' . $_sLowestFormatted . '</span></span>'
            : ( '' === $sPriceFormatted
                ? ''
                : '<span class="amazon-prices"><span class="offered-price">' . $sPriceFormatted . '</span></span>'
            );
    }
        /**
         * @param   integer      $_iLowestNew
         * @param   integer      $_iDiscount
         * @return  integer|null
         * @since   3.4.11
         * @since   3.5.0        Moved from `AmazonAutoLinks_UnitOutput_Base_ElementFormatter`.
         */
        static private function ___getLowestPrice( $_iLowestNew, $_iDiscount ) {
            $_aOfferedPrices        = array();
            if ( null !== $_iLowestNew ) {
                $_aOfferedPrices[] = ( integer ) $_iLowestNew;
            }
            if ( null !== $_iDiscount ) {
                $_aOfferedPrices[] = ( integer ) $_iDiscount;
            }
            return ! empty( $_aOfferedPrices )
                ? min( $_aOfferedPrices )
                : null;
        }

    /**
     * Extracts price information from a PA API response item element.
     * @since   3.9.0
     * @param   array   $aItem
     * @return  array
     */
    static public function getPrices( array $aItem ) {
        
        // The actual displayed tag price. This can be a discount price or proper price.
        $_aBuyingPrice         = self::getElementAsArray(
            $aItem,
            array( 'Offers', 'Listings', 0, 'Price' )
        );
        $_sBuyingPrice         = self::getElement( $_aBuyingPrice, array( 'DisplayAmount' ), '' );
        $_sCurrency            = self::getElement( $_aBuyingPrice, array( 'Currency' ), '' );
        $_inBuyingPrice        = self::getElement( $_aBuyingPrice, array( 'Amount' ) );
        // Saved price, present if there is a discount
        $_sSavingPrice         = self::getElement(
            $aItem,
            array( 'Offers', 'Listings', 0, 'Price', 'Savings', 'DisplayAmount' ),
            ''
        );

        // There are cases that `SavingBasis` is missing when there is no discount item.
        // @see https://webservices.amazon.com/paapi5/documentation/offers.html#savingbasis
        $_sProperPrice         = self::getElement(
            $aItem,
            array( 'Offers', 'Listings', 0, 'SavingBasis', 'DisplayAmount' ),
            $_sBuyingPrice
        );
        $_ndProperPriceAmount  = self::getElement(
            $aItem,
            array( 'Offers', 'Listings', 0, 'SavingBasis', 'Amount' ),
            $_inBuyingPrice
        );
        $_isProperPriceAmount  = isset( $_ndProperPriceAmount ) ? $_ndProperPriceAmount * 100 : '';
        $_sDiscountedPrice     = $_sSavingPrice ? $_sBuyingPrice : '';
        $_inDiscountedPrice    = $_sSavingPrice ? $_inBuyingPrice : null;
        $_aSummaries           = self::getElementAsArray( $aItem, array( 'Offers', 'Summaries' ) );
        $_aLowests             = self::___getLowestPrices( $_aSummaries );
        $_isDiscountPercentage = self::getElement(
            $aItem,
            array( 'Offers', 'Listings', 0, 'Price', 'Savings', 'Percentage' ),
            ''
        );
        $_aPrices              = array(
            'proper_price'       => $_sProperPrice  // 3.8.11 changed from `price`
                ? "<span class='amazon-product-price-value'>"  
                       . "<span class='proper-price'>" . $_sProperPrice . "</span>"
                    . "</span>"
                : "",
            'discounted_price'   => $_sDiscountedPrice
                ? "<span class='amazon-product-discounted-price-value'>" 
                        . $_sDiscountedPrice
                    . "</span>"
                : '',
            'lowest_new_price'     => isset( $_aLowests[ 'new_formatted' ] )
                ? "<span class='amazon-product-lowest-new-price-value'>"
                        . $_aLowests[ 'new_formatted' ]
                    . "</span>"
                : '',
            'lowest_used_price'  => isset( $_aLowests[ 'used_formatted' ] )
                ? "<span class='amazon-product-lowest-used-price-value'>"
                        . $_aLowests[ 'used_formatted' ]
                    . "</span>"
                : '',

            // For DB
            'currency'                      => $_sCurrency,
            'price_amount'                  => $_isProperPriceAmount,
            'price_formatted'               => $_sProperPrice,  // price_formatted
            'lowest_new_price_amount'       => is_null( $_aLowests[ 'new_amount' ] ) ? '' : $_aLowests[ 'new_amount' ] * 100, // lowest_new_price
            'lowest_new_price_formatted'    => $_aLowests[ 'new_formatted' ], // lowest_new_price_formatted
            'lowest_used_price_amount'      => is_null( $_aLowests[ 'used_amount' ] ) ? '' : $_aLowests[ 'used_amount' ] * 100, // lowest_used_price
            'lowest_used_price_formatted'   => $_aLowests[ 'used_formatted' ], // lowest_used_price_formatted
            'discounted_price_amount'       => is_null( $_inDiscountedPrice ) ? '' : $_inDiscountedPrice * 100, // discounted_price
            'discounted_price_formatted'    => $_sDiscountedPrice, // discounted_price_formatted

            // [3.7.10+] for discount rates and labels
            'discount_percentage'           => $_isDiscountPercentage,
            'formatted_discount'            => self::getFormattedDiscount( $_isDiscountPercentage ),
        );

        $_aPrices[ 'formatted_price' ] = self::getPrice(
            $_sProperPrice,                 // string
            $_inDiscountedPrice,            // integer|null
            $_aLowests[ 'new_amount' ],     // integer|null
            $_sDiscountedPrice,             // string
            $_aLowests[ 'new_formatted' ]   // string
        );
        return $_aPrices;

    }
        /**
         * @param  array $aOffers
         * @return array
         * @since  3.9.0
         */
        static private function ___getLowestPrices( array $aOffers ) {
            $_aLowests = array(
                'new_amount'     => null, // integer
                'new_formatted'  => null, // string
                'used_amount'    => null, // integer
                'used_formatted' => null, // string
            );
            foreach( $aOffers as $_aOffer ) {
                $_sCondition = self::getElement( $_aOffer, array( 'Condition', 'Value' ) );
                $_dAmount    = self::getElement( $_aOffer, array( 'LowestPrice', 'Amount' ) );
                $_sFormatted = self::getElement( $_aOffer, array( 'LowestPrice', 'DisplayAmount' ) );
                if ( 'New' === $_sCondition ) {
                    if (
                        null === $_aLowests[ 'new_amount' ]
                        || $_aLowests[ 'new_amount' ] > $_dAmount
                    ) {
                        $_aLowests[ 'new_amount' ]    = $_dAmount;
                        $_aLowests[ 'new_formatted' ] = $_sFormatted;
                    }
                    continue;
                }
                if ( 'Used' === $_sCondition ) {
                    if (
                        null === $_aLowests[ 'used_amount' ]
                        || $_aLowests[ 'used_amount' ] > $_dAmount
                    ) {
                        $_aLowests[ 'used_amount' ]    = $_dAmount;
                        $_aLowests[ 'used_formatted' ] = $_sFormatted;
                    }
                    continue;
                }
            }
            return $_aLowests;
        }

    /**
     * @param  integer|string|null $insPercentage
     * @return string
     * @since  4.7.10
     */
    static public function getFormattedDiscount( $insPercentage ) {
        if ( ! $insPercentage ) {
            return '';
        }
        return "<span class='amazon-discount'>"
                . "<span class='discount-label'>"
                    . sprintf( __( '%1$s%% Off', 'amazon-auto-links' ), $insPercentage )
                . "</span>"
            . "</span>";
    }

    /**
     *
     * @param   array          $aOffer
     * @param   integer|double $nPrice
     * @param   string         $sPriceFormatted
     * @param   integer|double $nDiscountedPrice
     * @return  string
     * @since   3
     * @since   3.8.11         Renamed from `___getFormattedDiscountPrice()` and moved from `AmazonAutoLinks_Event___Action_APIRequestSearchProduct`.
     * @deprecated 4.3.4 Unused.
     */
    /*
    static public function getFormattedDiscountPrice( array $aOffer, $nPrice, $sPriceFormatted, $nDiscountedPrice ) {

        // If the formatted price is set in the Offer element, use it.
        $_sDiscountedPriceFormatted = self::getElement(
            $aOffer,
            array( 'Price', 'FormattedPrice' ),
            ''  // 3.8.5 Changed the value from `null` to an empty string to avoid automatic background product detail retrieval tasks
        );
        if ( '' !== $_sDiscountedPriceFormatted ) {
            return $_sDiscountedPriceFormatted;
        }

        // Otherwise, replace the price part of the listed price with the discounted one.
        return preg_replace(
            '/[\d.,]+/',
            $nDiscountedPrice / 100,   // decimal,  // numeric price
            $sPriceFormatted // price format
        );

    } */

    /**
     * @param  array       $aImageURLs     A numerically indexed array holding sub-image URLs.
     * @param  string      $sTitle         The product title.
     * @param  string      $sProductURL    The product URL.
     * @param  boolean     $bImagePreview  Whether to enable image previews.
     * @return string|null An HTML portion of a set of sub-images.
     * @since  4.0.0
     * @since  4.7.0       Added the `$bImagePreview` parameter.
     */
    static public function getSubImageOutput( array $aImageURLs, $sTitle, $sProductURL, $bImagePreview=true ) {
        $_aSubImageTags = array();
        foreach( $aImageURLs as $_iIndex => $_sImageURL ) {
            $_sTitle      = trim( $sTitle ) . ' #' . ( $_iIndex + 1 );
            $_aAttributes = array(
                'src'   => self::hasPrefix( 'data:image/', $_sImageURL )
                    ? $_sImageURL
                    : esc_url( $_sImageURL ),
                'class' => 'sub-image',
                'alt'   => $_sTitle,
            );
            if ( $bImagePreview ) {
                $_aAttributes[ 'data-large-src' ] = self::hasPrefix( 'data:image/', $_sImageURL )
                    ? $_sImageURL
                    : esc_url( self::getImageURLBySize( $_sImageURL, 500 ) );
                $_aAttributes[ 'data-href' ] = esc_url( $sProductURL );
            }
            $_sImageTag     = self::getHTMLTag( 'img', $_aAttributes );
            $_sATag         = self::getHTMLTag(
                'a',
                array(
                    'href'   => esc_url( $sProductURL ),
                    'target' => '_blank',
                    'title'  => $_sTitle,
                ),
                $_sImageTag
            );
            $_aSubImageTags[] = self::getHTMLTag(
                'div',
                array(
                    'class' => 'sub-image-container',
                ),
                $_sATag
            );
        }
        return empty( $aImageURLs )
            ? null
            : "<div class='sub-images'>" . implode( '', $_aSubImageTags ) . "</div>";
    }

    /**
     * Extract the sub-image (image-set) information from PA API response and generates the output.
     *
     * @param  array   $aImages         The extracted image array from a PA-API response.
     * @param  string  $sProductURL
     * @param  string  $sTitle
     * @param  integer $iMaxImageSize    The maximum size of each sub-image.
     * @param  integer $iMaxNumberOfImages
     * @param  boolean $bImagePreview
     * @return string
     * @since  3       Originally defined in `AmazonAutoLinks_UnitOutput___ElementFormatter_ImageSet`.
     * @since  3.8.11  Renamed from `___getFormattedOutput()` and moved from `AmazonAutoLinks_UnitOutput___ElementFormatter_ImageSet`.
     * @since  4.7.0   Added the `$bImagePreview` parameter.
     */
    static public function getSubImages( array $aImages, $sProductURL, $sTitle, $iMaxImageSize, $iMaxNumberOfImages, $bImagePreview=true ) {
        if ( empty( $aImages ) ) {
            return '';
        }
        return self::getSubImageOutput(
            self::___getSubImageURLs( $aImages, $iMaxImageSize, $iMaxNumberOfImages ),  // extract image urls
            strip_tags( $sTitle ),
            $sProductURL,
            $bImagePreview
        );
    }
        /*
         * @param  array  $aImages
         * @param  integer$iMaxImageSize
         * @param  integer $iMaxNumberOfImages
         * @return array   An array holding image urls.
         */
        static private function ___getSubImageURLs( array $aImages, $iMaxImageSize, $iMaxNumberOfImages ) {

            // If the size is set to 0, it means the user wants no image.
            if ( ! $iMaxImageSize ) {
                return array();
            }

            // The 'main' element is embedded by the plugin.
            unset( $aImages[ 'main' ] );

            $_aURLs  = array();
            foreach( $aImages as $_iIndex => $_aImage ) {

                // The user may set 0 to disable it.
                if ( ( integer ) $_iIndex >= $iMaxNumberOfImages ) {
                    break;
                }
                unset( $_aImage[ 'Large' ] );
                $_aURLs[] = self::___getImageURLFromResponseElement(
                    $_aImage,
                    $iMaxImageSize
                );

            }
            // Drop empty items.
            return array_filter( $_aURLs );

        }
            /**
             * @remark Available key names
             * - Small
             * - Medium
             * - Large
             * @param  array   $aImage
             * @param  integer $iImageSize
             * @return string
             */
            static private function ___getImageURLFromResponseElement( array $aImage, $iImageSize ) {

                $_sURL = '';
                foreach( $aImage as $_sKey => $_aDetails ) {
                    $_sURL = self::getElement(
                        $_aDetails, // subject array
                        array( 'URL' ), // dimensional key
                        ''  // default
                    );
                    if ( $_sURL ) {
                        break;
                    }
                }
                return self::getImageURLBySize( $_sURL, $iImageSize );

                // @deprecated 3.9.0 All Amazon images now start from https
                // return is_ssl()
                //     ? self::getAmazonSSLImageURL( $_sURL )
                //     : $_sURL;

            }

    /**
     * Returns the resized image url.
     *
     * ### Example
     * to adjust the image size to 350 pixels,
     * https://images-na.ssl-images-amazon.com/images/I/81pFmoL7GVL._AC_UL200_SR200,200_.jpg -> https://images-na.ssl-images-amazon.com/images/I/81pFmoL7GVL._AC_UL350_SR350,350_.jpg
     * https://m.media-amazon.com/images/I/31SSaevqVNL._SL160_.jpg -> https://m.media-amazon.com/images/I/31SSaevqVNL._SL350_.jpg
     *
     * @since  3
     * @since  3.5.0       Moved from `AmazonAutoLinks_UnitOutput_Base_ElementFormat`.
     * @since  3.8.11      Moved from `AmazonAutoLinks_UnitOutput_Utility`.
     * @since  3.9.4       Supported more complex image URLs.
     * @param  $sImgURL    string
     * @param  $iImageSize integer     0 to 500.
     * @return string
     */
    static public function getImageURLBySize( $sImgURL, $iImageSize ) {
        preg_match( "/.+\/(.+)$/i", $sImgURL, $_aMatches );
        $_sAfterLastSlash = self::getElement( $_aMatches, array( 1 ), '' );
        // from JS Script string.replace( /(?<=\.*[_A-Z,\.]+)(\d+)(?=[,_\.])/g, imageSize ) );
        $_sDigitsReplaced = preg_replace(
            '/(?<=[\w,])(\d{1,3})(?=[,_])/i',
            '${2}'. $iImageSize,
            $_sAfterLastSlash
        );
        return str_replace( $_sAfterLastSlash, $_sDigitsReplaced, $sImgURL );
    }

    /**
     * Extracts image set (including the main thumbnail image and sub-images) from the product array.
     * @since  3.8.11 Moved from `AmazonAutoLinks_Event___Action_APIRequestSearchProduct`.
     * @param  array  $aItem
     * @return array
     */
    static public function getImageSet( array $aItem ) {

        $_aMainImage = array(
            'MediumImage' => self::getElement( $aItem, array( 'Images', 'Primary', 'Medium', 'URL' ), '' ),
            'LargeImage'  => self::getElement( $aItem, array( 'Images', 'Primary', 'Large', 'URL' ), '' ),
        );
        // Will be numerically indexed array holding sub-image each.
        $_aSubImages = self::getElementAsArray( $aItem, array( 'Images', 'Variants' ) );

        // @deprecated 3.9.0
        // Sub-images can be only single. In that case, put it in a numeric element.
        // if ( ! isset( $_aSubImages[ 0 ] ) ) {
        //     $_aSubImages = array( $_aSubImages );
        // }

        return array( 'main' => $_aMainImage, ) + $_aSubImages;
        
    }

    /**
     * @since  5.2.6
     * @param  array  $aCategoryList
     * @param  string $sDelimiter
     * @return string
     */
    static public function getCategoriesFormatted( array $aCategoryList, $sDelimiter=' > ' ) {
        $_sList = '';
        foreach( $aCategoryList as $_aCategoryListItem ) {
            $_sList .= "<li class='category'>" . implode( $sDelimiter, $_aCategoryListItem ) . "</li>";
        }
        return "<ul class='categories'>" . $_sList . "</ul>";
    }

    /**
     * Constructs the category output from an array of nested browse nodes.
     * @since  3.8.0
     * @since  3.8.11 Moved from `AmazonAutoLinks_UnitOutput_Utility`
     * @since  5.2.6  Renamed from `getCategories()`.
     * @param  array  $aBrowseNodes
     * @return string
     */
    static public function getCategoriesFormattedFromBrowseNodes( array $aBrowseNodes ) {
        return self::getCategoriesFormatted( self::getBrowseNodeList( $aBrowseNodes ) );
    }

    /**
     * @since  3.8.0
     * @since  3.8.11 Moved from `AmazonAutoLinks_UnitOutput_Utility`.
     * @since  5.2.6  Renamed from `___getBrowseNodes()` and changed the visibility scope from private to public.
     * @param  array  $aBrowseNodes
     * @return array
     */
    static public function getBrowseNodeList( array $aBrowseNodes ) {
        $_aLocaleCodes = AmazonAutoLinks_Locales::getLocales();
        $_aList        = array();
        if ( empty( $aBrowseNodes ) ) {
            return $_aList;
        }
        foreach( $aBrowseNodes as $_asBrowseNode ) {
            if ( is_scalar( $_asBrowseNode ) ) {
                $_aBreadCrumb = explode( ' > ', $_asBrowseNode );
                $_sFirstItem  = reset( $_aBreadCrumb );
                if ( in_array( $_sFirstItem, $_aLocaleCodes, true ) ) {
                    unset( $_aBreadCrumb[ 0 ] );
                    $_aBreadCrumb = array_values( $_aBreadCrumb );  // re-index to start from 0
                }
                $_aList[] = $_aBreadCrumb;
                continue;
            }
            $_aList[] = self::___getNodeBreadcrumb( $_asBrowseNode, array() );
        }
        return $_aList;
    }
        /**
         * @param  array  $aBrowseNode
         * @param  array  $aBreadcrumb
         * @return array
         * @since  3.8.0
         * @since  3.8.11 Moved from `AmazonAutoLinks_UnitOutput_Utility`
         * @since  5.2.6  Change the `$sBreadcrumb` parameter to `$aBreadcrumb` and the return type from string to array.
         */
        static private function ___getNodeBreadcrumb( array $aBrowseNode, array $aBreadcrumb=array() ) {
            $_sName     = self::getElement( $aBrowseNode, 'DisplayName' );
            if ( ! $_sName ) {  // There are cases that the `Name` does not exist.
                return $aBreadcrumb;
            }
            array_unshift( $aBreadcrumb, $_sName );
            $_aAncestor = self::getElementAsArray( $aBrowseNode, array( 'Ancestor' ) );
            if ( ! empty( $_aAncestor ) ) {
               $aBreadcrumb = self::___getNodeBreadcrumb( $_aAncestor, $aBreadcrumb );
            }
            return $aBreadcrumb;
        }

    /**
     * Constructs the features list output from an array storing features.
     * @since  3.8.0
     * @since  3.8.11 Moved from `AmazonAutoLinks_UnitOutput_Utility`
     * @param  array  $aFeatures
     * @return string
     */
    static public function getFeatures( array $aFeatures ) {
        $_sList = "";
        foreach( $aFeatures as $_sFeature ) {
            if ( ! trim( $_sFeature ) ) {
                continue;
            }
            $_sList .= "<li class='feature'>$_sFeature</li>";
        }
        return $_sList
            ? "<ul class='features'>" . $_sList . "</ul>"
            : '';
    }

    /**
     * @param  array  $aItem
     * @return string
     * @since  3.9.0
     */
    static public function getContent( array $aItem ) {
        $_aFeatures = self::getElementAsArray( $aItem, array( 'ItemInfo', 'Features', 'DisplayValues' ) );
        $_sContents = implode( ' ', $_aFeatures );
        return trim( $_sContents )
            ? "<div class='amazon-product-content'>"
                . $_sContents
            . "</div>"
            : '';
    }

    /**
     * @param  array   $aItem
     * @return boolean
     * @since  3.9.0
     */
    static public function isPrime( array $aItem ) {
        return self::isDeliveryEligible( $aItem, array( 'DeliveryInfo', 'IsPrimeEligible' ) );
    }

    /**
     * @param  array   $aItem
     * @param  array   $aKeys
     * @return boolean
     * @since  3.10.0
     */
    static public function isDeliveryEligible( array $aItem, array $aKeys=array( 'DeliveryInfo', 'IsPrimeEligible' ) ) {
        $_bHasPrime     = false;
        $_aOfferListing = self::getElementAsArray( $aItem, array( 'Offers', 'Listings' ) );
        foreach( $_aOfferListing as $_aOffer ) {
            if ( self::getElement( $_aOffer, $aKeys, false ) ) {
                return true;
            }
        }
        return $_bHasPrime;
    }
    /**
     * Extracts a rating from a given string.
     * e.g.
     * 4.5 out of 5 stars -> 4.5 -> 45
     * 4,7 su 5 stelle -> 4.7 -> 47
     *
     * @param  $sString
     * @return integer|null
     * @since  3.9.0
     */
    static public function getRatingExtracted( $sString ) {
        preg_match(
            '/\d[.,]\d/', // needle
            $sString,   // subject
            $_aMatches
        );
        if ( ! isset( $_aMatches[ 0 ] ) ) {
            return null;
        }
        $_sRating = str_replace( ',', '.', $_aMatches[ 0 ] );
        return is_numeric( $_sRating )
            ? ( integer ) ( $_sRating * 10 )
            : null;
    }

    /**
     * e.g. https://images-na.ssl-images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-5-0.gif
     * @since  3.9.0
     * @param  integer $iRating    IMPORTANT! Not a decimal number.  e.g. 45 (not 4.5)
     * @return string
     */
    static public function getRatingStarImageURL( $iRating ) {

        // Only .5 numbers can be displayed.
        // e.g. 42 -> no image -> must be converted to 4.5
        $_iRating = $iRating * 2 ;
        $_iRating = round( $_iRating, -1 );
        $_iRating = $_iRating / 2 / 10;
        $_dRating = number_format( $_iRating, 1, '.', '');
        $_sRating = str_replace( '.', '-', $_dRating );

        // https://images-na.ssl-images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-5-0.gif
        return 'https://images-na.ssl-images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-'
            . $_sRating . '.gif';

    }

    /**
     * @param  integer $iRating
     * @param  string  $sReviewURL
     * @param  integer $iReviewCount
     * @param  boolean $bUseSVGCache
     * @since  3.9.0
     * @since  4.6.0   Added the `$bUseSVGCache` parameter.
     * @return string
     */
    static public function getRatingOutput( $iRating, $sReviewURL='', $iReviewCount=null, $bUseSVGCache=true ) {
        $_sStarImageURL = self::getRatingStarImageURL( $iRating );
        $_sAAttributes  = self::getAttributes(
            array(
                'href'     => $sReviewURL,
                'target'   => '_blank',
                'rel'      => 'nofollow noopener',
            )
        );
        $_dRating      = number_format( $iRating / 10, 1, '.', '');
        $_sAlt         = sprintf( __( '%1$s out of 5 stars', 'amazon-auto-links' ), $_dRating );
        $_sReviewCount = null !== $iReviewCount
            ? ( $sReviewURL
                    ? "(<a " . $_sAAttributes . ">" .$iReviewCount . "</a>)"
                    : "(" . $iReviewCount . ")"
                )
            : '';
        $_oSVG             = new AmazonAutoLinks_SVGGenerator_RatingStar( $bUseSVGCache, $_sAlt, $_sStarImageURL );
        $_sIconRatingStars = $_oSVG->get( $iRating );
        return "<div class='crIFrameNumCustReviews' data-rating='" . intval( $iRating ) . "' data-review-count='" . intval( $iReviewCount ) . "' data-review-url='" . esc_url( $sReviewURL ) . "'>"
                    . "<span class='crAvgStars'>"
                        . "<span class='review-stars'>"
                            . ( $sReviewURL
                                ? "<a " . $_sAAttributes . ">"
                                    . $_sIconRatingStars
                                . "</a>"
                                : $_sIconRatingStars
                            )
                        . "</span>"
                        . "<span class='review-count'>"
                            . $_sReviewCount
                        . "</span>"
                    . "</span>"
               . "</div>";
        /* @deprecated 4.6.0 Use SVG
        $_sImgAttributes = self::getAttributes(
            array(
                'src'    => esc_url( $_sStarImageURL ),
                'alt'    => $_sAlt,
                'title'  => $_sAlt,
            )
        );
        return "<div class='crIFrameNumCustReviews'>"
                   . "<span class='crAvgStars'>"
                       . "<span class='review-stars' style='display:inline-block;'>"
                            . ( $sReviewURL
                                ? "<a " . $_sAAttributes . ">"
                                    . "<img " . $_sImgAttributes . " alt='" . __( 'Rating stars', 'amazon-auto-links' ) . "'/>"
                                . "</a>"
                                : "<img " . $_sImgAttributes . "/>"
                            )
                       . "</span>"
                       . "<span class='review-count'>"
                            . $_sReviewCount
                       . "</span>"
                   . "</span>"
               . "</div>";*/
    }

        /**
         * @param  integer $iRating Two digits representing the rating such as 50 for 5 stars, 35 for 3.5 stars.
         * @return string
         * @since  4.6.0   Uses the SVG image instead of the one from the amazon site.
         * @deprecated 4.6.0
         */
        /*static private function ___getRatingStarsSVG( $sTitle, $iRating, $sFallbackIMGSRC ) {

            static $_aDefinitions   = array();
            $_sSVGDefinition        = '';
            $_sDefsGradient          = '';

            if ( ! isset( $_aDefinitions[ 'main' ] ) ) {
                $_sSVGDefinition = "<svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width=0 height=0 viewBox='0 0 160 32' display='none'>"
                    . "<g id='amazon-rating-stars'>"
                        . "<path stroke='#E17B21' stroke-width='2' d='M 16.025391 0.58203125 L 11.546875 10.900391 L 0 12.099609 L 8.8222656 19.849609 L 6.1269531 31.382812 L 16.021484 25.294922 L 25.914062 31.382812 L 23.265625 19.849609 L 32 12.099609 L 20.388672 10.900391 L 16.025391 0.58203125 z M 32 12.099609 L 40.822266 19.849609 L 38.126953 31.382812 L 48.021484 25.294922 L 57.914062 31.382812 L 55.265625 19.849609 L 64 12.099609 L 52.388672 10.900391 L 48.025391 0.58203125 L 43.546875 10.900391 L 32 12.099609 z M 64 12.099609 L 72.822266 19.849609 L 70.126953 31.382812 L 80.021484 25.294922 L 89.914062 31.382812 L 87.265625 19.849609 L 96 12.099609 L 84.388672 10.900391 L 80.025391 0.58203125 L 75.546875 10.900391 L 64 12.099609 z M 96 12.099609 L 104.82227 19.849609 L 102.12695 31.382812 L 112.02148 25.294922 L 121.91406 31.382812 L 119.26562 19.849609 L 128 12.099609 L 116.38867 10.900391 L 112.02539 0.58203125 L 107.54688 10.900391 L 96 12.099609 z M 128 12.099609 L 136.82227 19.849609 L 134.12695 31.382812 L 144.02148 25.294922 L 153.91406 31.382812 L 151.26562 19.849609 L 160 12.099609 L 148.38867 10.900391 L 144.02539 0.58203125 L 139.54688 10.900391 L 128 12.099609 z' />"    // the actual vector images of 5 stars
                    . "</g>"
                . "</svg>";
                $_aDefinitions[ 'main' ] = true;
            }
            $_sIDGradient = 'star-fill-gradient-' . $iRating;
            if ( ! isset( $_aDefinitions[ $_sIDGradient ] ) ) {
                $_sDefsGradient = "<defs>"
                        . "<linearGradient id='{$_sIDGradient}'>"
                            . "<stop offset='" . ( $iRating * 2 ) . "%' stop-color='#FFA41C'/>"
                            . "<stop offset='" . ( $iRating * 2 ) . "%' stop-color='transparent' stop-opacity='1' />"
                        . "</linearGradient>"
                    . "</defs>";
                $_aDefinitions[ $_sIDGradient ] = true;
            }
            $_sSVG = "<svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 160 32' enable-background='new 0 0 160 32'>"
                    . "<title>"
                        . esc_html( $sTitle )
                    . "</title>"
                    . $_sDefsGradient
                    . "<use xlink:href='#amazon-rating-stars' fill='url(#{$_sIDGradient})' />"
                    . "<image src='" . esc_url( $sFallbackIMGSRC ) . "' />" // fallback for browsers not supporting SVG
                . "</svg>";
            return $_sSVGDefinition . $_sSVG;

        }*/

    /**
     * @param   array   $aProduct
     * @param   boolean $bUseSVGCache
     * @return  string
     * @since   3.9.0
     * @since   3.10.0  Moved from `AmazonAutoLinks_UnitOutput_search`.
     * @since   4.6.0   Uses embedded SVG instead of an image file. Added the `$bUseSVGCache` parameter.
     */
    static public function getPrimeMark( array $aProduct, $bUseSVGCache=true ) {
        if ( ! self::getElement( $aProduct, 'is_prime' ) ) {
            return '';
        }
        $_sPrimeImageURL = AmazonAutoLinks_Registry::getPluginURL( AmazonAutoLinks_Unit_Loader::$sDirPath . '/asset/image/unit/prime.gif', true );
        $_oSVGRatingStar = new AmazonAutoLinks_SVGGenerator_PrimeIcon( $bUseSVGCache, __( 'Available for Amazon Prime', 'amazon-auto-links' ), $_sPrimeImageURL );
        return "<span class='amazon-prime'>"
                . $_oSVGRatingStar->get()
            . "</span>";

        // @deprecated 3.6.0 Now use an embedded SVG icon
        /*$_aAttributes    = array(
            'role'  => 'img',
            'class' => 'prime-icon',
            'style' => 'background-image: url(' . esc_url( $_sPrimeImageURL ) . ');'
        );
        return "<span class='amazon-prime'>"
                . "<i " . self::getAttributes( $_aAttributes ) . "></i>"
            . "</span>";*/
    }
    
}