!function(e){"use strict";angular.module("hametuha").controller("workContent",["$scope","$element",function(n,o){n.selection="",n.selectionTop=0,n.contentHeight=0,n.updateText=function(){var e=window.getSelection();if(n.selection=e.toString(),n.contentHeight=o[0].offsetHeight,"Range"===e.type){var t=e.getRangeAt(0).cloneRange().getBoundingClientRect();n.selectionTop=t.top-o[0].offsetTop-50}else n.selectionTop=0}}]).directive("textHolder",["$http",function(o){return{restrict:"E",replace:!0,scope:{selection:"=",selectionTop:"=",contentHeight:"=",id:"@"},templateUrl:Hametuha.template("text-holder.html"),link:function(n,e,t){n.styleTop=0,n.display="none",n.$watch("selection",function(e,t){e.length?(n.styleTop=n.selectionTop+document.body.scrollTop+"px",n.display="block"):n.display="none"}),n.share=function(){if(n.selection.length){var e=n.selection+"";return n.selection="",Hametuha.alert("リクエストしています……"),o({method:"POST",url:wpApiSettings.root+"hametuha/v1/text/of/"+n.id+"/",headers:{"X-WP-Nonce":wpApiSettings.nonce},data:{id:n.id,text:e}}).then(function(e){Hametuha.alert(e.data.message)},function(e){var t=e.data?e.data.message:"エラーが発生しました。やりなおしてください。";Hametuha.alert(t,!0)}).then(function(){n.selection=""})}}}}}])}(jQuery);
//# sourceMappingURL=../map/components/share.js.map
