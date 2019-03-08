'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('configurationCtrl', ['$scope', '$routeParams', '$route', '$filter', 'configurationFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $route, $filter, configurationFactory, functionsFactory, settingsFactory) {

        $scope.configuration = {};
        $scope.configuration_data = {};

        configurationFactory.get().success(function(data) {
            $scope.configuration_data = data;
            $scope.mode = "Edit";
        });

        $scope.base_configuration_logo = settingsFactory.getURL('base_configuration_logo');

        $scope.toggleDescriptionStatus = function (theConfiguration, forceUpdate) {
            theConfiguration.admin_id = $scope.admin.id;
            if (theConfiguration.description_status == 1) { theConfiguration.description_status = 0; } else { theConfiguration.description_status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                configurationFactory.update(theConfiguration)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.submitConfiguration = function(theConfiguration) {
            functionsFactory.clearError(angular.element('.configuration-frm'));
            theConfiguration.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                configurationFactory.update(theConfiguration)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                            $route.reload();
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.configuration-frm'));
                    });
            }
        }

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
