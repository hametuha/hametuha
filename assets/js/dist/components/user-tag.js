!function(t){"use strict";Hametuha.models.userTag=Backbone.Model.extend({defaults:{me:!1,name:"",taxonomy_id:0,url:"",number:0}}),Hametuha.collections.tagCollection=Backbone.Collection.extend({models:Hametuha.models.userTag}),Hametuha.views.Tag=Backbone.View.extend({tagName:"a",events:{"click i":"clickHandler"},initialize:function(){_.bindAll(this,"render","clickHandler"),this.model.bind("change",this.render)},render:function(){return this.$el.html(this.model.get("name")+"("+(this.model.get("number")>100?"100+":this.model.get("number"))+")<i></i>"),this.$el.attr("href",this.model.get("url")).attr("data-taxonomy-id",this.model.get("taxonomy_id")).attr("data-term",this.model.get("name")).attr("data-number",this.model.get("number")),this.model.get("me")?this.$el.addClass("me"):this.$el.removeClass("me"),this},clickHandler:function(e){e.preventDefault(),e.stopPropagation();var a=this;return t.ajax(this.model.get("me")?HametuhaUserTag.tagRemove:HametuhaUserTag.tagAdd,{type:"POST",dataType:"json",data:{taxonomy_id:this.model.get("taxonomy_id")},success:function(t){t.success?t.tag?a.model.set(t.tag):(a.$el.remove(),a.model.destroy()):Hametuha.alert(t.message,!0)},error:function(){Hametuha.alert("タグを更新できませんした。",!0)}}),!1}}),Hametuha.views.TagManager=Backbone.View.extend({el:"#post-tags",collection:null,tagList:null,post_id:0,events:{"submit #user-tag-editor":"submitTag"},initialize:function(){_.bindAll(this,"grepItem","appendItem","watchCollectionCount","submitTag"),this.tagList=t("#user-tag-list"),this.post_id=this.tagList.attr("data-post-id"),this.collection=new Hametuha.collections.tagCollection,this.tagList.find("a").each(this.grepItem),this.collection.bind("add",this.appendItem),this.collection.bind("remove",this.watchCollectionCount)},grepItem:function(e,a){var i=new Hametuha.models.userTag;i.set({me:t(a).hasClass("me"),name:t(a).attr("data-term"),taxonomy_id:t(a).attr("data-taxonomy-id"),url:t(a).attr("href"),number:t(a).attr("data-number")}),this.collection.add(i),this.appendItem(i)},appendItem:function(t){var e=new Hametuha.views.Tag({model:t}),a=this.$el.find("a[data-taxonomy-id="+t.get("taxonomy_id")+"]");a.length?e.setElement(a.get(0)):(this.tagList.append(e.render().$el),this.watchCollectionCount())},watchCollectionCount:function(){this.collection.length?this.tagList.removeClass("no-tag"):this.tagList.addClass("no-tag")},submitTag:function(e){var a=t(e.target).find("input[type=text]"),i=a.val(),n=this;return i.length&&t.ajax(HametuhaUserTag.tagCreate,{type:"POST",dataType:"json",data:{term:i},success:function(i){if(i.success){var o=!1;if(n.collection.each(function(t){t.get("taxonomy_id")===i.tag.taxonomy_id&&(o=!0,t.set(i.tag))}),!o){var s=new Hametuha.models.userTag;s.set(i.tag),n.collection.add(s)}}else Hametuha.alert(i.message,!0);a.val(""),t(e.target).find("input[type=submit]").prop("disabled",!1)},error:function(){t(e.target).find("input[type=submit]").prop("disabled",!1),Hametuha.alert("タグを追加できませんした。",!0)}}),!1}}),t(document).ready(function(){t("input[type=text]","#user-tag-editor").autocomplete({source:HametuhaUserTag.tagSearch,minLength:1,delay:500,select:function(e,a){a.item&&a.item.label&&t("#user-tag-editor").submit()}}),new Hametuha.views.TagManager})}(jQuery);
//# sourceMappingURL=../map/components/user-tag.js.map