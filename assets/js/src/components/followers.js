/**
 * Follower application
 */

/*global WP_API_Settings: true*/
/*global Hametuha: true*/

angular.module('hametuFollower', [])
    .controller('followed', ['$scope', '$http', function($scope, $http){

        $scope.followers = [];

        $scope.offset = 0;

        $scope.noFollowers = false;

        $scope.total = 1;

        $scope.more = true;

        $scope.getFollowers = function(offset){
            var endpoint = WP_API_Settings.root + 'hametuha/v1/doujin/followers/me/';
            $http({
                method: 'GET',
                url: endpoint,
                headers: {
                    'X-WP-Nonce': WP_API_Settings.nonce
                },
                params: {
                    offset: offset
                }
            }).then(
                function(result){
                    $scope.total = result.data.total;
                    $scope.offset = result.data.offset;
                    console.log(result.data);
                    if( result.data.users.length ){
                        angular.forEach( result.data.users, function(user){
                            $scope.followers.push(user);
                        } );
                    }else{
                        $scope.more = false;
                    }
                },
                function(result){
                    Hametuha.alert('フォロワーを取得できませんでした', true);
                }
            );
        };

        $scope.next = function(){
            $scope.getFollowers($scope.offset + 20);
        };

    }])
;
