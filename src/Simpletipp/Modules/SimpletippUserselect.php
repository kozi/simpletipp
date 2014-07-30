<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2011-2014 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    simpletipp
 * @license    LGPL
 * @filesource
 */

namespace Simpletipp\Modules;


use \Simpletipp\SimpletippModule;
use \Simpletipp\Simpletipp;

/**
 * Class SimpletippUserselect
 *
 * @copyright  Martin Kozianka 2011-2014
 * @author     Martin Kozianka <martin@kozianka.de>
 * @package    Controller
 */

class SimpletippUserselect extends SimpletippModule {
	protected $strTemplate = 'simpletipp_userselect';
    private $participants  = null;
    private $selectedUser  = null;

	public function generate() {

		if (TL_MODE == 'BE') {
			$this->Template = new \BackendTemplate('be_wildcard');
			$this->Template->wildcard  = '### SimpletippUserselect ###';
			$this->Template->wildcard .= '<br/>'.$this->headline;
			return $this->Template->parse();
		}
		return parent::generate();
	}
	
	protected function compile() {
        global $objPage;
        $participants = array();

        $objMembers = Simpletipp::getGroupMember($this->simpletipp->participant_group);
        if ($objMembers != null) {
            foreach (Simpletipp::getGroupMember($this->simpletipp->participant_group) as $objMember) {
                $objMember->link              = $this->addToUrl('user='.$objMember->username);
                $participants[$objMember->id] = $objMember;
            }
        }

        $this->Template->action             = $this->addToUrl('user=');
        $this->Template->isMobile           = $objPage->isMobile;
        $this->Template->participants       = $participants;
        $this->Template->simpletippUserId   = $this->simpletippUserId;
        $this->Template->simpletipp         = $this->simpletipp;
        $this->Template->now                = $this->now;
        $this->Template->resetOption        = (Object) array(
                'value' => $this->User->username,
                'label' => 'Eigene Daten anzeigen ('.$this->User->username.')'
        );

    }

} // END class SimpletippUserselect


