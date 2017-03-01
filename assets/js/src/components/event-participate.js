/**
 * Description
 */

/* global HamEvent: true*/
/* global Hametuha: true*/
/* global wpApiSettings: false */

(function(){

angular.module('hametuha')
  .controller( 'hameventStatus', [ '$scope', '$http', '$timeout', function( $scope, $http, $timeout ){

    $scope.participants = HamEvent.participants;

    $scope.inList = HamEvent.inList;

    $scope.limit = parseInt(HamEvent.limit, 10);

    $scope.text = HamEvent.text;

    $scope.loading = false;

    var timer = null;

    var saveStatus = function(status, text, callback){
      if ( $scope.loading ) {
        return;
      } else if (timer) {
        $timeout.cancel(timer);
      }
      var oldStatus = !! $scope.status;
      $scope.loading = true;
      return $http({
        method: 'POST',
        url: wpApiSettings.root + 'hametuha/v1/participants/' + HamEvent.event + '/',
        headers: {
          'X-WP-Nonce': wpApiSettings.nonce
        },
        data: {
          status: status,
          text: text
        }
      }).then(callback, function(response){
        var message = response.data ? response.data.message : 'エラーが発生しました。やりなおしてください。';
        Hametuha.alert(message, true);
      }).then(function(){
        $scope.loading = false;
      });
    };

    var findIndex = function(id){
      for(var i = 0, l = $scope.participants.length; i < l; i++){
        if( id === $scope.participants[i].id ){
          return i;
        }
      }
      return false;
    };

    $scope.getOut = function(){
      saveStatus(false, $scope.text, function(response){
        var index = findIndex(response.data.id);
        if ( false !== index ) {
          $scope.participants.splice(index, 1);
          $scope.inList = false;
        }
      });
    };

    $scope.getIn = function(){
      saveStatus(true, $scope.text, function(response){
        var index = findIndex(response.data.id);
        if ( false === index ) {
          $scope.participants.push(response.data);
          $scope.inList = true;
        }
      });
    };

    $scope.updateComment = function(){
      if ( timer ) {
        $timeout.cancel( timer );
      }
      timer = $timeout( function(){
        saveStatus($scope.inList, $scope.text, function(){
          // Do nothing.
        });
      }, 3000 );
    };


  }]);

})();
