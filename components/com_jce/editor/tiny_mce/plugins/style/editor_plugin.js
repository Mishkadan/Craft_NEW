/* jce - 2.9.17 | 2021-10-28 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2021 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function(){tinymce.create("tinymce.plugins.StylePlugin",{init:function(ed,url){ed.addCommand("mceStyleProps",function(){var applyStyleToBlocks=!1,blocks=ed.selection.getSelectedBlocks(),styles=[];1===blocks.length?styles.push(ed.selection.getNode().style.cssText):(tinymce.each(blocks,function(block){styles.push(ed.dom.getAttrib(block,"style"))}),applyStyleToBlocks=!0),ed.windowManager.open({file:ed.getParam("site_url")+"index.php?option=com_jce&task=plugin.display&plugin=style",size:"mce-modal-landscape-xxlarge"},{applyStyleToBlocks:applyStyleToBlocks,plugin_url:url,styles:styles})}),ed.addCommand("mceSetElementStyle",function(ui,v){var node=ed.selection.getNode();node&&(ed.dom.setAttrib(node,"style",v),ed.execCommand("mceRepaint"))}),ed.onNodeChange.add(function(ed,cm,n){cm.setDisabled("style","BODY"===n.nodeName||"BR"===n.nodeName&&n.getAttribute("data-mce-bogus"))}),ed.addButton("style",{title:"style.desc",cmd:"mceStyleProps"})}}),tinymce.PluginManager.add("style",tinymce.plugins.StylePlugin)}();