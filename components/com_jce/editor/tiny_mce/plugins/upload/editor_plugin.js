/* jce - 2.9.17 | 2021-10-28 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2021 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function(){var each=tinymce.each,JSON=tinymce.util.JSON,RangeUtils=tinymce.dom.RangeUtils,Uuid=tinymce.util.Uuid;tinymce.PluginManager.add("upload",function(ed,url){function cancel(){ed.dom.bind(ed.getBody(),"dragover",function(e){var dataTransfer=e.dataTransfer;dataTransfer&&dataTransfer.files&&dataTransfer.files.length&&e.preventDefault()}),ed.dom.bind(ed.getBody(),"drop",function(e){var dataTransfer=e.dataTransfer;dataTransfer&&dataTransfer.files&&dataTransfer.files.length&&e.preventDefault()})}function uploadHandler(file,success,failure,progress){var xhr,formData;success=success||noop,failure=failure||noop,progress=progress||noop;var args={method:"upload",id:Uuid.uuid("wf_"),inline:1,name:file.filename},url=file.upload_url;url+="&"+ed.settings.query,xhr=new XMLHttpRequest,xhr.open("POST",url),xhr.upload.onprogress=function(e){progress(e.loaded/e.total*100)},xhr.onerror=function(){failure("Image upload failed due to a XHR Transport error. Code: "+xhr.status)},xhr.onload=function(){var json;return xhr.status<200||xhr.status>=300?void failure("HTTP Error: "+xhr.status):(json=JSON.parse(xhr.responseText),json||failure("Invalid JSON response!"),json.error||!json.result?void failure(json.error.message||"Invalid JSON response!"):void success(json.result))},formData=new FormData,each(args,function(value,name){formData.append(name,value)}),formData.append("file",file,file.name),xhr.send(formData)}function addFile(file){if(/\.(php([0-9]*)|phtml|pl|py|jsp|asp|htm|html|shtml|sh|cgi)\./i.test(file.name))return ed.windowManager.alert(ed.getLang("upload.file_extension_error","File type not supported")),!1;if(each(plugins,function(plg){if(!file.upload_url){var url=plg.getUploadURL(file);if(url)return file.upload_url=url,file.uploader=plg,!1}}),file.upload_url){if(tinymce.is(file.uploader.getUploadConfig,"function")){var config=file.uploader.getUploadConfig(),name=file.target_name||file.name;if(file.filename=name.replace(/[\+\\\/\?\#%&<>"\'=\[\]\{\},;@\^\(\)£€$~]/g,""),!new RegExp(".("+config.filetypes.join("|")+")$","i").test(file.name))return ed.windowManager.alert(ed.getLang("upload.file_extension_error","File type not supported")),!1;if(file.size){var max=parseInt(config.max_size,10)||1024;if(file.size>1024*max)return ed.windowManager.alert(ed.getLang("upload.file_size_error","File size exceeds maximum allowed size")),!1}}if(!file.marker&&ed.settings.upload_use_placeholder!==!1){ed.execCommand("mceInsertContent",!1,'<span data-mce-marker="1" id="__mce_tmp">\ufeff</span>',{skip_undo:1});var w,h,n=ed.dom.get("__mce_tmp");/image\/(gif|png|jpeg|jpg)/.test(file.type)&&file.size?(w=h=Math.round(Math.sqrt(file.size)),w=Math.max(300,w),h=Math.max(300,h),ed.dom.setStyles(n,{width:w,height:h}),ed.dom.addClass(n,"mce-item-upload")):ed.setProgressState(!0),file.marker=n}return ed.undoManager.add(),files.push(file),!0}return ed.windowManager.alert(ed.getLang("upload.file_extension_error","File type not supported")),!1}function createUploadMarker(node){var styles,src=node.attr("src")||"",style={},cls=[];if(!node.attr("alt")&&!/data:image/.test(src)){var alt=src.substring(src.length,src.lastIndexOf("/")+1);node.attr("alt",alt)}node.attr("style")&&(style=ed.dom.styles.parse(node.attr("style"))),node.attr("hspace")&&(style["margin-left"]=style["margin-right"]=node.attr("hspace")),node.attr("vspace")&&(style["margin-top"]=style["margin-bottom"]=node.attr("vspace")),node.attr("align")&&(style.float=node.attr("align")),node.attr("class")&&(cls=node.attr("class").replace(/\s*upload-placeholder\s*/,"").split(" ")),cls.push("mce-item-upload"),cls.push("mce-item-upload-marker"),"media"===node.name&&(node.name="img",node.shortEnded=!0),node.attr({src:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",class:tinymce.trim(cls.join(" "))});var tmp=ed.dom.create("span",{style:style}),styles=ed.dom.getAttrib(tmp,"style");styles&&node.attr({style:styles,"data-mce-style":styles})}function selectAndInsert(file,data){var marker=file.marker,uploader=file.uploader;ed.selection.select(marker);var elm=uploader.insertUploadedFile(data);if(elm){if("object"==typeof elm&&elm.nodeType){if(ed.dom.hasClass(marker,"mce-item-upload-marker")){var styles=ed.dom.getAttrib(marker,"data-mce-style"),w=marker.width||0,h=marker.height||0;styles&&(styles=ed.dom.styles.parse(styles),styles.width&&(w=styles.width,delete styles.width),styles.height&&(h=styles.height,delete styles.height),ed.dom.setStyles(elm,styles)),w&&ed.dom.setAttrib(elm,"width",w),h&&(w&&(h=""),ed.dom.setAttrib(elm,"height",h))}ed.undoManager.add(),ed.dom.replace(elm,marker)}return ed.nodeChanged(),!0}}function bindUploadMarkerEvents(marker){function removeUpload(){dom.setStyles("wf_upload_button",{top:"",left:"",display:"none",zIndex:""})}var dom=tinymce.DOM;ed.onNodeChange.add(removeUpload),ed.dom.bind(ed.getWin(),"scroll",removeUpload);var input=dom.get("wf_upload_input"),btn=dom.get("wf_upload_button");btn||(btn=dom.add(dom.doc.body,"div",{id:"wf_upload_button",class:"btn",role:"button",title:ed.getLang("upload.button_description","Click to upload a file")},'<label for="wf_upload_input"><span class="icon-upload"></span>&nbsp;'+ed.getLang("upload.label","Upload")+"</label>"),input=dom.add(btn,"input",{type:"file",id:"wf_upload_input"})),ed.dom.bind(marker,"mouseover",function(e){if(!ed.dom.getAttrib(marker,"data-mce-selected")){var vp=ed.dom.getViewPort(ed.getWin()),p1=dom.getRect(ed.getContentAreaContainer()),p2=ed.dom.getRect(marker);if(!(vp.y>p2.y+p2.h/2-25||vp.y<p2.y+p2.h/2+25-p1.h)){var x=Math.max(p2.x-vp.x,0)+p1.x,y=Math.max(p2.y-vp.y,0)+p1.y-Math.max(vp.y-p2.y,0),zIndex="mce_fullscreen"==ed.id?dom.get("mce_fullscreen_container").style.zIndex:0;dom.setStyles("wf_upload_button",{top:y+p2.h/2-16,left:x+p2.w/2-50,display:"block",zIndex:zIndex+1}),dom.setStyles("wf_select_button",{top:y+p2.h/2-16,left:x+p2.w/2-50,display:"block",zIndex:zIndex+1}),input.onchange=function(){if(input.files){var file=input.files[0];file&&(file.marker=marker,addFile(file)&&(each(["width","height"],function(key){ed.dom.setStyle(marker,key,ed.dom.getAttrib(marker,key))}),file.marker=ed.dom.rename(marker,"span"),uploadFile(file),removeUpload()))}}}}}),ed.dom.bind(marker,"mouseout",function(e){!e.relatedTarget&&e.clientY>0||removeUpload()})}function uploadFile(file){uploadHandler(file,function(response){var files=response.files||[],item=files.length?files[0]:{};if(file.uploader){var obj=tinymce.extend({type:file.type,name:file.name},item);selectAndInsert(file,obj)}files.splice(tinymce.inArray(files,file),1),file.marker&&ed.dom.remove(file.marker)},function(message){ed.windowManager.alert(message),file.marker&&ed.dom.remove(file.marker)},function(value){file.marker&&ed.dom.setAttrib(file.marker,"data-progress",value)})}var plugins=[],files=[];ed.onPreInit.add(function(){function isMediaPlaceholder(node){if("media"===node.name)return!0;if("img"===node.name){if(node.attr("data-mce-upload-marker"))return!0;var cls=node.attr("class");if(cls&&cls.indexOf("upload-placeholder")!=-1)return!0}return!1}function bindUploadEvents(ed){each(ed.dom.select(".mce-item-upload-marker",ed.getBody()),function(n){0==plugins.length?ed.dom.remove(n):bindUploadMarkerEvents(n)})}each(ed.plugins,function(plg,name){if(tinymce.is(plg.getUploadConfig,"function")){var data=plg.getUploadConfig();data.inline&&data.filetypes&&plugins.push(plg)}}),ed.onBeforeSetContent.add(function(ed,o){o.content=o.content.replace(/<\/media>/g,"&nbsp;</media>")}),ed.onPostProcess.add(function(ed,o){o.content=o.content.replace(/(&nbsp;|\u00a0)<\/media>/g,"</media>")}),ed.schema.addCustomElements("~media[type|width|height|class|style|title|*]"),ed.settings.compress.css||ed.dom.loadCSS(url+"/css/content.css"),ed.serializer.addAttributeFilter("data-mce-marker",function(nodes,name,args){for(var i=nodes.length;i--;)nodes[i].remove()}),ed.parser.addNodeFilter("img,media",function(nodes){for(var node,i=nodes.length;i--;)node=nodes[i],isMediaPlaceholder(node)&&(0==plugins.length?node.remove():createUploadMarker(node))}),ed.serializer.addNodeFilter("img",function(nodes){for(var node,cls,i=nodes.length;i--;)node=nodes[i],cls=node.attr("class"),cls&&/mce-item-upload-marker/.test(cls)&&(cls=cls.replace(/(?:^|\s)(mce-item-)(?!)(upload|upload-marker|upload-placeholder)(?!\S)/g,""),node.attr({"data-mce-src":"",src:"",class:tinymce.trim(cls)}),node.name="media",node.shortEnded=!1,node.attr("alt",null))}),ed.selection.onSetContent.add(function(){bindUploadEvents(ed)}),ed.onSetContent.add(function(){bindUploadEvents(ed)}),ed.onFullScreen&&ed.onFullScreen.add(function(editor){bindUploadEvents(editor)})}),ed.onInit.add(function(){function cancelEvent(e){e.preventDefault(),e.stopPropagation()}return 0==plugins.length?void cancel():(ed.theme&&ed.theme.onResolveName&&ed.theme.onResolveName.add(function(theme,o){var n=o.node;n&&"IMG"===n.nodeName&&/mce-item-upload/.test(n.className)&&(o.name="placeholder")}),ed.dom.bind(ed.getBody(),"dragover",function(e){e.dataTransfer.dropEffect=tinymce.VK.metaKeyPressed(e)?"copy":"move"}),void ed.dom.bind(ed.getBody(),"drop",function(e){var dataTransfer=e.dataTransfer;dataTransfer&&dataTransfer.files&&dataTransfer.files.length&&(each(dataTransfer.files,function(file){var rng=RangeUtils.getCaretRangeFromPoint(e.clientX,e.clientY,ed.getDoc());rng&&(ed.selection.setRng(rng),rng=null),addFile(file)}),cancelEvent(e)),files.length&&each(files,function(file){uploadFile(file)}),tinymce.isGecko&&"IMG"==e.target.nodeName&&cancelEvent(e)}))});var noop=function(){};return{plugins:plugins,upload:uploadHandler}})}();