/**
 * Description
 */

/*global Hametuha: true*/

(function ($) {
    'use strict';
    angular.module('hametuha')
      .controller('workContent', ['$scope', function($scope){

        $scope.selection = '';

        $scope.updateText = function() {
          $scope.selection = window.getSelection().toString();
        }
      }])
      .directive('textHolder', [function () {

        'use strict';

        return {
          restrict   : 'E',
          replace    : false,
          scope      : {
            selection : '='
          },
          templateUrl: Hametuha.template('text-holder.html'),
          link       : function ($scope, $elem, attr) {

            $scope.share = function(){
              alert($scope.selection);
            };

          }
        };
      }]);
})(jQuery);
