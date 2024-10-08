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
 * Provides methods for template options.
 * 
 * @since 3
 */
class AmazonAutoLinks_TemplateOption extends AmazonAutoLinks_Option_Base {

    /**
     * Caches the active templates.
     * @since 3
     */
    private static $_aActiveTemplates = array();
    
    /**
     * Represents the structure of the template option array.
     * @since 3
     */
    public static $aStructure_Template = array(

        'relative_dir_path' => null,    // (string)
        'id'                => null,    // (string)
        'old_id'            => null,    // (string) v2 id (strID)
        'is_active'         => null,    // (boolean)
        'is_valid'          => null,    // (boolean)  [4.6.17+] indicates whether the template ID is valid or not. Don't rely on this value of saved data as the site might hae migrated while storing the old site information.
        'should_remove'     => null,    // (boolean)  [4.6.17+] indicates whether the template should be removed
        'warnings'          => array(), // (array)    [4.6.17+] stores warnings when the template has an issue
        'index'             => null,    // (integer)
        'name'              => null,    // (string)   will be used to list templates in options.
        
        // assigned at the load time
        'template_path'     => null,    // (string) template.php
        'dir_path'          => null,    // (string)
        
        // for listing table
        'description'       => null,
        'version'           => null,
        'author'            => null,
        'author_uri'        => null,
                    
    );
    
    /**
     * Represents the v2 template option structure.
     * @var array
     */
    static public $aStructure_Template_Legacy = array(
        'strCSSPath'        => null,
        'strTemplatePath'   => null,
        'strDirPath'        => null,
        'strFunctionsPath'  => null,
        'strSettingsPath'   => null,
        'strThumbnailPath'  => null,
        'strName'           => null,
        'strID'             => null,
        'strDescription'    => null,
        'strTextDomain'     => null,
        'strDomainPath'     => null,
        'strVersion'        => null,
        'strAuthor'         => null,
        'strAuthorURI'      => null,
        'fIsActive'         => null,    
    );
    
    /**
     * Stores the self instance.
     */
    static public $oSelf;

    /**
     * @var    string    The base path that a template relative path is based on.
     * @remark The value is set in the constructor.
     * @since  4.6.17
     */
    static public $sBasePath;

    /**
     * Returns an instance of the self.
     * 
     * @remark To support PHP 5.2, this method needs to be defined in each extended class
     * as in static methods, it is not possible to retrieve the extended class name in a base class in PHP 5.2.x.
     * @return AmazonAutoLinks_TemplateOption
     */
    static public function getInstance( $sOptionKey='' ) {
        
        if ( isset( self::$oSelf ) ) {
            return self::$oSelf;
        }
        $sOptionKey = $sOptionKey 
            ? $sOptionKey
            : AmazonAutoLinks_Registry::$aOptionKeys[ 'template' ];        
        
        $_sClassName = __Class__;
        self::$oSelf = new $_sClassName( $sOptionKey );            
        return self::$oSelf;
        
    }

    /**
     * @param string $sOptionKey
     * @since 4.6.17
     */
    public function __construct( $sOptionKey ) {
        self::$sBasePath = dirname( WP_CONTENT_DIR );
        parent::__construct( $sOptionKey );
    }

    /**
     * Returns the formatted options array.
     * @return array
     */    
    protected function _getFormattedOptions() {
        return parent::_getFormattedOptions() + $this->___getDefaultTemplates();
    }    
        /**
         * @return array Plugin default templates which should be activated upon installation / restoring factory default.
         */
        private function ___getDefaultTemplates() {
            $_aDirPaths = array(
                dirname( $this->getDefaultTemplatePathByUnitType( '' ) ),
            );
            $_iIndex     = 0;
            $_aTemplates = array();
            foreach( $_aDirPaths as $_sDirPath ) {
                $_aTemplate = $this->getTemplateArrayByDirPath( $_sDirPath );
                if ( ! $_aTemplate ) {
                    continue;
                }
                $_aTemplate[ 'is_active' ] = true;
                $_aTemplate[ 'index' ] = ++$_iIndex;
                $_aTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;
            }
            return $_aTemplates;
        }

