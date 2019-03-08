'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('members_pre_approvedCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'membersFactory', 'members_pre_approvedFactory', 'groupsFactory',  'sub_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, membersFactory, members_pre_approvedFactory, groupsFactory, sub_groupsFactory, functionsFactory, settingsFactory) {

        var timeoutCurrentPage;
        $scope.members_pre_approved = {};
        $scope.members_pre_approved_data = {};
        $scope.selected_groups = {};
        $scope.filters = {
            "search": "",
            "from_date": $filter('date')(new Date().toISOString(), 'MM/dd/yyyy'),
            "to_date": $filter('date')(new Date().toISOString(), 'MM/dd/yyyy'),
            "approved_type": null,
            "active": null
        };

        $scope.level_groups = [];

        // $scope.search = "";
        // $scope.from_date = $filter('date')(new Date().toISOString(), 'MM/dd/yyyy');
        // $scope.to_date = $filter('date')(new Date().toISOString(), 'MM/dd/yyyy');

        $scope.mode = "Create";

        $scope.defaultOptions = {
            "max_size": 5,
            "page": 1,
            "per_page": 10,
            "current_page": 1,
            "sorting_order": 'id',
            "sorting_direction": 'desc',
            "total": 0,
            "last_page": 0
        };

        $scope.last_7days = {};
        for (var i = 0; i < 7; i++) {
            var date_now = new Date();
            date_now.setDate(date_now.getDate() - [i]);
            $scope.last_7days[i] = $filter('date')(date_now.toISOString(), 'MM/dd/yyyy');
        }

        $scope.gender = [
            {'label': 'F', value:'F'},
            {'label': 'M', value:'M'}
        ];

        $scope.license_type = [
            {'label': 'ไม่มี', id:0},
            {'label': 'ผู้แนะนำการลงทุน IC', id:1},
            {'label': 'นักวิเคราะห์การลงทุน IA', id:2},
            {'label': 'รหัสพนักงาน', id:3}
        ];

        $scope.education_degree = [
            {'label': 'ต่ำกว่าปริญญาตรี', id:1},
            {'label': 'ปริญญาตรี', id:2},
            {'label': 'ปริญญาโท', id:3},
            {'label': 'ปริญญาเอก', id:4}
        ];

        $scope.activations = [
            { "value": 0, "title": "ไม่ได้เปิดใช้งาน" },
            { "value": 1, "title": "เปิดใช้งาน" },
            // { "value": 2, "title": "ถูกปิดการใช้งาน" }
        ];
        // $scope.members_pre_approved_data.active = 1;

        $scope.approved_types = [
            { "value": 1, "title": "Pre-Approved" },
            { "value": 2, "title": "Manual" },
            { "value": 3, "title": "Bypass Approval" },
            { "value": "waiting", "title": "Waiting" },
            { "value": "rejected", "title": "Rejected" },
            // { "value": 2, "title": "ถูกปิดการใช้งาน" }
        ];

        var set_pagination = function(pagination_data) {
            $scope.defaultOptions.total = pagination_data.total;
            $scope.defaultOptions.last_page = pagination_data.last_page;
            $scope.defaultOptions.current_page = pagination_data.current_page;
            $scope.defaultOptions.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.members_pre_approved = resp.data;
            for(var i=0; i<$scope.members_pre_approved.length; i++) {
                if($scope.members_pre_approved[i].last_login){
                    var newMembersPreApprovedLastLogin = new Date($scope.members_pre_approved[i].last_login).toISOString();
                    $scope.members_pre_approved[i].last_login = $filter('date')(newMembersPreApprovedLastLogin, 'dd MMM yyyy HH:mm:ss');
                }
                var newMembersPreApprovedCreateDatetime = new Date($scope.members_pre_approved[i].create_datetime).toISOString();
                $scope.members_pre_approved[i].create_datetime = $filter('date')(newMembersPreApprovedCreateDatetime, 'dd MMM yyyy HH:mm:ss');

                $scope.members_pre_approved[i].no = ((resp.total) - (resp.from + i)) + 1;
            }
            set_pagination(resp);
        };

        var members_pre_approved_query = function(page, per_page, from_date, to_date, search) {
            if(search){
                from_date = ''; $scope.filters.from_date = '';
                to_date = ''; $scope.filters.to_date = '';
            }
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptions.sorting_order+"&order_direction="+$scope.defaultOptions.sorting_direction+"&"+$httpParamSerializer($scope.filters);
            var query = members_pre_approvedFactory.query(query_string);
            query.success(success_callback);
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;

            $timeout(function() {
                $scope.$watch('members_pre_approved_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                        // console.log($scope.selected_groups);
                    }
                });
            }, 500);
        });

        $scope.toggleIsForeign = function(theMembersPreApproved) {
            if (theMembersPreApproved.is_foreign == 1) { theMembersPreApproved.is_foreign = 0; } else { theMembersPreApproved.is_foreign = 1; }
            if ($scope.mode == "Edit") {
                members_pre_approvedFactory.update(theMembersPreApproved)
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

        $scope.toggleStatus = function(theMembersPreApproved) {
            if (theMembersPreApproved.status == 1) { theMembersPreApproved.status = 0; } else { theMembersPreApproved.status = 1; }
            if ($scope.mode == "Edit") {
                members_pre_approvedFactory.update(theMembersPreApproved)
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
        }


        $scope.$watch('defaultOptions.current_page', function(new_page, old_page) {
            if (timeoutCurrentPage) {
                $timeout.cancel(timeoutCurrentPage);
            }

            timeoutCurrentPage = $timeout(function() {
                if (new_page != old_page) {
                    members_pre_approved_query(new_page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
                }
            }, 100);
        });

        $scope.$watch('members_pre_approved_data.sub_groups_id', function () {
            $scope.changeFilterSubGroups();
        });

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.defaultOptions.sorting_order == newSortingOrder) {
                $scope.defaultOptions.sorting_direction = ($scope.defaultOptions.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            members_pre_approved_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.defaultOptions.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        members_pre_approved_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);

        if (!angular.isUndefined($routeParams.id)) {
            members_pre_approvedFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.members_pre_approved_data = data;
                $scope.mode = "Edit";

                // $timeout(function() {
                //     $scope.$watch('members_pre_approved_data.groups_id', function (new_value, old_value) {
                //         if ($scope.groups !== undefined) {
                //             $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                //             console.log($scope.selected_groups);
                //         }
                //     });
                // }, 500);
            });
        }

        sub_groupsFactory.all().success(function (data) {
            $scope.sub_groups = data;
        });

        $scope.changeFilter = function() {
            members_pre_approved_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
        }

        $scope.changeLast7day = function(last_7day) {
            $scope.filters.search = "";
            $scope.filters.from_date = last_7day;
            $scope.filters.to_date = last_7day;
            members_pre_approved_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, last_7day, last_7day, $scope.filters.search);
        }

        $scope.changeFilterSubGroups = function () {
            $scope.level_groups = [];
            if (!angular.isUndefined($scope.members_pre_approved_data.sub_groups_id)) {
                sub_groupsFactory.level_groups({id:$scope.members_pre_approved_data.sub_groups_id}).success(function(data) {
                    for (var i = 0; i < data.owner.length; i++) {
                        $scope.level_groups.push({id: data.owner[i].id, title: data.owner[i].title});
                    }
                    for (var i = 0; i < data.access.length; i++) {
                        $scope.level_groups.push({id: data.access[i].id, title: data.access[i].title});
                    }
                });
            }
        }

        if (!angular.isUndefined($routeParams.id)) {
            members_pre_approvedFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.members_pre_approved_data = data;
                $scope.mode = "Edit";

                $scope.members_pre_approved_data.member2level_group = new Array();
                if (!angular.isUndefined($scope.members_pre_approved_data.level_groups) && $scope.members_pre_approved_data.level_groups.length != 0) {
                    for (var i = 0; i < $scope.members_pre_approved_data.level_groups.length; i++) {
                        $scope.members_pre_approved_data.member2level_group.push($scope.members_pre_approved_data.level_groups[i].id);
                    }
                }

            })
        }

        $scope.submitMembersPreApproved = function(theMembersPreApproved, nextAction) {
            functionsFactory.clearError(angular.element('.members_pre_approved-frm'));

            switch (theMembersPreApproved.groups_id) {
                case 1:
                    theMembersPreApproved.position_id = null;
                    theMembersPreApproved.department = null;
                    theMembersPreApproved.role = null;
                    theMembersPreApproved.institution_id = null;
                    theMembersPreApproved.license_type_id = null;
                    theMembersPreApproved.license_id = null;
                    theMembersPreApproved.occupation_id = null;
                    theMembersPreApproved.education_level_id = null;
                    theMembersPreApproved.table_number = null;
                    theMembersPreApproved.chief_name = null;
                break;

                case 2:
                    theMembersPreApproved.license_type_id = null;
                    theMembersPreApproved.license_id = null;
                    theMembersPreApproved.education_degree_id = null;
                    theMembersPreApproved.occupation_id = null;
                    theMembersPreApproved.education_level_id = null;
                    theMembersPreApproved.table_number = null;
                    theMembersPreApproved.chief_name = null;
                break;

                case 3:
                    theMembersPreApproved.role = null;
                    // theMembersPreApproved.education_level_id = null;
                    theMembersPreApproved.table_number = null;
                    theMembersPreApproved.chief_name = null;
                break;

                case 4:
                    theMembersPreApproved.position_id = null;
                    theMembersPreApproved.department = null;
                    theMembersPreApproved.role = null;
                    theMembersPreApproved.license_type_id = null;
                    theMembersPreApproved.license_id = null;
                    theMembersPreApproved.education_degree_id = null;
                    theMembersPreApproved.table_number = null;
                    theMembersPreApproved.chief_name = null;
                break;

                case 5:
                    theMembersPreApproved.role = null;
                    theMembersPreApproved.institution_id = null;
                    theMembersPreApproved.license_type_id = null;
                    theMembersPreApproved.license_id = null;
                    theMembersPreApproved.education_degree_id = null;
                    theMembersPreApproved.education_level_id = null;
                break;

                default:
                break;
            }

            if ($scope.mode == "Edit") {
                members_pre_approvedFactory.update(theMembersPreApproved)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('members'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.members_pre_approved-frm'));
                    });
            }else{
                members_pre_approvedFactory.create(theMembersPreApproved)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('members_pre_approved/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('members'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.members_pre_approved-frm'));
                    });
            }
        }

        $scope.deleteMembersPreApproved = function(theMembersPreApproved) {
            var id = theMembersPreApproved.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                members_pre_approvedFactory.delete(theMembersPreApproved).success(function(data) {
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
