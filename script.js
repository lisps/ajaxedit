/**
 * DokuWiki Plugin ajaxedit (JavaScript Component) 
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author lisps
 */

var LASTMOD = JSINFO?JSINFO['lastmod']:null;

/**
 * ajaxedit_send is a wrapper for jQuery's post function 
 * it automatically adds the current pageid, lastmod and the security token
 * 
 * @param string   plugin plugin name
 * @param int      idx_tag the id counter
 * @param function fcnSuccess callback function
 * @param hash     data additional data
 */
function ajaxedit_send(plugin,idx_tag,fcnSuccess,data){
	data['pageid']=JSINFO['id'];
	data['lastmod']=LASTMOD;
	data['sectok']=JSINFO['sectok'];
	data['id']=idx_tag;
	data['index']=idx_tag;
	jQuery.post(
		DOKU_BASE+'lib/plugins/'+plugin+'/ajax.php',
		data,
		fcnSuccess
	);
}

/**
 * ajaxedit_parse simply parses the json response using JSON.parse()
 * 
 * @param json   data 
 * @return mixed false if there is no data/ the response
 */
function ajaxedit_parse(data){
	if(!data) return false;
	return JSON.parse(data);
}

/**
 * ajaxedit_checkResponse checks if the server error flag is set and displays a jQuery Dialog, with the Message
 * 
 * @param  hash    response the parsed @see ajaxedit_parse server response 
 * @return boolean false on error
 */
function ajaxedit_checkResponse(response){
	if(response.error != 0) {
		if(jQuery('#ajaxedit__dialog')){
			jQuery('body').append('<div id="ajaxedit__dialog" position="absolute" border=1 ><div id="ajaxedit__dialog_div"></div></div>');
			jQuery( "#ajaxedit__dialog" ).dialog({title:'Error',height:300,width:400,autoOpen:true,modal:true});
		}
		jQuery('#ajaxedit__dialog_div').html(response.msg);
		return false;
	}
	
	LASTMOD = ret.lastmod;
	return true;
}


function ajaxedit_getIdxByIdClass(id,classname) {
	tag_type = jQuery("#"+id).prop('tagName');
	$els = jQuery(tag_type+"."+classname);

	for(ii=0,kk=0;ii<$els.size();ii++){
		if($els[ii].id == id) return kk;
		kk++; 
	}
}

function ajaxedit_getIdxByIdClassNodeid(id,classname,nodeid) {
	tag_type = jQuery("#"+id).prop('tagName');
	$els = jQuery('#'+nodeid +" > "+tag_type+"."+classname);

	for(ii=0;ii<$els.size();ii++){
		if($els[ii].id == id) {
			return ii;
		}	 
	}
}