    /**
     * Returns an array of common templates.
     *
     * Common templates are the plugin buil-in ones and auto-load without requiring user to activate.
     *
     * @since 3.6.0
     */
    public function getCommonTemplates() {
        if ( ! empty( self::$___aCommonTemplates ) ) {
            return self::$___aCommonTemplates;
        }

        $_aDirPaths  = array(
            '_common' => AmazonAutoLinks_Registry::$sDirPath . '/template/_common',
        );
        $_aTemplates = array();
        foreach( $_aDirPaths as $_sID => $_sDirPath ) {
            $_aTemplates[ $_sID ] = array(
                'is_active' => true,
                'dir_path'  => wp_normalize_path( $_sDirPath ),
                'id'        => $_sID,
            );
        }

        self::$___aCommonTemplates = $_aTemplates;
        return $_aTemplates;
    }
        /**
         * Stores caches of common templates.
         * @var   array
         * @since 3.6.0
         */
        static private $___aCommonTemplates = array();

    /**
     * Returns an array that holds arrays of activated template information.
     * 
     * @since  ?
     * @since  3           moved from the templates class.
     * @remark The visibility scope is public since this is accessed from the template loader class.
     */
    public function getActiveTemplates() {
        
        if ( ! empty( self::$_aActiveTemplates ) ) {
            return self::$_aActiveTemplates;
        }
                
        $_aActiveTemplates = $this->___getActiveTemplatesExtracted( $this->get() );    // get() gives saved all templates

        // Cache
        self::$_aActiveTemplates = $_aActiveTemplates;
        return $_aActiveTemplates;
        
    }

        /**
         * @param  array $aTemplates
         * @return array
         * @since  3.3.0
         */
        private function ___getActiveTemplatesExtracted( array $aTemplates ) {

            $_aActiveTemplates = array();
            $_aTemplateDirs    = $this->___getTemplateDirs();
            foreach( $aTemplates as $_sID => $_aTemplate ) {

                // Skip inactive templates.
                if ( ! $this->getElement( $_aTemplate, 'is_active' ) ) {
                    continue;
                }

                $_sID       = wp_normalize_path( untrailingslashit( $_sID ) );
                $_aTemplate = $this->___getTemplateArrayFormatted( $_aTemplate );

                if ( empty( $_aTemplate ) ) {   // can be false
                    continue;
                }

                // 4.1.0 there are cases that while the template is still active, a custom template directory is deleted manually via FTP or whatever
                if ( ! isset( $_aTemplate[ 'dir_path' ] ) || ! file_exists( $_aTemplate[ 'dir_path' ] ) ) {
                    continue;
                }

                // 5.0.7 Skip if it is not in the template directory list. This prevents loading custom templates by deactivated plugins.
                if ( ! in_array( $_aTemplate[ 'dir_path' ], $_aTemplateDirs, true ) ) {
                    continue;
                }

                // Backward compatibility for the v2 options structure.
                // If the id is not a relative dir path,
                if ( 
                    $_sID !== $_aTemplate[ 'relative_dir_path' ] 
                ) {

                    // If the same ID already exists, set the old id.
                    if ( isset( $aTemplates[ $_aTemplate[ 'relative_dir_path' ] ] ) ) {
                        $aTemplates[ $_aTemplate[ 'relative_dir_path' ] ][ 'old_id' ] = $_sID;
                    } else {                    
                        $aTemplates[ $_aTemplate[ 'relative_dir_path' ] ] = $_aTemplate;
                    }
                    
                }

                // 4.0.6+ Broken custom templates misses some necessary array elements
                if ( ! isset( $_aTemplate[ 'name' ] ) ) {
                    continue;
                }

                $_aTemplate[ 'is_active' ]  = true;
                $_aActiveTemplates[ $_sID ] = $_aTemplate;
                
            }
            return $_aActiveTemplates;
            
        }
       
