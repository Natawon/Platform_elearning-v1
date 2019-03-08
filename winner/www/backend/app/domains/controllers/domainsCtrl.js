'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('instructorsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', 'instructorsFactory', 'settingsFactory', function ($scope, $routeParams, $location, $route, $filter, instructorsFactory, settingsFactory) {

        $scope.instructors = {};
        $scope.instructors_data = {};

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.instructors = resp.data;
            for(var i=0; i<$scope.instructors.length; i++) {
                var newInstructorsModifyDatetime = new Date($scope.instructors[i].modify_datetime).toISOString();
                $scope.instructors[i].modify_datetime = $filter('date')(newInstructorsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var instructors_query = function(page, per_page) {
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction;
            var query = instructorsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theInstructors) {
            theInstructors.admin_id = $scope.admin.id;
            if (theInstructors.status == 1) { theInstructors.status = 0; } else { theInstructors.status = 1; }
            instructorsFactory.update(theInstructors)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                });
        }

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                instructors_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            instructorsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    instructors_query($scope.current_page, $scope.per_page);
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

                instructorsFactory.sort(dataSort).success(function() {
                    notification("success", "The instructors has been sortable.");
                    instructors_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            instructors_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        instructors_query($scope.page, $scope.per_page);

        if (!angular.isUndefined($routeParams.id)) {
            instructorsFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.instructors_data = data;
                $scope.mode = "Edit";
            })
        }

        $scope.submitInstructors = function(theInstructors) {
            theInstructors.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                instructorsFactory.update(theInstructors)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error"," No Access-Control-Allow-Origin");
                    });
            }else{
                instructorsFactory.create(theInstructors)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                            $location.path('instructors');
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error"," No Access-Control-Allow-Origin");
                    });
            }
        }

        $scope.deleteInstructors = function(theInstructors) {
            var id = theInstructors.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                instructorsFactory.delete(theInstructors).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
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
