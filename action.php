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
	function register(&$controller) {
		$controller->register_hook('DOKUWIKI_STARTED', 'AFTER',  $this, '_addlastmod');
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
}
