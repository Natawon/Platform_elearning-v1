'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('sub_groupsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'sub_groupsFactory', 'groupsFactory', 'domainsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, sub_groupsFactory, groupsFactory, domainsFactory, functionsFactory, settingsFactory) {

        $scope.sub_groups = {};
        $scope.sub_groups_data = {
            "restriction_mode": "off",
            "domains": [
                {"title": "gmail.com"},
                {"title": "outlook.com"},
                {"title": "live.com"},
                {"title": "window.live"}
            ]
        };
        $scope.selected_groups = {};

        $scope.mode = "Create";

        $scope.filters = {
            // "search": "",
        };

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'title';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.sub_groups = resp.data;
            for(var i=0; i<$scope.sub_groups.length; i++) {
                var newSubGroupsModifyDatetime = new Date($scope.sub_groups[i].modify_datetime).toISOString();
                $scope.sub_groups[i].modify_datetime = $filter('date')(newSubGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var sub_groups_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = sub_groupsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theSubGroups, forceUpdate) {
            functionsFactory.clearError(angular.element('.sub_groups-frm'));
            theSubGroups.admin_id = $scope.admin.id;
            if (theSubGroups.status == 1) { theSubGroups.status = 0; } else { theSubGroups.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                sub_groupsFactory.update(theSubGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        // notification("error", settingsFactory.getConstant('server_error'));
                        functionsFactory.handleError(data, angular.element('.sub_groups-frm'));
                    });
            }
        };

        $scope.updateStatus = function(theSubGroups) {
            if (theSubGroups.status == 1) { theSubGroups.status = 0; } else { theSubGroups.status = 1; }
            sub_groupsFactory.updateStatus({'id': theSubGroups.id, 'status': theSubGroups.status})
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
                sub_groups_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            sub_groupsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    sub_groups_query($scope.current_page, $scope.per_page);
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

                sub_groupsFactory.sort(dataSort).success(function() {
                    notification("success", "The sub_groups has been sortable.");
                    sub_groups_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            sub_groups_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        sub_groups_query($scope.page, $scope.per_page);

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            sub_groups_query($scope.page, $scope.per_page);
        };

        $scope.clearFilters = function () {
            $('#btnFiltersClear').button('loading');
            // angular.element('.frm-filter')[0].reset();
            $scope.filters = {};
            $timeout(function() {
                angular.element('select#filter_groups_id').trigger('change');
            }, 10);
            // $scope.changeFilter(false);
        };

        if (!angular.isUndefined($routeParams.id)) {
            sub_groupsFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.sub_groups_data = data;
                $scope.mode = "Edit";
            })
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            $timeout(function() {
                $scope.$watch('sub_groups_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                        if ($scope.mode === "Create") {
                            if ($scope.selected_groups !== undefined) {
                                if ($scope.selected_groups.id == 4) {
                                    $scope.sub_groups_data.restriction_mode = "deny";
                                } else {
                                    $scope.sub_groups_data.restriction_mode = "off";
                                }
                            } else {
                                $scope.sub_groups_data.restriction_mode = "off";
                            }
                        }
                    }
                });
            }, 500);

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        });

        $scope.addDomain = function(theSubGroups) {

            $scope.sub_groups_data.domains.push({
                "sub_groups_id": theSubGroups.id
            });

            $timeout(function() {
                $(".domain_input:last-child").focus();
            }, 100);
        };

        $scope.deleteDomain = function(theDomain, theSubGroups) {
            if (theDomain.id === undefined) {
                var index = $scope.sub_groups_data.domains.indexOf(theDomain);
                $scope.sub_groups_data.domains.splice(index, 1);
            } else {
                var id = theDomain.id;
                var alert = confirm("Are you sure to delete domain " + theDomain.title + " ?");
                if(alert == true) {
                    domainsFactory.delete(theDomain)
                        .success(function(data) {
                            if(data.is_error == false){
                                $route.reload();
                            }
                            if(data.is_error == true){
                                notification("error",data.message);
                            }
                        })
                        .error(function() {
                            notification("error", settingsFactory.getConstant('server_error'));
                        });
                }
            }

        };

        $scope.submitSubGroups = function(theSubGroups, nextAction) {
            functionsFactory.clearError(angular.element('.sub_groups-frm'));
            theSubGroups.admin_id = $scope.admin.id;

            _.remove(theSubGroups.domains, function(n) {
                return n.title == undefined || n.title == "";
            });

            // console.log(theSubGroups.domains);
            // return false;

            if ($scope.mode == "Edit") {
                sub_groupsFactory.update(theSubGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('sub_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.sub_groups-frm'));
                    });
            }else{
                sub_groupsFactory.create(theSubGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('sub_groups/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('sub_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.sub_groups-frm'));
                    });
            }
        }

        $scope.deleteSubGroups = function(theSubGroups) {
            var id = theSubGroups.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                sub_groupsFactory.delete(theSubGroups).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
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
