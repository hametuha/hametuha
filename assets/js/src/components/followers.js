/**
 * Follower application
 */

/*global WP_API_Settings: true*/
/*global Hametuha: true*/

angular.module('hametuFollower', ['ui.bootstrap'])
    .controller('followed', ['$scope', '$http', function ($scope, $http) {

        var endpoint = WP_API_Settings.root + 'hametuha/v1/doujin/';

        $scope.tabs = [
            {
                active: false,
                init  : false
            },
            {
                active: false,
                init  : false
            }
        ];

        // Activate tab
        $scope.detectTab = function () {
            var hash = document.location.hash;
            switch (hash) {
                case '#following':
                    $scope.tabs[1].active = true;
                    break;
                default:
                    $scope.tabs[0].active = true;
                    break;
            }

        };

        // Initialize followers.
        $scope.initFollowers = function (index) {
            if (!$scope.tabs[index].init) {
                switch (index) {
                    case 1:
                        $scope.getFollowings(0);
                        break;
                    default:
                        $scope.getFollowers(0);
                        break;
                }
                $scope.tabs[index].init = true;
            }
        };

        // Followers
        $scope.followers = [];
        $scope.followersOffset = 0;
        $scope.followersTotal = 1;
        $scope.followersMore = true;

        // Get followers
        $scope.getFollowers = function (offset) {
            $http({
                method : 'GET',
                url   : endpoint + 'followers/me/',
                headers: {
                    'X-WP-Nonce': WP_API_Settings.nonce
                },
                params : {
                    offset: offset
                }
            }).then(
                function (result) {
                    $scope.followersTotal = result.data.total;
                    $scope.followersOffset = result.data.offset;
                    if (result.data.users.length) {
                        angular.forEach(result.data.users, function (user) {
                            $scope.followers.push(user);
                        });
                    } else {
                        $scope.followersMore = false;
                    }
                },
                function (result) {
                    Hametuha.alert('フォロワーを取得できませんでした', true);
                }
            );
        };

        // Get next followers
        $scope.nextFollowers = function () {
            $scope.getFollowers($scope.followersOffset + 20);
        };


        // Followings
        $scope.followings = [];
        $scope.followingOffset = 0;
        $scope.followingTotal = 1;
        $scope.followingMore = true;

        // Get followings
        $scope.getFollowings = function (offset) {
            $http({
                method : 'GET',
                url   : endpoint + 'following/me/',
                headers: {
                    'X-WP-Nonce': WP_API_Settings.nonce
                },
                params : {
                    offset: offset
                }
            }).then(
                function (result) {
                    $scope.followingTotal = result.data.total;
                    $scope.followingOffset = result.data.offset;
                    if (result.data.users.length) {
                        angular.forEach(result.data.users, function (user) {
                            $scope.followings.push(user);
                        });
                    } else {
                        $scope.followingMore = false;
                    }
                },
                function (result) {
                    Hametuha.alert('フォローしている人を取得できませんでした', true);
                }
            );
        };

        // Remove Follower
        $scope.removeFollowing = function (id) {
            Hametuha.confirm('フォローを解除してよろしいですか？', function () {

                var indexToRemove = null;

                for (var i = 0, l = $scope.followings.length; i < l; i++) {
                    if ($scope.followings[i].ID == id) {
                        indexToRemove = i;
                        break;
                    }
                }


                if (null !== indexToRemove) {
                    $http({
                        method : 'DELETE',
                        url   : endpoint + 'follow/' + id + '/',
                        headers: {
                            'X-WP-Nonce': WP_API_Settings.nonce
                        }
                    }).then(
                        function (result) {
                            if( result.data.success ){
                                $scope.followings.splice(indexToRemove, 1);
                            }
                        },
                        function () {
                            Hametuha.alert('削除できませんでした。', true)
                        }
                    );
                }

            });

        };

        // Get next followings
        $scope.nextFollowers = function () {
            $scope.getFollowings($scope.followingOffset + 20);
        };

    }])
;
