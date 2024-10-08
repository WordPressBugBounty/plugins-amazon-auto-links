<?php
/**
 * Auto Amazon Links
 * 
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno
 */

/**
 * Handles outputs of the plugin.
 * 
 * @since 2
 * @since 3 Changed the name from `AmazonAutoLinks_Units`
*/
class AmazonAutoLinks_Output extends AmazonAutoLinks_WPUtility {
    
    /**
     * Stores unit arguments.
     */
    public $aArguments = array();

    /**
     * Stores the raw arguments.
     * @remark Used for JavaScript loading.
     * Also, since v4.3.4 the unit option class accepts raw options to be passed. The 'item_format', 'image_format', 'title_format', 'unit_format' options need to use this to suppress the default.
     * @var    array
     * @since  3.6.0
     */
    private $___aRawArguments = array();

    /**
     * Instantiates the class and returns the object.
     *
     * This is for calling methods in one line like
     * ```
     * $_sOutput = AmazonAutoLinks_Output::getInstance()->render();
     * ```
     *
     * @since  2.1.1
     * @param  array $aArguments
     * @return AmazonAutoLinks_Output
     */
    static public function getInstance( $aArguments ) {
        // return new static( $aArguments ); Using static, extended classes don't have to declare the same method but the late static bindings are supported in PHP 5.3 or above.
        return new self( $aArguments );
    }

    /**
     * Sets up properties.
     *
     * @param array $aArguments
     * @since 2.0.0
     */
    public function __construct( $aArguments ) {
        $this->___aRawArguments = $this->getAsArray( $aArguments ); // [3.6.0]
        $_oFormatter            = new AmazonAutoLinks_Output___ArgumentFormatter( $aArguments );
        $this->aArguments       = $_oFormatter->get();
    }

    /**
     * Renders the output.
     */
    public function render() {
        echo $this->get();
    }

    /**
     * Retrieves the unit output.
     * @since  2
     * @since  3      Changed the name from `getOutput()`.
     * @return string
     */
    public function get() {

        /**
         * Allows the Ajax output to be returned.
         * @since 4.3.0
         */
        $_sPreOutput = apply_filters( 'aal_filter_pre_unit_output', $this->aArguments, $this->___aRawArguments );
        if ( $_sPreOutput ) {
            return $_sPreOutput;
        }

        $_sOutput = $this->___getOutput();
        return $this->___isWithoutOuterContainer()
            ? $_sOutput
            : "<div class='" . esc_attr( ( string ) apply_filters( 'aal_filter_plugin_slug_output', 'amazon-auto-links' ) ) . "'>"  // [5.3.10] filter
                    . $_sOutput
                . "</div>";

    }
        /**
         * @since  4.3.4
         * @return boolean
         * @remark Some templates use the filter.
         */
        private function ___isWithoutOuterContainer() {
            $_bNoOuterContainer = $this->getElement( $this->aArguments, array( '_no_outer_container' ) );
            return ( boolean ) apply_filters( 'aal_filter_output_is_without_outer_container', $_bNoOuterContainer, $this->aArguments );
        }

        /**
         * @since  3.5.0
         * @return string
         */
        private function ___getOutput() {

            $_aIDs    = $this->getAsArray( $this->aArguments[ '_unit_ids' ] );

            // For cases by direct arguments such as shortcode, PHP functions etc.
            if ( empty( $_aIDs ) || ! empty( $this->aArguments[ 'unit_type' ] ) ) {
                return $this->___getOutputByUnitType( $this->___getUnitTypeFromArguments( $this->aArguments ), $this->aArguments );
            }

            // If called by unit IDs,
            $_sOutput = '';
            foreach( $_aIDs as $_iID ) {
                $_sOutput .= $this->___getOutputByID( $_iID );
            }
            return $_sOutput;

        }

        /**
         * Returns the unit output by post (unit) ID.
         *
         * The auto-insert sets the 'id' as array storing multiple ids.
         * But this method is called per ID so the ID should be discarded.
         * If the unit gets deleted, auto-insert causes an error for not finding the options.
         *
         * @param  integer $iPostID
         * @return string
         */
        private function ___getOutputByID( $iPostID ) {
            $_aUnitOptions = array(
                    // Required keys
                    'id'        => $iPostID,
                    'unit_type' => get_post_meta( $iPostID, 'unit_type', true ), // [4.3.4]
                )
                + $this->aArguments;
            return $this->___getOutputByUnitType( $_aUnitOptions[ 'unit_type' ], $_aUnitOptions );
        }

            /**
             * Determines the unit type from the given argument array.
             * @since  3
             * @remark When the arguments are passed via shortcodes, the keys get all converted to lower-cases by the WordPress core.
             * @param  array  $aArguments
             * @return string The unit type slug.
             */
            private function ___getUnitTypeFromArguments( $aArguments ) {
                return isset( $aArguments[ 'unit_type' ] )
                    ? $aArguments[ 'unit_type' ]
                    : apply_filters( 'aal_filter_detected_unit_type_by_arguments', 'unknown', $aArguments );
            }

            /**
             * Generates product outputs by the given unit type.
             *
             * @remark All the outputs go through this method.
             * @param  string $sUnitType
             * @param  array  $aUnitOptions
             * @return string The unit output
             */
            private function ___getOutputByUnitType( $sUnitType, array $aUnitOptions ) {
                $_aRegisteredUnitTypes = $this->getAsArray( apply_filters( 'aal_filter_registered_unit_types', array() ) );
                if ( in_array( $sUnitType, $_aRegisteredUnitTypes, true ) ) {
                    // Each unit type hooks into this filter hook and generates their outputs.
                    return trim( apply_filters_ref_array( 'aal_filter_unit_output_' . $sUnitType, array( '', $aUnitOptions, &$_oUnitOption ) ) );
                }
                return apply_filters( 'aal_filter_unit_output_unknown', $this->___getUnknownUnitTypeMessage( $sUnitType, $aUnitOptions ), $aUnitOptions );
            }
                /**
                 * @param  string $sUnitType
                 * @param  array  $aUnitOptions
                 * @return string
                 * @since  4.3.5
                 */
                private function ___getUnknownUnitTypeMessage( $sUnitType, array $aUnitOptions ) {
                    $_oOption    = AmazonAutoLinks_Option::getInstance();
                    $_iShowError = ( integer ) $_oOption->get( 'unit_default', 'show_errors' ); // 0: do not show, 1: show, 2: show as HTML comment
                    $_iShowError = ( integer ) apply_filters( 'aal_filter_unit_show_error_mode', $_iShowError, $aUnitOptions );
                    if ( ! $_iShowError ) {
                        return '';
                    }
                    $_sMessage = AmazonAutoLinks_Registry::NAME . ': '
                             . sprintf(
                                 __( 'Could not resolve the given unit type, %1$s.', 'amazon-auto-links' ),
                                $sUnitType
                             )
                             . ' ' . __( 'Please be sure to update the auto-insert definition if you have deleted the unit.', 'amazon-auto-links' );
                    return 1 === $_iShowError
                        ? "<p class='error'>{$_sMessage}</p>"
                        : "<!-- {$_sMessage} -->";
                }

    /* Deprecated Methods */

    /**
     * @deprecated 3
     */
    public function getOutput() {
        return $this->get();
    }

}