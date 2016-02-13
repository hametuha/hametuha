/**
 * Follower application
 */

/* global wpApiSettings:false */

angular.module('hametuha', ['ui.bootstrap'])
    .controller('ideaList', ['$scope', '$http', function ($scope, $http) {

        'use strict';

        var endpoint = wpApiSettings.root + 'hametuha/v1/idea/mine/';

        // ideas
        $scope.loading = false;
        $scope.ideas = [];
        $scope.ideasOffset = 0;
        $scope.ideasTotal = 1;
        $scope.ideasMore = true;
        $scope.query = '';

        // Initialize ideas.
        $scope.initIdeas = function (index) {
            $scope.getIdeas(0);
        };

        // Get ideas
        $scope.getIdeas = function (offset) {
            var params = {
                offset: offset
            };
            if( $scope.query.length ){
                params.s = $scope.query;
            }
            $scope.loading = true;
            $http({
                method : 'GET',
                url    : endpoint,
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                params : params
            }).then(
                function (result) {
                    $scope.ideasTotal = result.data.total;
                    $scope.ideasOffset = result.data.offset;
                    if ( result.data.ideas.length ) {
                        angular.forEach(result.data.ideas, function (idea) {
                            $scope.ideas.push(idea);
                        });
                    } else {
                        $scope.ideasMore = false;
                        Hametuha.alert('これ以上のアイデアは保存されていません。');
                    }
                    $scope.loading = false;
                },
                function (result) {
                    Hametuha.alert('アイデアを取得できませんでした', true);
                    $scope.loading = false;
                }
            );
        };

        // Add query and refresh search
        $scope.search = function(query){
            $scope.query = query;
            for( var i = $scope.ideas.length - 1; i >= 0; i--){
                delete $scope.ideas[i];
            }
            $scope.ideas = [];
            $scope.ideasOffset = 0;
            $scope.ideasTotal = 1;
            $scope.ideasMore = true;
            $scope.getIdeas(0);
        };

        // Get next ideas
        $scope.nextIdeas = function () {
            $scope.getIdeas($scope.ideasOffset + 20);
        };

        // Stock recommended
        $scope.stock = function(id){
            var indexToUpdate = null;
            for(var i = 0, l = $scope.ideas.length; i < l; i++){
                if( $scope.ideas[i].ID == id ){
                    indexToUpdate = i;
                    break;
                }
            }
            if( null !== indexToUpdate ) {
                $scope.loading = true;
                $http({
                    method : 'POST',
                    url    : wpApiSettings.root + 'hametuha/v1/idea/' + id + '/',
                    headers: {
                        'X-WP-Nonce': wpApiSettings.nonce
                    }
                }).then(
                    function(response){
                       $scope.ideas[indexToUpdate].location = 1;
                    },
                    function(response){
                        Hametuha.alert(response.data.message, true);
                    }
                ).then(function(){
                    $scope.loading = false;
                });
            }
        };

        // Unstock idea
        $scope.unstock = function(id){
            var indexToRemove = null;
            for(var i = 0, l = $scope.ideas.length; i < l; i++){
                if( $scope.ideas[i].ID == id ){
                    indexToRemove = i;
                    break;
                }
            }
            if( null !== indexToRemove ){
                $scope.loading = true;
                Hametuha.confirm('このアイデアのストックを解除しますか？', function () {
                    $http({
                        method : 'DELETE',
                        url    : wpApiSettings.root + 'hametuha/v1/idea/' + id + '/',
                        headers: {
                            'X-WP-Nonce': wpApiSettings.nonce
                        }
                    }).then(
                        function (response) {
                            var idea = $scope.ideas[indexToRemove];
                            if( ! idea.own ){
                                $scope.ideas.splice(indexToRemove, 1);
                                $scope.ideasTotal--;
                                $scope.offset--;
                            }else{
                                $scope.ideas[i].location = 0;
                                $scope.ideas[i].stocking = false;
                            }
                        },
                        function (response) {
                            Hametuha.alert(response.data.message, true);
                        }
                    ).then(function(){
                        $scope.loading = false;
                    });
                });
            }
        };

        // Remove idea
        $scope.removeIdea = function (id) {
            Hametuha.confirm('このアイデアを削除してよろしいですか？', function () {
                var indexToRemove = null;

                for (var i = 0, l = $scope.ideas.length; i < l; i++) {
                    if ($scope.ideas[i].ID == id) {
                        indexToRemove = i;
                        break;
                    }
                }

                if (null !== indexToRemove) {
                    $scope.loading = true;
                    $http({
                        method : 'DELETE',
                        url    : endpoint + '?post_id=' + id,
                        headers: {
                            'X-WP-Nonce': wpApiSettings.nonce
                        }
                    }).then(
                        function (result) {
                            $scope.ideas.splice(indexToRemove, 1);
                            $scope.ideasTotal--;
                            $scope.offset--;
                        },
                        function (response) {
                            Hametuha.alert(response.data.message, true);
                        }
                    ).then(function(){
                        $scope.loading = false;
                    });
                }

            }, true);

        };

    }])
;
