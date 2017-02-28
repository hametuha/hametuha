angular.module("hametuha").controller("ideaList",["$scope","$http",function(e,a){"use strict";var t=wpApiSettings.root+"hametuha/v1/idea/mine/";e.loading=!1,e.ideas=[],e.ideasOffset=0,e.ideasTotal=1,e.ideasMore=!0,e.query="",e.initIdeas=function(a){e.getIdeas(0)},e.getIdeas=function(n){var i={offset:n};e.query.length&&(i.s=e.query),e.loading=!0,a({method:"GET",url:t,headers:{"X-WP-Nonce":wpApiSettings.nonce},params:i}).then(function(a){e.ideasTotal=a.data.total,e.ideasOffset=a.data.offset,a.data.ideas.length?angular.forEach(a.data.ideas,function(a){e.ideas.push(a)}):(e.ideasMore=!1,Hametuha.alert("これ以上のアイデアは保存されていません。")),e.loading=!1},function(a){Hametuha.alert("アイデアを取得できませんでした",!0),e.loading=!1})},e.search=function(a){e.query=a;for(var t=e.ideas.length-1;t>=0;t--)delete e.ideas[t];e.ideas=[],e.ideasOffset=0,e.ideasTotal=1,e.ideasMore=!0,e.getIdeas(0)},e.nextIdeas=function(){e.getIdeas(e.ideasOffset+20)},e.stock=function(t){for(var n=null,i=0,o=e.ideas.length;o>i;i++)if(e.ideas[i].ID==t){n=i;break}null!==n&&(e.loading=!0,a({method:"POST",url:wpApiSettings.root+"hametuha/v1/idea/"+t+"/",headers:{"X-WP-Nonce":wpApiSettings.nonce}}).then(function(a){e.ideas[n].location=1},function(e){Hametuha.alert(e.data.message,!0)}).then(function(){e.loading=!1}))},e.unstock=function(t){for(var n=null,i=0,o=e.ideas.length;o>i;i++)if(e.ideas[i].ID==t){n=i;break}null!==n&&(e.loading=!0,Hametuha.confirm("このアイデアのストックを解除しますか？",function(){a({method:"DELETE",url:wpApiSettings.root+"hametuha/v1/idea/"+t+"/",headers:{"X-WP-Nonce":wpApiSettings.nonce}}).then(function(a){var t=e.ideas[n];t.own?(e.ideas[i].location=0,e.ideas[i].stocking=!1):(e.ideas.splice(n,1),e.ideasTotal--,e.offset--)},function(e){Hametuha.alert(e.data.message,!0)}).then(function(){e.loading=!1})}))},e.removeIdea=function(n){Hametuha.confirm("このアイデアを削除してよろしいですか？",function(){for(var i=null,o=0,s=e.ideas.length;s>o;o++)if(e.ideas[o].ID==n){i=o;break}null!==i&&(e.loading=!0,a({method:"DELETE",url:t+"?post_id="+n,headers:{"X-WP-Nonce":wpApiSettings.nonce}}).then(function(a){e.ideas.splice(i,1),e.ideasTotal--,e.offset--},function(e){Hametuha.alert(e.data.message,!0)}).then(function(){e.loading=!1}))},!0)}}]);
//# sourceMappingURL=../map/components/ideas.js.map
