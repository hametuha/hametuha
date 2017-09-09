/**
 * Description
 */

/*global Hametuha: true*/

(function ($) {
    'use strict';
    angular.module('hametuha')
      .controller('workContent', ['$scope', '$element', function($scope, $element){

        $scope.selection = '';

        $scope.selectionTop = 0;

        $scope.contentHeight = 0;

        $scope.updateText = function() {
          var selection = window.getSelection();
          $scope.selection = selection.toString();
          $scope.contentHeight = $element[0].offsetHeight;
          if ( 'Range' === selection.type ) {
            var range = selection.getRangeAt(0).cloneRange();
            var rect = range.getBoundingClientRect();
            $scope.selectionTop = rect.top - $element[0].offsetTop - 50;
          } else {
            $scope.selectionTop = 0;
          }
        }
      }])
      .directive('textHolder', [ '$http', function ($http) {

        'use strict';

        return {
          restrict   : 'E',
          replace    : true,
          scope      : {
            selection : '=',
            selectionTop: '=',
            contentHeight: '=',
            id: '@'
          },
          templateUrl: Hametuha.template('text-holder.html'),
          link       : function ($scope, $elem, attr) {

            $scope.styleTop = 0;

            $scope.display = 'none';

            $scope.$watch('selection', function(newValue, oldValue){
              if ( newValue.length ) {
                $scope.styleTop = ( $scope.selectionTop + document.body.scrollTop ) + 'px';
                $scope.display = 'block';
              } else {
                $scope.display = 'none';
              }
            });

            $scope.share = function(){
              if ( ! $scope.selection.length ) {
                return;
              }
              // Copy text and share
              var text = $scope.selection + '';
              $scope.selection = '';
              Hametuha.alert('リクエストしています……');
              return $http({
                method: 'POST',
                url: wpApiSettings.root + 'hametuha/v1/text/of/' + $scope.id + '/',
                headers: {
                  'X-WP-Nonce': wpApiSettings.nonce
                },
                data: {
                  id: $scope.id,
                  text: text
                }
              }).then(
                function(response){
                  Hametuha.alert( response.data.message )
                },
                function(response){
                  var message = response.data ? response.data.message : 'エラーが発生しました。やりなおしてください。';
                  Hametuha.alert(message, true);
                }).then(function(){
                  $scope.selection = '';
                }
              );
            };

          }
        };
      }]);
})(jQuery);
