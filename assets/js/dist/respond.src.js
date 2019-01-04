!function(e){"use strict";var t,n,a,s,i,r;e.matchMedia=e.matchMedia||(t=e.document,a=t.documentElement,s=a.firstElementChild||a.firstChild,i=t.createElement("body"),(r=t.createElement("div")).id="mq-test-1",r.style.cssText="position:absolute;top:-100em",i.style.background="none",i.appendChild(r),function(e){return r.innerHTML='&shy;<style media="'+e+'"> #mq-test-1 { width: 42px; }</style>',a.insertBefore(i,s),n=42===r.offsetWidth,a.removeChild(i),{matches:n,media:e}})}(this),function(y){"use strict";var c={};(y.respond=c).update=function(){};var i=[],a=function(){var t=!1;try{t=new y.XMLHttpRequest}catch(e){t=new y.ActiveXObject("Microsoft.XMLHTTP")}return function(){return t}}(),e=function(e,t){var n=a();n&&(n.open("GET",e,!0),n.onreadystatechange=function(){4!==n.readyState||200!==n.status&&304!==n.status||t(n.responseText)},4!==n.readyState&&n.send(null))},f=function(e){return e.replace(c.regex.minmaxwh,"").match(c.regex.other)};if(c.ajax=e,c.queue=i,c.unsupportedmq=f,c.regex={media:/@media[^\{]+\{([^\{\}]*\{[^\}\{]*\})+/gi,keyframes:/@(?:\-(?:o|moz|webkit)\-)?keyframes[^\{]+\{(?:[^\{\}]*\{[^\}\{]*\})+[^\}]*\}/gi,comments:/\/\*[^*]*\*+([^/][^*]*\*+)*\//gi,urls:/(url\()['"]?([^\/\)'"][^:\)'"]+)['"]?(\))/g,findStyles:/@media *([^\{]+)\{([\S\s]+?)$/,only:/(only\s+)?([a-zA-Z]+)\s?/,minw:/\(\s*min\-width\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/,maxw:/\(\s*max\-width\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/,minmaxwh:/\(\s*m(in|ax)\-(height|width)\s*:\s*(\s*[0-9\.]+)(px|em)\s*\)/gi,other:/\([^\)]*\)/g},c.mediaQueriesSupported=y.matchMedia&&null!==y.matchMedia("only all")&&y.matchMedia("only all").matches,!c.mediaQueriesSupported){var x,v,E,w=y.document,S=w.documentElement,T=[],C=[],b=[],r={},$=w.getElementsByTagName("head")[0]||S,o=w.getElementsByTagName("base")[0],z=$.getElementsByTagName("link"),M=function(){var e,t=w.createElement("div"),n=w.body,a=S.style.fontSize,s=n&&n.style.fontSize,i=!1;return t.style.cssText="position:absolute;font-size:1em;width:1em",n||((n=i=w.createElement("body")).style.background="none"),S.style.fontSize="100%",n.style.fontSize="100%",n.appendChild(t),i&&S.insertBefore(n,S.firstChild),e=t.offsetWidth,i?S.removeChild(n):n.removeChild(t),S.style.fontSize=a,s&&(n.style.fontSize=s),e=E=parseFloat(e)},R=function(e){var t="clientWidth",n=S[t],a="CSS1Compat"===w.compatMode&&n||w.body[t]||n,s={},i=z[z.length-1],r=(new Date).getTime();if(e&&x&&r-x<30)return y.clearTimeout(v),void(v=y.setTimeout(R,30));for(var o in x=r,T)if(T.hasOwnProperty(o)){var l=T[o],m=l.minw,d=l.maxw,h=null===m,u=null===d;m&&(m=parseFloat(m)*(-1<m.indexOf("em")?E||M():1)),d&&(d=parseFloat(d)*(-1<d.indexOf("em")?E||M():1)),l.hasquery&&(h&&u||!(h||m<=a)||!(u||a<=d))||(s[l.media]||(s[l.media]=[]),s[l.media].push(C[l.rules]))}for(var c in b)b.hasOwnProperty(c)&&b[c]&&b[c].parentNode===$&&$.removeChild(b[c]);for(var f in b.length=0,s)if(s.hasOwnProperty(f)){var p=w.createElement("style"),g=s[f].join("\n");p.type="text/css",p.media=f,$.insertBefore(p,i.nextSibling),p.styleSheet?p.styleSheet.cssText=g:p.appendChild(w.createTextNode(g)),b.push(p)}},l=function(e,t,n){var a=e.replace(c.regex.comments,"").replace(c.regex.keyframes,"").match(c.regex.media),s=a&&a.length||0,i=function(e){return e.replace(c.regex.urls,"$1"+t+"$2$3")},r=!s&&n;(t=t.substring(0,t.lastIndexOf("/"))).length&&(t+="/"),r&&(s=1);for(var o=0;o<s;o++){var l,m,d,h;r?(l=n,C.push(i(e))):(l=a[o].match(c.regex.findStyles)&&RegExp.$1,C.push(RegExp.$2&&i(RegExp.$2))),h=(d=l.split(",")).length;for(var u=0;u<h;u++)m=d[u],f(m)||T.push({media:m.split("(")[0].match(c.regex.only)&&RegExp.$2||"all",rules:C.length-1,hasquery:-1<m.indexOf("("),minw:m.match(c.regex.minw)&&parseFloat(RegExp.$1)+(RegExp.$2||""),maxw:m.match(c.regex.maxw)&&parseFloat(RegExp.$1)+(RegExp.$2||"")})}R()},m=function(){if(i.length){var t=i.shift();e(t.href,function(e){l(e,t.href,t.media),r[t.href]=!0,y.setTimeout(function(){m()},0)})}},t=function(){for(var e=0;e<z.length;e++){var t=z[e],n=t.href,a=t.media,s=t.rel&&"stylesheet"===t.rel.toLowerCase();n&&s&&!r[n]&&(t.styleSheet&&t.styleSheet.rawCssText?(l(t.styleSheet.rawCssText,n,a),r[n]=!0):(/^([a-zA-Z:]*\/\/)/.test(n)||o)&&n.replace(RegExp.$1,"").split("/")[0]!==y.location.host||("//"===n.substring(0,2)&&(n=y.location.protocol+n),i.push({href:n,media:a})))}m()};t(),c.update=t,c.getEmValue=M,y.addEventListener?y.addEventListener("resize",n,!1):y.attachEvent&&y.attachEvent("onresize",n)}function n(){R(!0)}}(this);