        /**
         * Formats the template array.
         * 
         * Takes care of formatting change through version updates.
         * 
         * @since  3
         * @since  4.0.2         Changed the scope to private. Renamed from `_formatTemplateArray()`.
         * @param  array         $aTemplate
         * @return array|boolean Formatted template array. If the passed value is not an array
         * or something wrong with the template array, false will be returned.
         */
        private function ___getTemplateArrayFormatted( $aTemplate ) {
         
            if ( ! is_array( $aTemplate ) ) { 
                return false; 
            }
            
            $aTemplate = $aTemplate + self::$aStructure_Template;
            $aTemplate = $this->___getTemplateArrayFormattedLegacy( $aTemplate );
                       
            // Format elements
            $aTemplate[ 'relative_dir_path' ] = $this->getElement(
                $aTemplate,
                'relative_dir_path',
                $this->getRelativePathTo( self::$sBasePath, $aTemplate[ 'strDirPath' ] )
            );
            $aTemplate[ 'relative_dir_path' ] = wp_normalize_path( $aTemplate[ 'relative_dir_path' ] );

            // Set the directory path every time the page loads. Do not store in the data base.
            // This path is absolute so when the user moves the site, the value will be different.
            $aTemplate[ 'dir_path' ]          = $this->getElement(
                $aTemplate,
                'dir_path',
                $this->getAbsolutePathFromRelative( $aTemplate[ 'relative_dir_path' ], self::$sBasePath )
            );
            $aTemplate[ 'dir_path' ]          = wp_normalize_path( realpath( untrailingslashit( $aTemplate[ 'dir_path' ] ) ) );

            // @see https://wordpress.org/support/topic/open_basedir-restriction-error-3/
            if ( empty( $aTemplate[ 'dir_path' ] ) ) {
                return false;
            }

            // Check required files. Consider the possibility that the user may directly delete the template files/folders.
            $_aRequiredFiles = array(
                $aTemplate[ 'dir_path' ] . '/' . 'style.css',
                $aTemplate[ 'dir_path' ] . '/' . 'template.php',
            );
            if ( ! $this->doFilesExist( $_aRequiredFiles ) ) {
                return false;
            }

            // Other elements
            $aTemplate[ 'template_path' ]      = $this->getElement(
                $aTemplate,
                'template_path',
                $aTemplate[ 'dir_path' ] . '/' . 'template.php'
            );
            $aTemplate[ 'template_path' ]      = wp_normalize_path( $aTemplate[ 'template_path' ] );

            $aTemplate[ 'id' ]                 = $this->getElement(
                $aTemplate,
                'id',
                $aTemplate[ 'relative_dir_path' ]
            );
            $aTemplate[ 'id' ]                 = untrailingslashit( $aTemplate[ 'id' ] );
            $aTemplate[ 'old_id' ]             = $this->getElement(
                $aTemplate,
                'old_id',
                $aTemplate[ 'strID' ]
            );

            // For uploaded templates
            $aTemplate[ 'name' ]               = $this->getElement(
                $aTemplate,
                'name',
                $aTemplate[ 'strName' ]
            );
            $aTemplate[ 'description' ]        = $this->getElement(
                $aTemplate,
                'description',
                $aTemplate[ 'strDescription' ]
            );
            $aTemplate[ 'version' ]            = $this->getElement(
                $aTemplate,
                'version',
                $aTemplate[ 'strVersion' ]
            );
            $aTemplate[ 'author' ]             = $this->getElement(
                $aTemplate,
                'author',
                $aTemplate[ 'strAuthor' ]
            );
            $aTemplate[ 'author_uri' ]         = $this->getElement(
                $aTemplate,
                'author_uri',
                $aTemplate[ 'strAuthorURI' ]
            );
            $aTemplate[ 'is_active' ]          = $this->getElement(
                $aTemplate,
                'is_active',
                $aTemplate[ 'fIsActive' ]
            );

            return $aTemplate;

        }
            /**
             * Make the passed template array compatible with the format of v2.x or below.
             *
             * @return array|false            The formatted template array or false if the necessary file paths do not exist.
             * @since  ?
             * @since  4.0.0       Renamed from `_formatTemplateArrayLegacy()`.
             */
            private function ___getTemplateArrayFormattedLegacy( array $aTemplate ) {

                $aTemplate = $aTemplate + self::$aStructure_Template_Legacy;
                $aTemplate[ 'strDirPath' ] = $aTemplate[ 'strDirPath' ]    // check if it's not missing
                    ? $aTemplate[ 'strDirPath' ]
                    : dirname( ( string ) $aTemplate[ 'strCSSPath' ] );

                $aTemplate[ 'strTemplatePath' ] = $aTemplate[ 'strTemplatePath' ]    // check if it's not missing
                    ? $aTemplate[ 'strTemplatePath' ]
                    : dirname( ( string ) $aTemplate[ 'strCSSPath' ] ) . '/' . 'template.php';

                return $aTemplate;

            }

