/**
 * Description
 */
tinymce.PluginManager.add("hametuha",function(e,t){"use strict";function n(e,t){var n;(n=e.getElementById("hametuha-mce"))||(n=e.createElement("style"),n.type="text/css",n.id="hametuha-mce",e.getElementsByTagName("head")[0].appendChild(n)),n.innerHTML=t}function a(e){return!/^[ 　【】《〔〝『「（”"'’\(\)]/.test(e.textContent)}function c(t){var c=e.dom,d=[],h=c.doc.body.childNodes;jQuery.each(h,function(e,t){switch(t.nodeName){case"P":a(t)||d.push("body > p:nth-child("+(e+1)+")");break;case"BLOCKQUOTE":for(var n=t.childNodes,c=0,h=n.length;c<h;c++)"P"!==n[c].nodeName||a(n[c])||d.push("body > blockquote:nth-child("+(e+1)+") > p:nth-child("+(c+1)+")")}}),d.length&&n(c.doc,d.join(",")+"{text-indent: 0;}")}e.on("init",c),e.on("change",c)});
//# sourceMappingURL=../map/admin/mce.js.map
