tinymce.PluginManager.add("hametuha",function(e,n){function t(e,n){var t;(t=e.getElementById("hametuha-mce"))||(t=e.createElement("style"),t.type="text/css",t.id="hametuha-mce",e.getElementsByTagName("head")[0].appendChild(t)),console.log(t),t.innerHTML=n}function o(e){return!/^[ 　【】《〔〝『「（”"'’\(\)]/.test(e.textContent)}function a(n){for(var a=e.dom,c=[],d=a.doc.body.childNodes,h=0,i=d.length;i>h;h++)switch(d[h].nodeName){case"P":o(d[h])||c.push("body > p:nth-child("+(h+1)+")");break;case"BLOCKQUOTE":!function(e,n){for(var t=e.childNodes,a=0,d=t.length;d>a;a++)"P"!==t[a].nodeName||o(t[a])||c.push("body > blockquote:nth-child("+(n+1)+") > p:nth-child("+(a+1)+")")}(d[h],h)}c.length&&t(a.doc,c.join(",")+"{text-indent: 0;}")}e.on("init",a),e.on("change",a)});
//# sourceMappingURL=../map/admin/mce.js.map