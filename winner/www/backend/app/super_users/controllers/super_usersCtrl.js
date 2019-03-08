'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('super_usersCtrl', ['$scope', '$routeParams', '$location',  '$route', '$filter', '$timeout', '$httpParamSerializer', 'pluginsService', 'super_usersFactory', 'level_groupsFactory', 'groupsFactory', 'admins_groupsFactory', 'sub_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, pluginsService, super_usersFactory, level_groupsFactory, groupsFactory, admins_groupsFactory, sub_groupsFactory, functionsFactory, settingsFactory) {

        $scope.super_users = {};
        $scope.super_users_data = {}
        $scope.level_groups = [];
        $scope.selected_groups = {};

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'id';
        $scope.sorting_direction = 'desc';
        $scope.keyword = "";

        $scope.filters = {
            // "search": "",
        };

        $scope.hint = {
            'password': '<ul class="m-t-5 m-r-10 m-b-5 m-l-10 hint-list"> <li class="f-12">The password is at least 8 characters long.</li> <li class="f-12">The password is alphanumeric and contain both letters and numbers.</li> <li class="f-12">The password is a mix of uppercase and lowercase letters.</li> <li class="f-12">The password contains special characters such as #,$ etc.</li> <li class="f-12">The password should not contain contextual information such as login credentials, website name etc.</li> </ul>'
        };

        $scope.activations = [
            { "value": 0, "title": "ไม่ได้เปิดใช้งาน" },
            { "value": 1, "title": "เปิดใช้งาน" },
            { "value": 2, "title": "ระงับการใช้งาน" }
        ];

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.super_users = resp.data;
            for(var i=0; i<$scope.super_users.length; i++) {
                var newSuperUsersLastLogin = new Date($scope.super_users[i].last_login).toISOString();
                $scope.super_users[i].last_login = $filter('date')(newSuperUsersLastLogin, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var super_users_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = super_usersFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleUploadStatus = function(theSuperUsers) {
            theSuperUsers.admin_id = $scope.admin.id;
            if (theSuperUsers.upload_status == 1) { theSuperUsers.upload_status = 0; } else { theSuperUsers.upload_status = 1; }
            if ($scope.mode == "Edit") {
                super_usersFactory.update(theSuperUsers)
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

        $scope.toggleStatus = function(theSuperUsers, forceUpdate) {
            theSuperUsers.admin_id = $scope.admin.id;
            if (theSuperUsers.status == 1) { theSuperUsers.status = 0; } else { theSuperUsers.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                super_usersFactory.update(theSuperUsers)
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

        $scope.updateStatus = function(theSuperUsers) {
            if (theSuperUsers.status == 1) { theSuperUsers.status = 0; } else { theSuperUsers.status = 1; }
            super_usersFactory.updateStatus({'id': theSuperUsers.id, 'status': theSuperUsers.status})
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
                super_users_query(new_page, $scope.per_page);
            }
        });

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            super_users_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        super_users_query($scope.page, $scope.per_page);

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            super_users_query($scope.page, $scope.per_page);
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
            super_usersFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.super_users_data = data;
                    $scope.mode = "Edit";

                    $scope.super_users_data.admin2level_group = new Array();
                    if (!angular.isUndefined($scope.super_users_data.level_group) && $scope.super_users_data.level_group.length != 0) {
                        for (var i = 0; i < $scope.super_users_data.level_group.length; i++) {
                            $scope.super_users_data.admin2level_group.push($scope.super_users_data.level_group[i].id);
                        }
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        admins_groupsFactory.super_user_all().success(function(data) {
            $scope.admins_groups = data;
        });

        groupsFactory.all().success(function(data) {
            $scope.groups = data;
            $scope.groups_list = data;

            $timeout(function() {
                $scope.$watch('super_users_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                    }
                });
            }, 500);

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        });

        $scope.changeFilterGroups = function () {
            if (!angular.isUndefined($scope.super_users_data.groups_id)) {
                $scope.sub_groups = [];
                groupsFactory.sub_groups({id:$scope.super_users_data.groups_id}).success(function(data) {
                    $scope.sub_groups = data;
                    if ($scope.sub_groups.length == 1) {
                        $scope.super_users_data.sub_groups_id = $scope.sub_groups[0].id;
                        $timeout(function() {
                            angular.element('select#sub_groups_id').trigger('change');
                        }, 10);
                    }
                });
            }
        }

        $scope.$watch('super_users_data.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.changeFilterGroups();
        });

        $scope.filtersChangeSubGroups = function () {
            $scope.sub_groups_list = [];
            if (!angular.isUndefined($scope.filters.groups_id) && $scope.filters.groups_id !== null) {
                groupsFactory.sub_groups({id:$scope.filters.groups_id}).success(function(data) {
                    $scope.sub_groups_list = data;
                    if ($scope.sub_groups_list.length == 1) {
                        $scope.filters.sub_groups_id = $scope.sub_groups_list[0].id;
                        $timeout(function() {
                            angular.element('select#filter_sub_groups_id').trigger('change');
                            $scope.changeFilter();
                        }, 10);
                    }
                });
            }
        };

        $scope.$watch('filters.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#filter_sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.filtersChangeSubGroups();
        });

        if (!angular.isUndefined($routeParams.id)) {
            var query_string = "&admins_id="+ $routeParams.id;
            level_groupsFactory.sub_groups(query_string).success(function (data) {
                $scope.level_groups = data;
            });
        };

        $scope.submitSuperUsers = function(theSuperUsers, nextAction) {
            functionsFactory.clearError(angular.element('.super_users-frm'));
            theSuperUsers.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                super_usersFactory.update(theSuperUsers)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('super_users'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.super_users-frm'));
                    });
            }else{
                super_usersFactory.create(theSuperUsers)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('super_users/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('super_users'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.super_users-frm'));
                    });
            }
        }

        $scope.deleteSuperUsers = function(theSuperUsers) {
            var id = theSuperUsers.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                super_usersFactory.delete(theSuperUsers)
                    .success(function(data) {
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

        // Init Plugins
        $timeout(function() {
            pluginsService.popover();
        }, 2000);

        $timeout(function() {
            $('input[type="password"]').prop('readonly', false);
        }, 1000);

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
