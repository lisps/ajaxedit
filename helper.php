<?php
/**
 * DokuWiki Plugin ajaxedit (Helper Component)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author lisps
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
 
require_once(DOKU_INC.'inc/auth.php');

class helper_plugin_ajaxedit extends DokuWiki_Plugin {
	const ERROR_LOCK	=  1;
	const ERROR_SECTOC  =  2;
	const ERROR_MODIFIED=  4;
	const ERROR_ACL  	=  3;
	const ERROR_READ	=  5;
	
	
	const ERROR_OTHER	= 99;
	
	public $ID; 
	public $index;
	
	function __construct(){
	    global $ID;
	    $ID=isset($_POST["pageid"])?cleanID($_POST["pageid"]):$ID;
	}
	
	/**
	 * error throw an error and leave
	 * @param string $msg 
	 * @param integer $type error type 
	 * @param boolean $exit leave or not
	 **/
	function error($msg,$type=self::ERROR_OTHER,$exit = true){
		$ret = array(
			'error' 	=> $type,
			'msg'		=> $msg,
			'lastmod'	=> intval($_POST["lastmod"]),
		);
		
		echo json_encode($ret);
		if($exit && !DOKU_UNITTEST) exit;
	}
	
	function _error($type,$exit = true){
		$ret = array(
			'error' 	=> $type,
			'msg'		=> $this->_getErrorMsg($type),
			'lastmod'	=> intval($_POST["lastmod"]),
		);
		
		echo json_encode($ret);
		if($exit && !DOKU_UNITTEST) exit;
	}
	
	/**
	 * getWikiPage returns the raw wiki data
	 * @return string
	 */
	function getWikiPage(){
		global $ID;
		global $INFO;
		$this->ID=cleanID(trim($_POST["pageid"]));

		$ID = $this->ID;
		$this->index = intval($_POST["index"]);
		
		$oldrev = intval($_POST["lastmod"]);
		
		if(!checkSecurityToken()) $this->_error(self::ERROR_SECTOC);
		
		if (auth_quickaclcheck($ID) < AUTH_EDIT) {
			$this->_error(self::ERROR_ACL);
		}
		
		$INFO = pageinfo();
		if($INFO['lastmod']!=$oldrev ) {
			$this->_error(self::ERROR_MODIFIED);
		}
		
		if(checklock($ID)){
			$this->_error(self::ERROR_LOCK);
		}
		
		if (!($data=rawWiki($ID))){
			$this->_error(self::ERROR_READ);
		}
		return $data;
	
	}
	/**
	 * success sends the success message
	 * automatically sends error,msg,lastmod,index(id counter) 
	 *
	 * @param array $data additional data
	 */
	function success($data=array()){
		$info = pageinfo();
		
		$ret = array(
			'error'  => 0,
			'msg'    => '',
			'lastmod'=> $info['lastmod'],
			'index'  => $this->index,
		);
		$ret = array_merge($ret,$data);
		echo json_encode($ret);
		exit;
	}
	
	/**
	 * saveWikiPage saves the wiki page
	 * 
	 * @param string $data wiki page
	 * @param string $summary
	 * @param boolean $minor 
	 * @param array $param will go to @see success
	 * @param boolean $autosubmit if set will call success
	 */
	function saveWikiPage($data,$summary,$minor = false,$param=array(),$autosubmit=true){
		saveWikiText($this->ID,$data,$summary,$minor);
		if($autosubmit){
			$this->success($param);
		}
	}
	
	function _getErrorMsg($error){
		global $INFO;
		$INFO = pageinfo();
		$msg = '';
		switch($error){
			case self::ERROR_LOCK: 
				$msg = 'ERROR_LOCK:tbd';
				ob_start();
			    html_locked();
			    $msg = ob_get_clean();
				break;	
			case self::ERROR_SECTOC : 
				$msg = 'ERROR_SECTOC:tbd';
				break; 
			case self::ERROR_ACL: 
				$msg = 'ERROR_ACL:tbd';
				break;	

			case self::ERROR_READ: 
				$msg = 'ERROR_READ:tbd';
				break;	
		
			case self::ERROR_MODIFIED: 
				$msg = sprintf($this->getLang('e_modified'),hsc(editorinfo($INFO['user'])));
				break;
				
			case self::ERROR_OTHER: 
				$msg = 'tbd';
				break;
			default: 
				$msg = 'Undefined Failure';
				break;
		}
		
		return $msg;
	}
}