    /**
     * Retrieves the label(name) of the template by template id
     *
     * @remark            Used when rendering the post type table of units.
     */
    public function getTemplateNameByID( $sTemplateID ) {
        // Not using getActiveTemplates() here because there could be missed templates by getUploadedTemplates()
        // after the template ID generation mechanism is changed in 4.6.17. Those unloaded templates are stored in $this->>get()
        return $this->getElement( $this->get() + $this->getUploadedTemplates(), array( $sTemplateID, 'name' ), '' );
        // @deprecated 4.6.17
        // return $this->get(
        //     array( $sTemplateID, 'name' ), // dimensional keys
        //     '' // default
        // );
    }


    /**
     * Returns an array holding active template labels.
     * @since  3
     */
    public function getActiveTemplateLabels() {
        $_aLabels = array();
        foreach( $this->getActiveTemplates() as $_aTemplate ) {
            $_aLabels[ $_aTemplate[ 'id' ] ] = $_aTemplate[ 'name' ];
        }
        return $_aLabels;
    }

    /**
     * Returns an array holding usable template labels,
     * mainly consisting of the active templates but the default template in addition.
     *
     * @remark Used for template select option field.
     * @since  4.0.4
     * @retun  array
     */
    public function getUsableTemplateLabels() {
        $_aLabels = $this->getActiveTemplateLabels();
        if ( ! empty( $_aLabels ) ) {
            return $_aLabels;
        }
        $_aDefaultTemplate = $this->getDefaultTemplateByUnitType( '', true );
        $_aLabels[ $_aDefaultTemplate[ 'id' ] ] = $_aDefaultTemplate[ 'name' ];
        return $_aLabels;
    }


    /**
     * @param   string $sUnitType
     * @since   4.0.2
     * @return  string  The default template path
     */
    public function getDefaultTemplatePathByUnitType( $sUnitType ) {
        $_sPath = AmazonAutoLinks_Registry::$sDirPath
            . '/' . 'template'
            . '/' . $this->getDefaultTemplateDirectoryBaseName( $sUnitType )
            . '/' . 'template.php';
        return wp_normalize_path( $_sPath );
    }

    /**
     * @since   4.0.4
     * @return  string the directory base name of the default template.
     */
    public function getDefaultTemplateDirectoryBaseName( $sUnitType='' ) {
        switch ( $sUnitType ) {
            // @deprecated 4.0.0
            // Now all unit types default to the List template
            // case 'email':               // 3.5.0+
            // case 'contextual':          // 3.5.0+
            // case 'contextual_widget':   // 3.2.1+
            // case 'similarity_lookup':
            // case 'item_lookup':
            // case 'search':
            // case 'url':                 // 3.2.0+
            //     $_sTemplateDirectoryName = 'search';
            //     break;
            // case 'tag':
            // case 'category':
            //     $_sTemplateDirectoryName = 'category';
            //     break;
            // case 'embed':   // 4.0.0
            default:
                $_sTemplateDirectoryName = 'list';
                break;
        }
        return $_sTemplateDirectoryName;
    }

