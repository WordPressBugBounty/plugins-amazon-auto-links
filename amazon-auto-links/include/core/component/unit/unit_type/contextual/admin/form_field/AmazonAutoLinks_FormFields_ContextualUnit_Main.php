<?php
/**
 * Auto Amazon Links
 *
 * https://en.michaeluno.jp/amazon-auto-links/
 * Copyright (c) 2013-2022 Michael Uno; Licensed GPLv2
 *
 */

/**
 * Provides the definitions of form fields for the main section of the 'contextual' unit type.
 *
 * @since  3.5.0
 * @since  4.5.0    Changed the parent class from `AmazonAutoLinks_FormFields_Base` to `AmazonAutoLinks_FormFields_Unit_Base`.
 */
class AmazonAutoLinks_FormFields_ContextualUnit_Main extends AmazonAutoLinks_FormFields_Unit_Base {

    /**
     * Returns field definition arrays.
     *
     * Pass an empty string to the parameter for meta box options.
     *
     * @return      array
     */
    public function get( $sFieldIDPrefix='' ) {

        $_aFields = array(
            array(
                'field_id'      => $sFieldIDPrefix . 'criteria',
                'title'         => __( 'Criteria', 'amazon-auto-links' ),
                'type'          => 'revealer',
                'select_type'   => 'checkbox',
                'label'         => array(
                    'post_title'        => __( 'Post Title', 'amazon-auto-links' ),
                    'taxonomy_terms'    => __( 'Taxonomy Terms', 'amazon-auto-links' ),
                    'breadcrumb'        => __( 'Breadcrumb', 'amazon-auto-links' ),
                    'site_title'        => __( 'Site Title', 'amazon-auto-links' ),
                    'url_query'         => __( 'URL Query', 'amazon-auto-links' ),
                    'post_meta'         => __( 'Post Meta', 'amazon-auto-links' ),
                ),
                'default'       => array(
                    'post_title'        => true,
                    'taxonomy_terms'    => true,
                    'breadcrumb'        => false,
                    'site_title'        => false,
                    'url_query'         => false,
                    'post_meta'         => false,
                ),
                'selectors'         => array(
                    'url_query'   => '.fieldrow_http_query_parameters',
                    'post_meta'   => '.fieldrow_post_meta_keys',
                ),
            ),
            array( // 5.4.0
                'field_id'      => $sFieldIDPrefix . 'http_query_parameters',
                'title'         => __( 'URL Query Keys', 'amazon-auto-links' ),
                'type'          => 'text',
                'attributes'    => array(
                    'class' => 'width-full',
                ),
                'repeatable'    => true,
                'tip'           => array(
                    __( 'The parameter of the GET HTTP request seen in URLs to be used as a search keyword.', 'amazon-auto-links' ),                ),
                'hidden'        => true,
                'class'         => array(
                    'fieldrow'  => 'fieldrow_http_query_parameters',
                ),
            ),
            array( // 5.4.0
                'field_id'      => $sFieldIDPrefix . 'post_meta_keys',
                'title'         => __( 'Post Meta Keys', 'amazon-auto-links' ),
                'type'          => 'text',
                'attributes'    => array(
                    'class' => 'width-full',
                ),
                'repeatable'    => true,
                'tip'           => array(
                    __( 'Set the post meta key names known as custom fields whose value will be used as a search keyword.', 'amazon-auto-links' ),
                ),
                'hidden'        => true,
                'class'         => array(
                    'fieldrow'  => 'fieldrow_post_meta_keys',
                ),
            ),
            array( // 5.4.0
                'field_id'      => $sFieldIDPrefix . 'concatenate_keywords',
                'title'         => __( 'Concatenate', 'amazon-auto-links' ),
                'type'          => 'checkbox',
                'label'         => __( 'Concatenate multiple keywords to perform search at once rather than with a single word multiple times.', 'amazon-auto-links' ),
                // 'hidden'        => true,
                // 'class'         => array(
                //     'fieldrow'  => 'fieldrow_concatenate_keywords',
                // ),
            ),
            array(
                'field_id'      => $sFieldIDPrefix . 'additional_keywords',
                'title'         => __( 'Additional Keywords', 'amazon-auto-links' ),
                'type'          => 'text',
                'attributes'    => array(
                    'class' => 'width-full',
                ),
                'tip'           => array(
                    __( 'Add additional search keywords, separated by commas.', 'amazon-auto-links' ),
                    ' e.g. <code>' . __( 'laptop, desktop', 'amazon-auto-links' ) . '</code>',
                ),
            ),
            array( // 3.12.0
                'field_id'      => $sFieldIDPrefix . 'excluding_keywords',
                'title'         => __( 'Keywords to Exclude', 'amazon-auto-links' ),
                'type'          => 'text',
                'attributes'    => array(
                    'class' => 'width-full',
                ),
                'tip'           => array(
                    __( 'Specify keywords to exclude from search keywords, separated by commas.', 'amazon-auto-links' ),
                    ' e.g. <code>' . __( 'test, demo', 'amazon-auto-links' ) . '</code>',
                ),
            ),
            $this->___getCountryField( $sFieldIDPrefix ),
        );
        return $_aFields;

    }
        /**
         * @param  string $sFieldIDPrefix
         * @return array
         * @since  4.7.4
         */
        private function ___getCountryField( $sFieldIDPrefix ) {

            $_aLabels = $this->getAdWidgetAPILocaleFieldLabels() + $this->getPAAPILocaleFieldLabels();
            $_aBase   = array(
                'field_id'          => $sFieldIDPrefix . 'country',
                'type'              => 'select',
                'title'             => __( 'Country', 'amazon-auto-links' ),
                'label'             => $_aLabels,
                'default'           => AmazonAutoLinks_Option::getInstance()->getMainLocale(),
                'description'       => AmazonAutoLinks_Message::getLocaleFieldGuide() . ' ' . AmazonAutoLinks_Message::get( 'locale_field_tip_paapi' ),
            );
            // In the widget page in WordPress 5.8 or above, the select2 field type does not load
            if ( AmazonAutoLinks_Registry::$aPostTypes[ 'unit' ] !== $this->getHTTPQueryGET( 'post_type' ) ) {
                return $_aBase;
            }
            return array(
                'type'              => 'select2',
                'icon'              => $this->getLocaleIcons( array_keys( $_aLabels ) ),
            ) + $_aBase;
        }

}