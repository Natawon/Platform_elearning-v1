'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('my_profileCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', 'my_profileFactory', 'admins_groupsFactory', 'functionsFactory', 'authenticationService', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, my_profileFactory, admins_groupsFactory, functionsFactory, authenticationService, settingsFactory) {

        $scope.admins_groups = {};
        $scope.my_profile_data = {}

        $scope.mode = "Edit";

        $scope.base_my_profile_avatar = settingsFactory.getURL('base_admins_avatar');

        $scope.hint = {
            'password': '<ul class="m-t-5 m-r-10 m-b-5 m-l-10 hint-list"> <li class="f-12">The password is at least 8 characters long.</li> <li class="f-12">The password is alphanumeric and contain both letters and numbers.</li> <li class="f-12">The password is a mix of uppercase and lowercase letters.</li> <li class="f-12">The password contains special characters such as #,$ etc.</li> <li class="f-12">The password should not contain contextual information such as login credentials, website name etc.</li> </ul>'
        };

        $scope.activations = [
            { "value": 0, "title": "ไม่ได้เปิดใช้งาน" },
            { "value": 1, "title": "เปิดใช้งาน" },
            { "value": 2, "title": "ระงับการใช้งาน" }
        ];

        $scope.toggleStatus = function(theMyProfile, forceUpdate) {
            theMyProfile.admin_id = $scope.admin.id;
            if (theMyProfile.status == 1) { theMyProfile.status = 0; } else { theMyProfile.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                my_profileFactory.update(theMyProfile)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
            }
        };

        my_profileFactory.get()
            .success(function(data) {
                $scope.my_profile_data = data;
            })
            .error(function() {
                notification("error", settingsFactory.getConstant('server_error'));
            });

        admins_groupsFactory.all().success(function(data) {
            $scope.admins_groups = data;
        })

        $scope.submitProfileAccess = function(theMyProfile, nextAction) {
            functionsFactory.clearError(angular.element('.my_profile-access-frm'));
            theMyProfile.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                my_profileFactory.changeAccess(theMyProfile)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                            authenticationService.updateUser().success(function() {
                                $timeout(function() {
                                    window.location.reload()
                                }, 500);
                            });
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.my_profile-access-frm'));
                    });
            }
        };

        $scope.submitMyProfile = function(theMyProfile, nextAction) {
            functionsFactory.clearError(angular.element('.my_profile-frm'));
            theMyProfile.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                my_profileFactory.update(theMyProfile)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                            authenticationService.updateUser().success(function() {
                                $timeout(function() {
                                    window.location.reload()
                                }, 500);
                            });
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.my_profile-frm'));
                    });
            }
        };

        //notification
        var notification = function (status,alert) {
            if(status == "success") {
                var n = noty({
                    text        : '<div class="alert alert-success"><p><strong> '+ alert +' </strong></p></div>',
                    layout      : 'topRight',
                    theme       : 'made',
                    maxVisible  : 10,
                    animation   : {
                        open  : 'animated bounceInRight',
                        close : 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else {
                var n = noty({
                    text        : '<div class="alert alert-danger"><p><strong> '+ alert +' </strong></p></div>',
                    layout      : 'topRight',
                    theme       : 'made',
                    maxVisible  : 10,
                    animation   : {
                        open  : 'animated bounceInRight',
                        close : 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            }
        }

    }]);