    /**
     * @param  string  $sUnitType
     * @param  boolean $bExtraInfo   Whether to retrieve extra information
     * @since  4.0.2
     * @return array   The template data array
     */
    public function getDefaultTemplateByUnitType( $sUnitType, $bExtraInfo=false ) {
        return $this->getTemplateArrayByDirPath(
            dirname( $this->getDefaultTemplatePathByUnitType( $sUnitType ) ),
            $bExtraInfo       // no extra info
        );
    }

    /**
     * Returns the plugin default template unit ID by unit type regardless of whether it is activated or not.
     *
     * @param  string $sUnitType
     * @return string
     * @since  3
     */
    public function getDefaultTemplateIDByUnitType( $sUnitType ) {
        $_aDefaultTemplate = $this->getDefaultTemplateByUnitType( $sUnitType );
        return $this->getElement( $_aDefaultTemplate, array( 'id' ), '' );
    }

    /**
     * Caches the uploaded templates.
     *
     * @since  3
     */
    private static $_aUploadedTemplates = array();

    /**
     * Retrieve templates and returns the template information as array.
     *
     * This method is called for the template listing table to list available templates. So this method generates the template information dynamically.
     * This method does not deal with saved options.
     *
     * @return array
     */
    public function getUploadedTemplates() {

        if ( ! empty( self::$_aUploadedTemplates ) ) {
            return self::$_aUploadedTemplates;
        }

        // Construct a template array.
        $_aTemplates = array();
        $_iIndex     = 0;
        foreach( $this->___getTemplateDirs() as $_sDirPath ) {

            $_aTemplate = $this->getTemplateArrayByDirPath( $_sDirPath );
            if ( empty( $_aTemplate ) ) {
                continue;
            }

            // Uploaded templates are supposed to be only called in the admin template listing page.
            // So by default, these are not active.
            $_aTemplate[ 'is_active' ] = false;

            $_aTemplate[ 'index' ] = ++$_iIndex;
            $_aTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;

        }

        self::$_aUploadedTemplates = $_aTemplates;
        return $_aTemplates;

    }

        /**
         * Returns the template array by the given directory path.
         * @since  3
         * @since  3.7.4       Added the `$bAbsolutePath` parameter.
         * @sinc   4.0.0       Deprecated the third parameter $bAbsolutePath as it is not used anywhere.
         * @scope  public      The unit class also accesses this.
         * @return false|array
         */
        public function getTemplateArrayByDirPath( $sDirPath, $bExtraInfo=true ) {

            $sDirPath       = realpath( $sDirPath );
            $_sRelativePath = $this->getTemplateID( $sDirPath ); // at the moment, the ID serves as a relative path
            $_aData         = array(
                'dir_path'              => $sDirPath,
                'relative_dir_path'     => $_sRelativePath,
                'id'                    => $_sRelativePath,
                'old_id'                => md5( $sDirPath ),

                // Backward compatibility
                'strDirPath'            => $sDirPath,
                'strID'                 => md5( $sDirPath ),
            );

            if ( $bExtraInfo ) {
                $_aData[ 'thumbnail_path' ] = $this->_getScreenshotPath( $_aData[ 'dir_path' ] );
                return $this->___getTemplateArrayFormatted(
                    $this->getTemplateData( $_aData[ 'dir_path' ] . '/' . 'style.css' )
                    + $_aData
                );
            }
            return $this->___getTemplateArrayFormatted( $_aData );

        }
            /**
             * @return  string|null
             */
            protected function _getScreenshotPath( $sDirPath ) {
                foreach( array( 'jpg', 'jpeg', 'png', 'gif' ) as $sExt ) {
                    if ( file_exists( $sDirPath . '/' . 'screenshot.' . $sExt ) ) {
                        return $sDirPath . '/' . 'screenshot.' . $sExt;
                    }
                }
                return null;
            }

        /**
         * Stores the read template directory paths.
         * @since       3
         */
        static private $_aTemplateDirs = array();

