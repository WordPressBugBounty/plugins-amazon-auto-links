<?php
/*
 * Admin Page Framework v3.9.2b01 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/amazon-auto-links-compiler>
 * <https://en.michaeluno.jp/amazon-auto-links>
 * Copyright (c) 2013-2023, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class AmazonAutoLinks_AdminPageFramework_PostType_Router extends AmazonAutoLinks_AdminPageFramework_Factory {
    public function __construct($oProp)
    {
        parent::__construct($oProp);
        $this->oUtil->registerAction('init', array( $this, '_replyToDetermineToLoad' ));
        $this->oUtil->registerAction('current_screen', array( $this, '_replyToDetermineToLoadAdmin' ));
    }
    public function _replyToDetermineToLoadAdmin()
    {
        if (! $this->_isInThePage()) {
            return;
        }
        $this->_load(array( "load_{$this->oProp->sPostType}", "load_{$this->oProp->sClassName}", ));
    }
    public function _replyToDetermineToLoad()
    {
        $this->_setUp();
    }
    protected function _getLinkObject()
    {
        $_sClassName = $this->aSubClassNames[ 'oLink' ];
        return new $_sClassName($this->oProp, $this->oMsg);
    }
    protected function _getPageLoadObject()
    {
        $_sClassName = $this->aSubClassNames[ 'oPageLoadInfo' ];
        return new $_sClassName($this->oProp, $this->oMsg);
    }
    protected function _isInThePage()
    {
        if (! $this->oProp->bIsAdmin) {
            return false;
        }
        if ($this->_isValidAjaxReferrer()) {
            return true;
        }
        if (! in_array($this->oProp->sPageNow, array( 'edit.php', 'edit-tags.php', 'term.php', 'post.php', 'post-new.php' ))) {
            return false;
        }
        if (isset($_GET[ 'page' ])) {
            return false;
        }
        return $this->oUtil->getCurrentPostType() === $this->oProp->sPostType;
    }
    protected function _isValidAjaxReferrer()
    {
        if (! $this->oProp->bIsAdminAjax) {
            return false;
        }
        if (! $this->oUtil->getElement($this->oProp->aPostTypeArgs, 'public', true)) {
            return false;
        }
        $_aReferrer = parse_url($this->oProp->sAjaxReferrer) + array( 'query' => '', 'path' => '' );
        parse_str($_aReferrer[ 'query' ], $_aQuery);
        $_sBaseName = basename($_aReferrer[ 'path' ]);
        if (! in_array($_sBaseName, array( 'edit.php', ))) {
            return false;
        }
        return $this->oUtil->getElement($_aQuery, array( 'post_type' ), '') === $this->oProp->sPostType;
    }
    public function _replyToLoadComponents()
    {
        if ('plugins.php' === $this->oProp->sPageNow) {
            $this->oLink = $this->_replyTpSetAndGetInstance_oLink();
        }
        parent::_replyToLoadComponents();
    }
}
