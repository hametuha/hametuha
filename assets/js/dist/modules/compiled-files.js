!function(e){var t={};function n(o){if(t[o])return t[o].exports;var a=t[o]={i:o,l:!1,exports:{}};return e[o].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)n.d(o,a,function(t){return e[t]}.bind(null,a));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=7)}({7:function(e,t,n){e.exports=n(8)},8:function(e,t){
/*!
 * wpdeps=jquery, wp-api-fetch, jquery-effects-highlight, moment
 */
var n=jQuery;n(document).on("click",".compiled-file-delete-btn",function(e){if(e.preventDefault(),window.confirm("本当に削除してよろしいですか？　この操作は取り消せません。")){var t=n(this).attr("data-file-id"),o=n(this).parents("td");o.addClass("loading"),wp.apiFetch({path:"/hametuha/v1/epub/file/".concat(t),method:"DELETE"}).then(function(e){o.removeClass("loading"),o.parents("tr").effect("highlight",{},500,function(){n(this).fadeOut(300,function(){n(this).remove()})})}).catch(function(e){o.removeClass("loading"),alert(e.message||"エラーが発生しました。")})}}),n(document).ready(function(){n("iframe[name=file-downloader]").load(function(e){if(e.target.contentDocument&&e.target.contentDocument.body&&e.target.contentDocument.body.innerText){var t=JSON.parse(e.target.contentDocument.body.innerText);t.message&&alert(t.message)}})}),n(document).on("click",".compiled-file-validate-btn",function(e){e.preventDefault();var t=n(this).attr("data-file-id"),o=n(this).parents("td");o.addClass("loading"),wp.apiFetch({path:"/hametuha/v1/epub/file/".concat(t,"?format=report")}).then(function(e){alert(e.message),window.console&&console.log(e)}).catch(function(e){var t=["【バリデーション失敗】"];if(t.push(e.message),e.additional_errors){var n=!0,o=!1,a=void 0;try{for(var r,i=e.additional_errors[Symbol.iterator]();!(n=(r=i.next()).done);n=!0){var l=r.value;t.push(l.message)}}catch(e){o=!0,a=e}finally{try{n||null==i.return||i.return()}finally{if(o)throw a}}}alert(t.join("\n")),window.console&&console.log(e)}).finally(function(e){o.removeClass("loading")})}),n(document).on("click",".compiled-file-published-btn",function(e){e.preventDefault();var t=n(this).attr("data-published"),o=n(this).attr("data-file-id"),a=n(this).parents("td"),r=window.prompt("公開日時を入力してください。\n削除する場合は大文字で DELETE と入力してください。",t||moment().format("YYYY-MM-DD HH:mm:ss"));if(!r)return!0;a.addClass("loading"),wp.apiFetch({path:"/hametuha/v1/epub/file/".concat(o),method:"POST",data:{published:r}}).then(function(e){var t="";t="DELETE"===r?"---":r,a.removeClass("loading").parents("tr").effect("highlight").find(".compile-file-published").text(t)}).catch(function(e){alert(e.message||"更新に失敗しました。"),a.removeClass("loading")})})}});
//# sourceMappingURL=compiled-files.js.map