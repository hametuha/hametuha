!function(e){var t={};function a(n){if(t[n])return t[n].exports;var r=t[n]={i:n,l:!1,exports:{}};return e[n].call(r.exports,r,r.exports,a),r.l=!0,r.exports}a.m=e,a.c=t,a.d=function(e,t,n){a.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},a.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.t=function(e,t){if(1&t&&(e=a(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)a.d(n,r,function(t){return e[t]}.bind(null,r));return n},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="",a(a.s=0)}([function(e,t,a){e.exports=a(1)},function(e,t){function a(e){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function r(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function o(e,t,a){return t&&r(e.prototype,t),a&&r(e,a),e}function c(e,t){return!t||"object"!==a(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function l(e){return(l=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function i(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var u=wp.element,m=u.Component,f=u.render,d=function(e){function t(e){var a;return n(this,t),(a=c(this,l(t).call(this,e))).state=Object.assign({login_url:"/wp-login.php?redirect_to="+location.pathname,register:"/wp-login.php?action=register&redirect_to="+location.pathname,lastChecked:0,notifications:[]},a.getStateValue()),a}return i(t,m),o(t,[{key:"componentDidMount",value:function(){var e=this;jQuery("html").on("cookie.tasting.updated",function(){e.setState(e.getStateValue())}),this.checkNotifications(),setInterval(function(){e.checkNotifications()},6e4)}},{key:"checkNotifications",value:function(){var e=this;this.state.loggedIn&&wp.apiFetch({path:"hametuha/v1/notifications/recent"}).then(function(t){e.setState({notifications:t})}).catch(function(e){Hametuha.alert(e.message||"エラーが発生しました。")})}},{key:"getStateValue",value:function(){return{logout:"/wp-login.php?action=logout&_wpnonce="+CookieTasting.get("logout"),loggedIn:CookieTasting.isLoggedIn(),avatar:CookieTasting.get("avatar")||"",name:CookieTasting.userName(),role:CookieTasting.get("role"),isAuthor:!!CookieTasting.get("is_author")}}},{key:"render",value:function(){var e=this;return React.createElement("ul",{className:"navbar-nav navbar-right navbar-login navbar-login--user nav nav-pills col-sm-1"},this.state.loggedIn?React.createElement("li",{className:"dropdown"},React.createElement("a",{href:"#",className:"dropdown-toggle","data-toggle":"dropdown"},React.createElement("img",{className:"avatar",src:this.state.avatar,alt:this.state.name})),React.createElement("ul",{className:"dropdown-menu"},React.createElement("li",{className:"greeting"},React.createElement("strong",null,this.state.name),"さん",React.createElement("br",null),React.createElement("span",{className:"role"},this.state.role)),React.createElement("li",{className:"divider"}),React.createElement("li",null,React.createElement("a",{href:"/dashboard"},React.createElement("i",{className:"icon-cog"}),"ダッシュボード")),this.state.isAuthor?React.createElement("li",null,React.createElement("a",{href:"/wp-admin/edit.php"},React.createElement("i",{className:"icon-dashboard"}),"作品管理")):null,React.createElement("li",{className:"divider"}),React.createElement("li",null,React.createElement("a",{href:"/your/comments/"},React.createElement("i",{className:"icon-bubble-dots"}),"あなたのコメント")),React.createElement("li",null,React.createElement("a",{href:"/your/lists/"},React.createElement("i",{className:"icon-drawer3"}),"あなたのリスト")),React.createElement("li",null,React.createElement("a",{href:"/your/reviews/"},React.createElement("i",{className:"icon-star2"}),"レビューした作品")),React.createElement("li",null,React.createElement("a",{href:"/my/ideas/"},React.createElement("i",{className:"icon-lamp4"}),"アイデア帳")),React.createElement("li",null,React.createElement("a",{href:"/doujin/follower/"},React.createElement("i",{className:"icon-heart5"}),"フォロワー")),React.createElement("li",{className:"divider"}),React.createElement("li",null,React.createElement("a",{href:this.state.logout},React.createElement("i",{className:"icon-exit4"}),"ログアウト")))):null,this.state.loggedIn?React.createElement("li",{className:"dropdown"},React.createElement("a",{href:"#",className:"dropdown-toggle dropdown--notify","data-toggle":"dropdown","data-last-checked":this.state.lastChecked},React.createElement("i",{className:"icon-earth"})),React.createElement("ul",{id:"notification-container",className:"dropdown-menu notification__container"},this.state.notifications.length?this.state.notifications.map(function(e){return React.createElement("li",{className:"notification__item--header",dangerouslySetInnerHTML:{__html:e.rendered}})}):React.createElement("li",null,React.createElement("span",null,"お知らせはなにもありません。")),React.createElement("li",{className:"text-center notification__more"},React.createElement("a",{href:"/dashboard/notifications/all"},"通知一覧へ",React.createElement("i",{className:"icon-arrow-right4"}))))):null,this.state.loggedIn?null:React.createElement("li",{className:"login-buttons"},React.createElement("a",{href:this.state.login_url,onClick:function(t){e.handleClick(t)}},"ログイン"),React.createElement("a",{href:this.state.register,onClick:function(t){e.handleClick(t)}},"登録")))}},{key:"handleClick",value:function(e){var t=this;e.preventDefault();var a=e.currentTarget;a.classList.add("disabled"),CookieTasting.testBefore().then(function(){t.setState(t.getStateValue())}).catch(function(){window.location.href=a.href}).finally(function(){a.classList.remove("disabled")})}}]),t}();f(React.createElement(d,null),document.getElementById("user-info"))}]);
//# sourceMappingURL=header.js.map