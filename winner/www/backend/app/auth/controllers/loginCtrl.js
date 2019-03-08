/**
 * Created by root on 17/7/2558.
 */
'use strict';

angular.module('newApp')
    .controller('loginCtrl', ['$scope', '$location', '$timeout', 'authenticationService', 'adminsFactory', 'functionsFactory', function ($scope, $location, $timeout, authenticationService, adminsFactory, functionsFactory) {

    authenticationService.getCsrf();

    $scope.credentials = { username:'', password:'', remember: true};

    $scope.login = function (isForce) {
        if (isForce !== undefined && isForce === true) {
            $scope.credentials.forceLogin = true;
        }

        authenticationService.login($scope.credentials).success(function(data) {
            switch (data.option) {
                case 'change-password':
                    functionsFactory.notification('info', 'รัหสผ่านของท่านหมดอายุ');
                    $timeout(function() {
                        initForm($('.form-change-password'));
                    }, 1000);
                break;

                case 'session-exists':
                    // functionsFactory.notification('info', 'รัหสผ่านของท่านหมดอายุ');
                    $timeout(function() {
                        initForm($('.form-session-exists'));
                    }, 1000);
                break;

                default:
                    authenticationService.cacheSession();
                    authenticationService.saveUser(data);
                    $timeout(function() {
                        window.location.href = "/backend";
                    }, 100);
                break;
            }
        }).error(function(data) {
            notification("error",data.message);
        });
    };

    $scope.changePassword = function (newPassword, confirmNewPassword, isSaveUser) {
        authenticationService.changePassword({'change_password': newPassword, 'confirm_change_password': confirmNewPassword}).success(function(data) {
            notification("success", data.message);

            $scope.credentials.password = newPassword;

            if (isSaveUser) {
                authenticationService.login($scope.credentials).success(function(data) {
                    authenticationService.cacheSession();
                    authenticationService.saveUser(data);
                });
            }

            $timeout(function() {
                window.location.href = "/backend";
            }, 2000);
        }).error(function(data) {
            notification("error",data.message);
        });
    }

    $scope.destroyUser = function () {
        $scope.login(true);
    };

    $scope.back = function () {
        authenticationService.forgetTempSession().success(function(data) {
            window.location.href = "/backend";
        }).error(function(data) {
            notification("error",data.message);
        });
    }

    var initForm = function($formToInit) {
        var $ele;

        if ($formToInit.closest('.account-wall').length) {
            $ele = $formToInit.closest('.account-wall');
        } else {
            $ele = $formToInit;
        }

        if (!$ele.is(':visible')) {
            $ele.siblings().addClass('hide');
            $timeout(function() {
                $ele.removeClass('hide').addClass('fadeInUp animated');
            }, 200);
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
