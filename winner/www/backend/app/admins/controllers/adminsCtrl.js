'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('adminsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', 'adminsFactory', 'admins_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, adminsFactory, admins_groupsFactory, functionsFactory, settingsFactory) {

        $scope.admins = {};
        $scope.admins_groups = {};
        $scope.admins_data = {}

        $scope.mode = "Create";

        $scope.base_admins_avatar = settingsFactory.getURL('base_admins_avatar');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'id';
        $scope.sorting_direction = 'desc';
        $scope.keyword = "";

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
            $scope.admins = resp.data;
            for(var i=0; i<$scope.admins.length; i++) {
                var newAdminsLastLogin = new Date($scope.admins[i].last_login).toISOString();
                $scope.admins[i].last_login = $filter('date')(newAdminsLastLogin, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var admins_query = function(page, per_page) {
            var filters = angular.element('.frm-filter').serialize();
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = adminsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theAdmins, forceUpdate) {
            theAdmins.admin_id = $scope.admin.id;
            if (!angular.isUndefined(theAdmins.admins_groups_id) && !angular.isUndefined(theAdmins.admins_groups_id.id)) {
                theAdmins.admins_groups_id = theAdmins.admins_groups_id.id;
            }
            if (theAdmins.status == 1) { theAdmins.status = 0; } else { theAdmins.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                adminsFactory.update(theAdmins)
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

        $scope.updateStatus = function(theAdmins) {
            if (theAdmins.status == 1) { theAdmins.status = 0; } else { theAdmins.status = 1; }
            adminsFactory.updateStatus({'id': theAdmins.id, 'status': theAdmins.status})
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
                admins_query(new_page, $scope.per_page);
            }
        });

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            admins_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        admins_query($scope.page, $scope.per_page);

        $scope.changeFilter = function() {
            admins_query($scope.page, $scope.per_page);
        };

        if (!angular.isUndefined($routeParams.id)) {
            adminsFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.admins_data = data;
                    $scope.admins_data.admins_groups_id = {id:$scope.admins_data.admins_groups_id};
                    $scope.mode = "Edit";
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        admins_groupsFactory.all().success(function(data) {
            $scope.admins_groups = data;
        })

        $scope.submitAdmins = function(theAdmins, nextAction) {
            var admins_groups_id = theAdmins.admins_groups_id;

            functionsFactory.clearError(angular.element('.admins-frm'));

            theAdmins.admin_id = $scope.admin.id;
            if (!angular.isUndefined(theAdmins.admins_groups_id) && !angular.isUndefined(theAdmins.admins_groups_id.id)) {
                theAdmins.admins_groups_id = theAdmins.admins_groups_id.id;
            }
            if ($scope.mode == "Edit") {
                adminsFactory.update(theAdmins)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('admins'); break;
                            }
                        }
                        if(data.is_error == true){
                            theAdmins.admins_groups_id = admins_groups_id;
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        theAdmins.admins_groups_id = admins_groups_id;
                        functionsFactory.handleError(data, angular.element('.admins-frm'));
                    });
            }else{
                adminsFactory.create(theAdmins)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('admins/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('admins'); break;
                            }
                        }
                        if(data.is_error == true){
                            theAdmins.admins_groups_id = admins_groups_id;
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        theAdmins.admins_groups_id = admins_groups_id;
                        functionsFactory.handleError(data, angular.element('.admins-frm'));
                    });
            }
        }

        $scope.deleteAdmins = function(theAdmins) {
            var id = theAdmins.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                adminsFactory.delete(theAdmins)
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
