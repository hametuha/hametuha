/**
 * Description
 */
!function(t){"use strict";t(document).ready(function(){var a,e,n=t("#genre-context"),o={labels:[],datasets:[{data:[],backgroundColor:[]}]};n.length&&Modernizr.canvas&&(t.each(HametuhaGenreStatic.categories,function(t,a){return!(t>10)&&(o.labels.push(a.name),o.datasets[0].data.push(parseInt(a.count,10)),void o.datasets[0].backgroundColor.push("rgba(255, 0, 0, "+Math.min(1,Math.round(a.count/HametuhaGenreStatic.total*.8*10)/10+.2)+")"))}),a=n.get(0).getContext("2d"),e=new Chart(a,{type:"doughnut",data:o}));var r=t(".frontpage-widget");r.imagesLoaded(function(){r.masonry({itemSelector:".col-sm-4"})})})}(jQuery);
//# sourceMappingURL=../map/components/front-helper.js.map