        /**
         * Returns an array holding the template directories.
         *
         * @since  3
         * @return array Contains list of template directory paths.
         */
        private function ___getTemplateDirs() {

            if ( ! empty( self::$_aTemplateDirs ) ) {
                return self::$_aTemplateDirs;
            }
            foreach( $this->___getTemplateContainerDirs() as $__sTemplateDirPath ) {
                if ( ! @file_exists( $__sTemplateDirPath  ) ) {
                    continue;
                }
                $__abFoundDirs = glob( $__sTemplateDirPath . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR );
                if ( ! is_array( $__abFoundDirs ) ) {    // glob can return false
                    continue;
                }
                self::$_aTemplateDirs = array_merge( $__abFoundDirs, self::$_aTemplateDirs );
            }
            self::$_aTemplateDirs = array_unique( self::$_aTemplateDirs );
            self::$_aTemplateDirs = ( array ) apply_filters( 'aal_filter_template_directories', self::$_aTemplateDirs );
            self::$_aTemplateDirs = array_filter( self::$_aTemplateDirs );    // drops elements of empty values.
            self::$_aTemplateDirs = array_unique( self::$_aTemplateDirs );
            self::$_aTemplateDirs = array_map( 'wp_normalize_path', self::$_aTemplateDirs );    // 5.0.7 This is needed to filter out custom templates of deactivated plugin
            return self::$_aTemplateDirs;

        }
            /**
             * Returns the template container directories.
             * @since  3
             * @return array
             */
            private function ___getTemplateContainerDirs() {
                $_aTemplateContainerDirs    = array();
                $_aTemplateContainerDirs[]  = AmazonAutoLinks_Registry::$sDirPath . '/' . 'template';
                $_aTemplateContainerDirs[]  = get_stylesheet_directory() . '/' . ( string ) apply_filters( 'aal_filter_plugin_slug_autoload_template_directory', 'amazon-auto-links' ); // [5.3.10] filter
                $_aTemplateContainerDirs    = apply_filters( 'aal_filter_template_container_directories', $_aTemplateContainerDirs );
                $_aTemplateContainerDirs    = array_filter( $_aTemplateContainerDirs );    // drop elements of empty values.
                return array_unique( $_aTemplateContainerDirs );
            }


    /**
     * A helper function for the getUploadedTemplates() method.
     *
     * Used when rendering the template listing table.
     * An alternative to get_plugin_data() as some users change the location of the wp-admin directory.
     *
     * @param  string $sCSSPath
     * @return array  An array of template detail information from the given file path.
     */
    protected function getTemplateData( $sCSSPath )    {
        return file_exists( $sCSSPath )
            ? get_file_data(
                $sCSSPath,
                array(
                    'name'           => 'Template Name',
                    'template_uri'   => 'Template URI',
                    'version'        => 'Version',
                    'description'    => 'Description',
                    'author'         => 'Author',
                    'author_uri'     => 'Author URI',
                ),
                '' // context - do not set any
            )
            : array();
    }

    /**
     * Retrieves a template ID from a given directory path.
     *
     * A template ID is a relative path to self::$sBasePath.
     *
     * @param  string $sDirPath
     * @return string
     * @since  4.0.0
     * @remark The visibility scope is public since each template accesses this method to get the ID for filters.
     */
    public function getTemplateID( $sDirPath ) {
        return $this->getRelativePathTo( self::$sBasePath, $sDirPath );
        // @deprecated 4.6.17 Moved to getRelativePathTo().
        // $sDirPath = wp_normalize_path( $sDirPath );
        // $sDirPath = $this->getRelativePath( self::$sBasePath, $sDirPath );
        // return untrailingslashit( $sDirPath );
    }

    /**
     * @param  string $sBasePath
     * @param  string $sDirPath
     * @return string
     * @since  4.6.17
     */
    public function getRelativePathTo( $sBasePath, $sDirPath ) {
        $sDirPath = wp_normalize_path( $sDirPath );
        $sDirPath = $this->getRelativePath( $sBasePath, $sDirPath );
        return untrailingslashit( $sDirPath );
    }

