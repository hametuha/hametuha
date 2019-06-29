!function(e){var t={};function n(a){if(t[a])return t[a].exports;var r=t[a]={i:a,l:!1,exports:{}};return e[a].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,a){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(n.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(a,r,function(t){return e[t]}.bind(null,r));return a},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=8)}({11:function(e,t,n){"use strict";function a(e){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function r(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function o(e,t){return!t||"object"!==a(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function l(e){return(l=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function c(e,t){return(c=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}n.r(t);var i=wp.element.Component,s=function(e){function t(e){var n;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),(n=o(this,l(t).call(this,e))).state={loading:!1,slug:""},n}var n,a,s;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&c(e,t)}(t,i),n=t,(a=[{key:"handleClick",value:function(){var e=this;this.setState({loading:!0},function(){wp.apiFetch({path:"hametuha/v1/collaborators/".concat(e.props.postId),method:"POST",data:{collaborator:e.state.slug}}).then(function(t){e.props.addHandler(t)}).catch(function(e){alert(e.message||"ユーザーを追加できませんでした。")}).finally(function(t){e.setState({loading:!1})})})}},{key:"render",value:function(){var e=this;return React.createElement("tr",{className:this.state.loading?"hametuha-loading":""},React.createElement("td",null," "),React.createElement("td",{colSpan:3},React.createElement("input",{type:"text",className:"widefat",placeholder:"ユーザーIDからスラッグを入れてください。",value:this.state.slug,onChange:function(t){return e.setState({slug:t.target.value})}})),React.createElement("td",{className:"collaborators-actions"},React.createElement("a",{href:"#",className:"button",onClick:function(t){t.preventDefault(),e.handleClick()}},React.createElement("span",{className:"dashicons dashicons-plus-alt"})," 追加")))}}])&&r(n.prototype,a),s&&r(n,s),t}();function u(e){return(u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function f(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function p(e){return(p=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function d(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function h(e,t){return(h=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var m=wp.element.Component,b=function(e){function t(e){var n,a,r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),a=this,(n=!(r=p(t).call(this,e))||"object"!==u(r)&&"function"!=typeof r?d(a):r).state={ratio:100*n.props.user.ratio},n.handleDelete=n.handleDelete.bind(d(n)),n.handleEdit=n.handleEdit.bind(d(n)),n}var n,a,r;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&h(e,t)}(t,m),n=t,(a=[{key:"handleEdit",value:function(e){e.preventDefault(),this.props.updateHandler(this.props.user.id,this.state.ratio)}},{key:"handleDelete",value:function(e){e.preventDefault(),confirm("".concat(this.props.user.name,"さんを関係者から削除してよろしいですか？"))&&this.props.deleteHandler(this.props.user)}},{key:"render",value:function(){var e=this,t=this.props.user.assigned;return React.createElement("tr",{className:["collaborators-item"].join(" ")},React.createElement("th",{className:"collaborators-list-number"},this.props.user.id),React.createElement("td",null,React.createElement("img",{alt:this.props.user.name,className:"collaborators-avatar",src:this.props.user.avatar}),React.createElement("a",{className:"collaborators-name",href:this.props.user.url},this.props.user.name),React.createElement("small",null,"（",this.props.user.label,"）")),React.createElement("td",{className:"collaborators-revenue"},this.props.user.ratio<0?React.createElement("span",{className:"collaborators-revenue-waiting"},React.createElement("span",{className:"dashicons dashicons-warning"}),"承認待ち"):React.createElement("label",null,React.createElement("input",{className:"collaborators-revenue-input",type:"number",step:1,max:100,min:0,value:this.state.ratio,onChange:function(t){return e.setState({ratio:t.target.value})}}),"%")),React.createElement("td",{className:"collaborators-assigned"},React.createElement("span",{title:t,className:"dashicons dashicons-clock"}),React.createElement("time",{dateTime:t},moment(t).format("YYYY.MM.DD"))),React.createElement("td",{className:"collaborators-actions"},0>this.props.user.ratio?null:React.createElement("button",{className:"button",onClick:this.handleEdit},"更新"),React.createElement("a",{className:"collaborators-delete-link",href:"#",onClick:this.handleDelete},"削除")))}}])&&f(n.prototype,a),r&&f(n,r),t}();function y(e){return(y="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function v(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function g(e){return(g=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function E(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function R(e,t){return(R=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}
/*!
 * wpdeps=wp-api-fetch, wp-element, moment
 */var S=wp.element,O=S.render,w=S.Component,_=S.Fragment,j=function(e){function t(e){var n,a,r;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),a=this,(n=!(r=g(t).call(this,e))||"object"!==y(r)&&"function"!=typeof r?E(a):r).state={loading:!0,collaborators:[],shareType:CollaboratorsList.shareType,id:parseInt(CollaboratorsList.series_id,10)},n.addHandler=n.addHandler.bind(E(n)),n.updateHandler=n.updateHandler.bind(E(n)),n.deleteHandler=n.deleteHandler.bind(E(n)),n}var n,a,r;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&R(e,t)}(t,w),n=t,(a=[{key:"componentDidMount",value:function(){var e=this;wp.apiFetch({path:"/hametuha/v1/collaborators/".concat(CollaboratorsList.series_id)}).then(function(t){e.setState({collaborators:t})}).catch(function(e){alert(e.message||"関係者一覧を取得できませんでした。")}).finally(function(t){e.setState({loading:!1})})}},{key:"addHandler",value:function(e){var t=this.state.collaborators;t.push(e),this.setState({collaborators:t})}},{key:"updateHandler",value:function(e,t){var n=this;this.setState({loading:!0},function(){wp.apiFetch({path:"/hametuha/v1/collaborators/".concat(CollaboratorsList.series_id),method:"PUT",data:{collaborator_id:e,margin:t}}).then(function(a){var r=[],o=!0,l=!1,c=void 0;try{for(var i,s=n.state.collaborators[Symbol.iterator]();!(o=(i=s.next()).done);o=!0){var u=i.value;u.id===e&&(u.ratio=t),r.push(u)}}catch(e){l=!0,c=e}finally{try{o||null==s.return||s.return()}finally{if(l)throw c}}n.setState({collaborators:r})}).catch(function(e){alert(e.message||"更新できませんでした。")}).finally(function(e){n.setState({loading:!1})})}),alert("".concat(e,"が").concat(t,"パーセント"))}},{key:"deleteHandler",value:function(e){var t=this;this.setState({loading:!0},function(){wp.apiFetch({path:"/hametuha/v1/collaborators/".concat(CollaboratorsList.series_id,"?collaborator_id=").concat(e.id),method:"DELETE"}).then(function(e){var n=[],a=!0,r=!1,o=void 0;try{for(var l,c=t.state.collaborators[Symbol.iterator]();!(a=(l=c.next()).done);a=!0){var i=l.value;i.id!=e.id&&n.push(i)}}catch(e){r=!0,o=e}finally{try{a||null==c.return||c.return()}finally{if(r)throw o}}t.setState({collaborators:n})}).catch(function(e){alert(e.message||"削除に失敗しました。")}).finally(function(e){t.setState({loading:!1})})})}},{key:"render",value:function(){var e=this,t=["collaborators-list"],n={};return this.state.loading?(t.push("hametuha-loading"),n.minHeight="150px"):this.state.collaborators.length||(n.display="none"),React.createElement(_,null,React.createElement("table",{className:t.join(" "),style:n},React.createElement("caption",null,"関係者"),React.createElement("thead",null,React.createElement("tr",null,React.createElement("th",{className:"collaborators-list-number"},"#"),React.createElement("th",{style:{textAlign:"left"}},"名前"),React.createElement("th",{style:{textAlign:"left"}},"報酬"),React.createElement("th",{className:"collaborators-assigned"},"追加日時"),React.createElement("th",null,"アクション"))),React.createElement("tfoot",null,React.createElement(s,{postId:this.state.id,addHandler:this.addHandler})),React.createElement("tbody",null,this.state.collaborators.map(function(t){return React.createElement(b,{key:t.id,user:t,deleteHandler:e.deleteHandler,updateHandler:e.updateHandler})}))))}}])&&v(n.prototype,a),r&&v(n,r),t}(),P=document.getElementById("series-collaborators");P&&O(React.createElement(j,null),P)},8:function(e,t,n){e.exports=n(11)}});
//# sourceMappingURL=collaborators-list.js.map