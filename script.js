/* DOKUWIKI:include_once vendor/pnotify/jquery.pnotify.js */
/**
 * DokuWiki Plugin ajaxedit (JavaScript Component) 
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author lisps
 */


var LASTMOD = JSINFO?JSINFO['lastmod']:null;
jQuery.pnotify.defaults.styling = "jqueryui";
jQuery.pnotify.defaults.delay = 2000;
jQuery.pnotify.defaults.history = false;


var ajaxedit_queue_ = [];
var ajaxedit_queue_working = false;
function ajaxedit_queue(callback) {
    ajaxedit_queue_.push(callback);
    if(!ajaxedit_queue_working) {
        ajaxedit_queue_working = true;
        ajaexedit_queue_next();
    }
}
function ajaexedit_queue_next(){
    var c = ajaxedit_queue_.pop();
    if(c) c();
    else  ajaxedit_queue_working = false;
}

function ajaxedit_send_(url,data,fcnSuccess) {
    data['lastmod']=LASTMOD;
    jQuery.post(
		url,
		data,
		function(data) {
            fcnSuccess(data);
            ajaexedit_queue_next();  
        }
	);
}

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
	data['pageid']=data['pageid']?data['pageid']:JSINFO['id'];
	data['sectok']=JSINFO['sectok'];
	data['id']=idx_tag;
	data['index']=idx_tag;

	var url = DOKU_BASE+'lib/plugins/'+plugin+'/ajax.php';
    ajaxedit_queue(function(){ajaxedit_send_(url,data,fcnSuccess)});

}

/**
 * ajaxedit_send is a wrapper for jQuery's post function 
 * it automatically adds the current pageid, lastmod and the security token
 * 
 * @param string   plugin plugin name
 * @param int      idx_tag the id counter
 * @param function fcnSuccess callback function
 * @param hash     data additional data
 */
function ajaxedit_send2(plugin,idx_tag,fcnSuccess,data){
	data['pageid']=data['pageid']?data['pageid']:JSINFO['id'];
	data['sectok']=JSINFO['sectok'];
	data['id']=idx_tag;
	data['index']=idx_tag;
	data['call']='plugin_'+plugin;
	var url = DOKU_BASE+'lib/exe/ajax.php';
    ajaxedit_queue(function(){ajaxedit_send_(url,data,fcnSuccess)});

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
	} else if(response.msg){
        
        jQuery.pnotify({
            title: false,
            text: response.msg?response.msg:'gespeichert',
            type: 'success',
            icon: false,
            delay: response.notifyDelay?response.notifyDelay*1000:2000,
            animate_speed: 100,
            animation: {
                effect_in:'bounce',
                effect_out:'drop',
            }
        });
    }
	if(response.pageid === JSINFO['id']) LASTMOD = response.lastmod; //refresh LASTMOD
	return true;
}


function ajaxedit_getIdxByIdClass(id,classname) {
	var tag_type = jQuery("#"+id).prop('tagName');
    id = jQuery("#"+id).attr('id');
	var $els = jQuery(tag_type+"."+classname);
    
	for(var ii=0,kk=0;ii<$els.length;ii++){
		if($els[ii].id == id) return kk;
		kk++; 
	}
}

function ajaxedit_getIdxByIdClassNodeid(id,classname,nodeid) {
	var tag_type = jQuery("#"+id).prop('tagName');
	id = jQuery("#"+id).attr('id');
    var $els = jQuery('#'+nodeid +" > "+tag_type+"."+classname);

	for(var ii=0;ii<$els.length;ii++){
		if($els[ii].id == id) {
			return ii;
		}	 
	}
}

jQuery(window).on('beforeunload',function(e){
    if(ajaxedit_queue_working) {
        return LANG.plugins.ajaxedit.tasks_left.replace('{0}',(ajaxedit_queue_.length *1 +1)) ;
    }
});
