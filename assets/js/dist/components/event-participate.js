angular.module("hametuha").controller("hameventStatus",["$scope","$http","$timeout",function(i,e,u){i.participants=HamEvent.participants,i.inList=HamEvent.inList,i.limit=parseInt(HamEvent.limit,10),i.text=HamEvent.text,i.loading=!1;var c=null,t=function(t,n,a){if(!i.loading)return c&&u.cancel(c),i.status,i.loading=!0,e({method:"POST",url:wpApiSettings.root+"hametuha/v1/participants/"+HamEvent.event+"/",headers:{"X-WP-Nonce":wpApiSettings.nonce},data:{status:t,text:n}}).then(a,function(t){var n=t.data?t.data.message:"エラーが発生しました。やりなおしてください。";Hametuha.alert(n,!0)}).then(function(){i.loading=!1})},a=function(t){for(var n=0,a=i.participants.length;n<a;n++)if(t===i.participants[n].id)return n;return!1};i.getOut=function(){t(!1,i.text,function(t){var n=a(t.data.id);!1!==n&&(i.participants.splice(n,1),i.inList=!1)})},i.getIn=function(){t(!0,i.text,function(t){!1===a(t.data.id)&&(i.participants.push(t.data),i.inList=!0)})},i.updateComment=function(){c&&u.cancel(c),c=u(function(){t(i.inList,i.text,function(){})},3e3)}}]);
//# sourceMappingURL=../map/components/event-participate.js.map
