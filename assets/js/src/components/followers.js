/**
 * Follower application
 */

/* global wpApiSettings: false */

angular.module('hametuha', ['ui.bootstrap'])
    .controller('followed', ['$scope', '$http', function ($scope, $http) {

        'use strict';

        var endpoint = wpApiSettings.root + 'hametuha/v1/doujin/';

        $scope.tabs = [
            {
                active: false,
                init  : false,
                loading: false
            },
            {
                active: false,
                init  : false,
                loading: false
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
            $scope.tabs[0].loading = true;
            $http({
                method : 'GET',
                url   : endpoint + 'followers/me/',
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
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
                        Hametuha.alert('フォロワーはこれ以上いません');
                    }
                    $scope.tabs[0].loading = false;
                },
                function (result) {
                    Hametuha.alert('フォロワーを取得できませんでした', true);
                    $scope.tabs[0].loading = false;
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
            $scope.tabs[1].loading = true;
            $http({
                method : 'GET',
                url   : endpoint + 'following/me/',
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
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
                        Hametuha.alert('フォロワーしている人はもういません');
                    }
                    $scope.tabs[1].loading = false;
                },
                function (result) {
                    Hametuha.alert('フォローしている人を取得できませんでした', true);
                    $scope.tabs[1].loading = false;
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
                    $scope.tabs[1].loading = true;
                    $http({
                        method : 'DELETE',
                        url   : endpoint + 'follow/' + id + '/',
                        headers: {
                            'X-WP-Nonce': wpApiSettings.nonce
                        }
                    }).then(
                        function (result) {
                            if( result.data.success ){
                                $scope.followings.splice(indexToRemove, 1);
                                $scope.followingTotal--;
                            }
                            $scope.tabs[1].loading = false;
                        },
                        function () {
                            Hametuha.alert('削除できませんでした。', true);
                            $scope.tabs[1].loading = false;
                        }
                    );
                }

            }, true);

        };

        // Get next followings
        $scope.nextFollowing = function () {
            $scope.getFollowings($scope.followingOffset + 20);
        };

    }])
;
