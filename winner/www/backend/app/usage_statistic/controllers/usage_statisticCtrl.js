'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('usage_statisticCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', 'usage_statisticFactory', 'coursesFactory', 'settingsFactory',
    function ($scope, $sce, $routeParams, $location, $route, $filter, $timeout, usage_statisticFactory, coursesFactory, settingsFactory) {

        $scope.usage_statistic = {};
        $scope.usage_statistic_data = {};
        $scope.selected_courses = {};

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'id';
        $scope.sorting_direction = 'desc';
        $scope.search = "";
        $scope.filterLoading = false;

        var filterTimeout;

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.filterLoading = false;
            $timeout(function() {
                angular.element('#search').focus();
                $scope.usage_statistic = resp.data;
                set_pagination(resp);
            }, 2000);
        };

        var usage_statistic_query = function (page, per_page, search) {
            $scope.filterLoading = true;
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction+"&search="+search;
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.usage_statistic(query_courses, query_string);
            } else {
                var query = usage_statisticFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                usage_statistic_query(new_page, $scope.per_page, $scope.search);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            usage_statisticFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    usage_statistic_query($scope.current_page, $scope.per_page, $scope.search);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                    $scope.enableSortable();
                });
        };

        $scope.sortableOptions = {
            stop: function(e, ui) {
                var $sorted = ui.item;

                var $prev = $sorted.prev();
                var $next = $sorted.next();

                var dataSort = {
                    id: $sorted.data('id')
                };

                if ($prev.length > 0) {
                    dataSort.type = 'moveAfter';
                    dataSort.positionEntityId = $prev.data('id');
                } else if ($next.length > 0) {
                    dataSort.type = 'moveBefore';
                    dataSort.positionEntityId = $next.data('id');
                } else {
                    notification("error"," Something wrong!");
                }

                usage_statisticFactory.sort(dataSort).success(function() {
                    notification("success", "The usage_statistic has been sortable.");
                    usage_statistic_query($scope.current_page, $scope.per_page, $scope.search);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            usage_statistic_query($scope.page, $scope.per_page, $scope.search);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        usage_statistic_query($scope.page, $scope.per_page, $scope.search);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
        })

        if (!angular.isUndefined($routeParams.id)) {
            usage_statisticFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.usage_statistic_data = data;
                $scope.mode = "Edit";
            })
        }

        $scope.changeFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/usage_statistic');
            } else {
                $location.path('usage_statistic');
            }
        }

        $scope.searchFilter = function() {
            if (filterTimeout) $timeout.cancel(filterTimeout);

            filterTimeout = $timeout(function() {
                usage_statistic_query($scope.page, $scope.per_page, $scope.search);
            }, 1000);
        }

        $scope.usage_statisticExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "usage_statistic/export?"+filters;
            window.open(url,"_blank");
        };


        //notification
        var notification = function (status, alert) {
            if (status == "success") {
                var n = noty({
                    text: '<div class="alert alert-success"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else {
                var n = noty({
                    text: '<div class="alert alert-danger"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            }
        }

    }]);
