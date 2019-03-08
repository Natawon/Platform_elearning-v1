'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('admins_groupsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', 'admins_groupsFactory', 'admins_menuFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, admins_groupsFactory, admins_menuFactory, functionsFactory, settingsFactory) {

        $scope.admins_groups = {};
        $scope.admins_groups_data = {};
        $scope.groups = [];
        $scope.admins_menu = [];

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
            $scope.admins_groups = resp.data;
            for(var i=0; i<$scope.admins_groups.length; i++) {
                var newAdminsGroupsModifyDatetime = new Date($scope.admins_groups[i].modify_datetime).toISOString();
                $scope.admins_groups[i].modify_datetime = $filter('date')(newAdminsGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var admins_groups_query = function(page, per_page) {
            var filters = angular.element('.frm-filter').serialize();
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = admins_groupsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleExternal = function(theAdminsGroups) {
            theAdminsGroups.admin_id = $scope.admin.id;
            if (theAdminsGroups.external == 1) { theAdminsGroups.external = 0; } else { theAdminsGroups.external = 1; }
            if ($scope.mode == "Edit") {
                admins_groupsFactory.update(theAdminsGroups)
                    .success(function(data) {
                        notification("success",data.message);
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleStatus = function(theAdminsGroups, forceUpdate) {
            theAdminsGroups.admin_id = $scope.admin.id;
            if (theAdminsGroups.status == 1) { theAdminsGroups.status = 0; } else { theAdminsGroups.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                if (forceUpdate === true) { theAdminsGroups._mode = 'list'; }
                admins_groupsFactory.update(theAdminsGroups)
                    .success(function(data) {
                        notification("success",data.message);
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theAdminsGroups) {
            if (theAdminsGroups.status == 1) { theAdminsGroups.status = 0; } else { theAdminsGroups.status = 1; }
            admins_groupsFactory.updateStatus({'id': theAdminsGroups.id, 'status': theAdminsGroups.status})
                .success(function(data) {
                    if (data.is_error == false) {
                        notification("success",data.message);
                    } else {
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                admins_groups_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        admins_groupsFactory.all_groups().success(function (data) {
            $scope.groups = data;
        });

        admins_menuFactory.all().success(function (data) {
            $scope.admins_menu = data;
        });

        $scope.sortOrder = function(theAdminsGroups) {
            admins_groupsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    admins_groups_query($scope.current_page, $scope.per_page);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
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

                admins_groupsFactory.sort(dataSort).success(function() {
                    notification("success", "The admin groups has been sortable.");
                    admins_groups_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            admins_groups_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        admins_groups_query($scope.page, $scope.per_page);

        $scope.changeFilter = function() {
            admins_groups_query($scope.page, $scope.per_page);
        };

        if (!angular.isUndefined($routeParams.id)) {
            admins_groupsFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.admins_groups_data = data;
                    $scope.mode = "Edit";

                    $scope.admins_groups_data.admin2group = new Array();
                    if (!angular.isUndefined($scope.admins_groups_data.groups) && $scope.admins_groups_data.groups.length != 0) {
                        for (var i = 0; i < $scope.admins_groups_data.groups.length; i++) {
                            $scope.admins_groups_data.admin2group.push($scope.admins_groups_data.groups[i].id);
                        }
                    }
                    $scope.admins_groups_data.admins_menu2admins_groups = new Array();
                    if (!angular.isUndefined($scope.admins_groups_data.admins_menu) && $scope.admins_groups_data.admins_menu.length != 0) {
                        for (var i = 0; i < $scope.admins_groups_data.admins_menu.length; i++) {
                            $scope.admins_groups_data.admins_menu2admins_groups.push($scope.admins_groups_data.admins_menu[i].id);
                        }
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        $scope.submitAdminsGroups = function(theAdminsGroups, nextAction) {
            functionsFactory.clearError(angular.element('.admins_groups-frm'));
            theAdminsGroups.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                admins_groupsFactory.update(theAdminsGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('admins_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.admins_groups-frm'));
                    });
            }else{
                admins_groupsFactory.create(theAdminsGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('admins_groups/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('admins_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.admins_groups-frm'));
                    });
            }
        }

        $scope.deleteAdminsGroups = function(theAdminsGroups) {
            var id = theAdminsGroups.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                admins_groupsFactory.delete(theAdminsGroups).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                }).error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
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
