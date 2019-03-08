'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('dashboardCtrl', ['$scope', 'dashboardFactory', 'pluginsService', function ($scope, dashboardFactory, pluginsService) {
        $scope.$on('$viewContentLoaded', function () {
            // dashboardFactory.init();
            // pluginsService.init();
            // dashboardFactory.setHeights()
            // if ($('.widget-weather').length) {
            //     widgetWeather();
            // }
            // handleTodoList();
        });

        $scope.activeTab = true;

    }]);