    /**
     * @param  string  $sTemplateID
     * @return boolean
     * @since  4.0.0
     */
    public function isActive( $sTemplateID ) {
        return in_array( $sTemplateID, array_keys( $this->getActiveTemplates() ), true );
    }

    /**
     * @param  string $sTemplateID
     * @return string
     * @since  4.0.0
     */
    public function getPathFromID( $sTemplateID ) {
        foreach( $this->getActiveTemplates() as $_sID => $_aTemplate ) {
            if ( $_sID === trim( $sTemplateID ) ) {
                return wp_normalize_path( $_aTemplate[ 'template_path' ] );
            }
        }
        return '';
    }

    /**
     * Returns a template ID from the given path.
     *
     * Unlike the other methods, this does not care whether the template is activated or not
     * as mostly used when the `template_path` unit argument is given and override the preset template such as `Preview`, `JSON`, and `RSS`.
     *
     * @param  string $sTemplatePath       The `template.php` file path.
     * @return string the template ID
     * @since  4.0.2
     */
    public function getIDFromPath( $sTemplatePath ) {
        if ( is_dir( $sTemplatePath ) ) {
            return $this->getTemplateID( $sTemplatePath );
        }
        return $this->getTemplateID( dirname( $sTemplatePath ) );
        // @deprecated 4.0.2 As non-active template path can be specified
        // foreach( $this->getActiveTemplates() as $_sID => $_aTemplate ) {
        //     $_sTemplatePath = wp_normalize_path( $_aTemplate[ 'template_path' ] );
        //     if ( $_sTemplatePath === $sTemplatePath ) {
        //         return $_sID;
        //     }
        // }
        // return '';
    }

    /**
     * @param   string $sName
     * @return  string the template ID
     * @since   4.0.2
     */
    public function getIDFromName( $sName ) {
        foreach( $this->getActiveTemplates() as $_sID => $_aTemplate ) {
            if ( strtolower( $_aTemplate[ 'name' ] ) === strtolower( trim( $sName ) ) ) {
                return $_sID;
            }
        }
        return '';
    }

    /**
     * Checks whether the template exists in the uploaded templates.
     * @param  string $sTemplateID
     * @return boolean
     * @since  4.6.7
     */
    public function exists( $sTemplateID ) {
        $_aAvailableTemplates = $this->getUploadedTemplates();
        return ! empty( $_aAvailableTemplates[ $sTemplateID ] );
    }


    /**
     * Checks if the template ID is valid or not.
     *
     * Currently, template IDs are a relative path to self::$sBasePath.
     * So if the path does not resolve, it is not valid. It occurs when the site has moved to another host.
     *
     * This method is used to list templates in the listing table.
     * @param  string  $sTemplateID
     * @return boolean
     * @since  4.6.17
     */
    public function isValidID( $sTemplateID ) {
        $_sPath = $this->getAbsolutePathFromRelative( $sTemplateID, self::$sBasePath );
        $_sPath = realpath( $_sPath );
        return file_exists( $_sPath );
    }

    /**
     * Returns all the available templates.
     *
     * This adds the `is_valid` element.
     * @return array
     * @since  4.6.17
     */
    public function getAvailable() {
        static $_aCache;
        if ( isset( $_aCache ) ) {
            return $_aCache;
        }
        $_aTemplates = $this->getActiveTemplates() + $this->getUploadedTemplates();
        foreach( $_aTemplates as $_sID => $_aTemplate ) {
            $_aTemplates[ $_sID ][ 'is_valid' ] = $this->isValidID( $_sID );
            $_aTemplates[ $_sID ][ 'should_remove' ] = ! $_aTemplates[ $_sID ][ 'is_valid' ];
        }
        $_aTemplates = apply_filters( 'aal_filter_available_templates', $_aTemplates );
        $_aCache     = $_aTemplates;
        return $_aTemplates;
    }

}