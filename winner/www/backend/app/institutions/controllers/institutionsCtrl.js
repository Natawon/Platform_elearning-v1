'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('institutionsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', 'institutionsFactory', 'groupsFactory', 'settingsFactory', function ($scope, $routeParams, $location, $route, $filter, institutionsFactory, groupsFactory, settingsFactory) {

        $scope.institutions = {};
        $scope.institutions_data = {};

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
            $scope.institutions = resp.data;
            for(var i=0; i<$scope.institutions.length; i++) {
                var newInstitutionsModifyDatetime = new Date($scope.institutions[i].modify_datetime).toISOString();
                $scope.institutions[i].modify_datetime = $filter('date')(newInstitutionsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var institutions_query = function(page, per_page) {
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction;
            var query = institutionsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theInstitutions) {
            theInstitutions.admin_id = $scope.admin.id;
            if (theInstitutions.status == 1) { theInstitutions.status = 0; } else { theInstitutions.status = 1; }
            institutionsFactory.update(theInstitutions)
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
                institutions_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            institutionsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    institutions_query($scope.current_page, $scope.per_page);
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

                institutionsFactory.sort(dataSort).success(function() {
                    notification("success", "The institutions has been sortable.");
                    institutions_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            institutions_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        institutions_query($scope.page, $scope.per_page);

        if (!angular.isUndefined($routeParams.id)) {
            institutionsFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.institutions_data = data;
                $scope.mode = "Edit";
            })
        }

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
        });

        $scope.submitInstitutions = function(theInstitutions) {
            theInstitutions.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                institutionsFactory.update(theInstitutions)
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
                institutionsFactory.create(theInstitutions)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                            $location.path('institutions');
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

        $scope.deleteInstitutions = function(theInstitutions) {
            var id = theInstitutions.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                institutionsFactory.delete(theInstitutions).success(function(data) {
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
