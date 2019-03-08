'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */

angular.module('newApp')
    .controller('instructorsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'instructorsFactory', 'groupsFactory', 'admins_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, instructorsFactory, groupsFactory, admins_groupsFactory, functionsFactory, settingsFactory) {

        $scope.instructors = {};
        $scope.instructors_data = {};
        $scope.groups = [];
        $scope.sub_groups = [];

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.filters = {
            // "search": "",
        };

        $scope.base_instructors_pdf = settingsFactory.getURL('base_instructors_pdf');

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

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var instructors_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = instructorsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theInstructors, forceUpdate) {
            theInstructors.admin_id = $scope.admin.id;
            if (theInstructors.status == 1) { theInstructors.status = 0; } else { theInstructors.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
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
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theInstructors) {
            if (theInstructors.status == 1) { theInstructors.status = 0; } else { theInstructors.status = 1; }
            instructorsFactory.updateStatus({'id': theInstructors.id, 'status': theInstructors.status})
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
                instructors_query(new_page, $scope.per_page);
            }
        });

        $scope.$watch('instructors_data.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.changeFilterGroups();
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

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            instructors_query($scope.page, $scope.per_page);
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

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        })

        if (!angular.isUndefined($routeParams.id)) {
            instructorsFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.instructors_data = data;
                $scope.mode = "Edit";

                $scope.instructors_data.instructor2group = new Array();
                if (!angular.isUndefined($scope.instructors_data.groups) && $scope.instructors_data.groups.length != 0) {
                    for (var i = 0; i < $scope.instructors_data.groups.length; i++) {
                        $scope.instructors_data.instructor2group.push($scope.instructors_data.groups[i].id);
                    }
                }

            })
        } else {
            if ($scope.admin.groups_id != null) {
                $scope.instructors_data.groups_id = $scope.admin.groups_id;
            } else {
                admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                    if (data.groups.length == 1) {
                        $scope.instructors_data.groups_id = data.groups[0].id;
                    }
                });
            }
        }

        $scope.changeFilterGroups = function () {
            if (!angular.isUndefined($scope.instructors_data.groups_id)) {
                $scope.sub_groups = [];
                groupsFactory.sub_groups({id:$scope.instructors_data.groups_id}).success(function(data) {
                    $scope.sub_groups = data;
                    if ($scope.sub_groups.length == 1) {
                        $scope.instructors_data.sub_groups_id = $scope.sub_groups[0].id;
                        $timeout(function() {
                            angular.element('select#sub_groups_id').trigger('change');
                        }, 10);
                    }
                });
            }
        }

        $scope.submitInstructors = function(theInstructors, nextAction) {
            functionsFactory.clearError(angular.element('.instructors-frm'));
            theInstructors.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                instructorsFactory.update(theInstructors)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('instructors'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.instructors-frm'));
                    });
            }else{
                instructorsFactory.create(theInstructors)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('instructors/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('instructors'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.instructors-frm'));
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
