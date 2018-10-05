<?php
/**
 * DokuWiki Plugin ajaxedit (Action Component) 
 * 
 * adds lastmod and sectok variable to JSINFO
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  lisps
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

class action_plugin_ajaxedit extends DokuWiki_Action_Plugin {

	/**
	 * Register the eventhandlers
	 */
	function register(Doku_Event_Handler $controller) {
		$controller->register_hook('DOKUWIKI_STARTED', 'AFTER',  $this, '_addlastmod');
		$controller->register_hook('DOKUWIKI_STARTED', 'AFTER',  $this, 'fixsecedit');
	}
	function _addlastmod(&$event, $param) {
		global $INFO;
		global $JSINFO;
		
		$info = pageinfo();
		$JSINFO['lastmod'] = $info["lastmod"];
		$JSINFO['sectok'] = getSecurityToken();
        $perm = auth_quickaclcheck($ID);
		if ($perm > AUTH_READ)
			$JSINFO['acl_write'] = '1';
	}
	
	/**
	 * try to fix sectioninfo for section edit buttons.
	 * 
	 * @param Doku_Event $event
	 * @param unknown $param
	 */
	function fixsecedit(Doku_Event $event, $param) {
		global $INPUT;
		global $RANGE;
		global $REV;
		global $INFO;
		global $ID;
		global $ACT;
		
		if($ACT !== 'edit') return;
		if(!$this->getConf('fix_section_edit')) return;
		if($INPUT->str('target') !== 'section') return;
		
		$range = $INPUT->str('range');
		$rev = $INPUT->str('rev');
		
		if($rev && $range && (!$REV && !$RANGE)) { //$_POST has range and rev but pageinfo() cleared it -> action
			list($r_start,$r_end) = explode('-',$range);
			$instructions = p_cached_instructions($INFO['filepath']); //get instructions
			$new_section_open = null;
			$new_section_close = '';
			$found = null;

			foreach($instructions as $key => $instruction) {
				
				if($new_section_open && $instruction[0] === 'section_close') { //moved section found, now find closing section
					$new_section_close = $instruction[2];
					break; //end
				} elseif ($new_section_open) {
					continue; 
				}
				
				if($instruction[0] === 'section_open') {
					if($r_start == $instruction[2]) {
						$new_section_open = $instruction[2];
						msg(sprintf($this->getLang('section_found'),wl($ID,array('do'=>'edit'))));
					} else if( abs($r_start-$instruction[2]) < $this->getConf('section_edit_range') ) {
						$new_section_open = $instruction[2];
						msg(sprintf($this->getLang('moved_section_found'),wl($ID,array('do'=>'edit'))));
					} 

				}
			}
			
			if($new_section_open !== null) {
				$RANGE = implode('-', array($new_section_open,$new_section_close));
			}
		}
		
		
		
		
	}
}
