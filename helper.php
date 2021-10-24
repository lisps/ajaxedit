<?php
/**
 * DokuWiki Plugin ajaxedit (Helper Component)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author lisps
 */

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
		if($exit && !defined('DOKU_UNITTEST')) exit;
	}
	
	function _error($type,$exit = true){
		$ret = array(
			'error' 	=> $type,
			'msg'		=> $this->_getErrorMsg($type),
			'lastmod'	=> intval($_POST["lastmod"]),
		);
		
		echo json_encode($ret);

		if($exit && !defined('DOKU_UNITTEST')) exit;
	}
	
	/**
	 * getWikiPage returns the raw wiki data
	 * @return string
	 */
	function getWikiPage($checkLastmod = true, $min_acl = AUTH_EDIT){
		global $ID;
		global $INFO;
		$this->ID=cleanID(trim($_POST["pageid"]));

		$ID = $this->ID;
		$this->index = intval($_POST["index"]);
		
		$oldrev = intval($_POST["lastmod"]);
		
		if(!checkSecurityToken()) $this->_error(self::ERROR_SECTOC);
		
		if (auth_quickaclcheck($ID) < $min_acl) {
			$this->_error(self::ERROR_ACL);
		}
		
		$INFO = pageinfo();
		if($checkLastmod && $INFO['lastmod']!=$oldrev ) {
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
		global $ID;
		$info = pageinfo();
		
		$ret = array(
			'error'  => 0,
			'msg'    => '',
			'lastmod'=> $info['lastmod'],
			'index'  => $this->index,
			'pageid' => $ID,
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
		global $INFO;
		$INFO = pageinfo();
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
				$msg = p_locale_xhtml('denied');
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