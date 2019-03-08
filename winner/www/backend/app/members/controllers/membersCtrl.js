'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('membersCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'pluginsService', 'membersFactory', 'members_pre_approvedFactory', 'groupsFactory',  'sub_groupsFactory', 'admins_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, pluginsService, membersFactory, members_pre_approvedFactory, groupsFactory, sub_groupsFactory, admins_groupsFactory, functionsFactory, settingsFactory) {

        var timeoutCurrentPage;
        $scope.members = {};
        $scope.members_data = {};
        $scope.selected_groups = {};
        $scope.filters = {
            "search": "",
            "from_date": $filter('date')(new Date().toISOString(), 'yyyy-MM-dd'),
            "to_date": $filter('date')(new Date().toISOString(), 'yyyy-MM-dd'),
            "approved_type": null,
            "active": null
        };

        $scope.hint = {
            'password': '<ul class="m-t-5 m-r-10 m-b-5 m-l-10 hint-list"> <li class="f-12">The password is at least 8 characters long.</li> <li class="f-12">The password is alphanumeric and contain both letters and numbers.</li> <li class="f-12">The password is a mix of uppercase and lowercase letters.</li> <li class="f-12">The password contains special characters such as #,$ etc.</li> <li class="f-12">The password should not contain contextual information such as login credentials, website name etc.</li> </ul>'
        };

        $scope.members_pre_approved_ = {};
        $scope.filtersPreApproved = angular.copy($scope.filters);

        $scope.level_groups = [];

        // $scope.search = "";
        // $scope.from_date = $filter('date')(new Date().toISOString(), 'yyyy-MM-dd');
        // $scope.to_date = $filter('date')(new Date().toISOString(), 'yyyy-MM-dd');

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
        $scope.defaultOptionsPreApproved = angular.copy($scope.defaultOptions);

        $scope.last_7days = {};
        $scope.last_7days_pre_approved = {};
        for (var i = 0; i < 7; i++) {
            var date_now = new Date();
            date_now.setDate(date_now.getDate() - [i]);
            $scope.last_7days[i] = $filter('date')(date_now.toISOString(), 'yyyy-MM-dd');
            $scope.last_7days_pre_approved[i] = $filter('date')(date_now.toISOString(), 'yyyy-MM-dd');
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
            { "value": 2, "title": "ระงับการใช้งาน" }
        ];
        // $scope.members_data.active = 1;

        $scope.approved_types = [
            { "value": 1, "title": "Pre-Approved" },
            { "value": 2, "title": "Manual" },
            { "value": 3, "title": "Bypass Approval" },
            { "value": "waiting", "title": "Waiting" },
            { "value": "rejected", "title": "Rejected" }
        ];

        $scope.branch_tax_invoices = [
            { "value": 0, "title": "สำนักงานใหญ่" },
            { "value": 1, "title": "สาขา" },
        ];

        ///Add on datepicker dateFormat
        setTimeout(function() {
            $("#members_start_date, #members_pre_approved_start_date").datepicker({
                dateFormat: "yy-mm-dd",
                // timeFormat: "HH:mm:ss",
                onSelect: function (date) {
                    $scope.filters.from_date = date;
                    $scope.filtersPreApproved.from_date = date;
                    if ($(this).attr('id') === "members_pre_approved_start_date") {
                        $scope.changeFilterPreApproved();
                    } else {
                        $scope.changeFilter();
                    }
                }
            });
            $("#members_to_date, #members_pre_approved_to_date").datepicker({
                dateFormat: "yy-mm-dd",
                // timeFormat: "HH:mm:ss",
                onSelect: function (date) {
                    $scope.filters.to_date = date;
                    $scope.filtersPreApproved.to_date = date;
                    if ($(this).attr('id') === "members_pre_approved_to_date") {
                        $scope.changeFilterPreApproved();
                    } else {
                        $scope.changeFilter();
                    }
                }
            });

            $('#expire').datetimepicker({
                prevText: '<i class="fa fa-angle-left"></i>',
                nextText: '<i class="fa fa-angle-right"></i>',
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                controlType: 'select',
                oneLine: true,
                timeInput: true,
                stepMinute: 5,

            });

            pluginsService.popover();
        }, 2000);

        var set_pagination = function(pagination_data) {
            $scope.defaultOptions.total = pagination_data.total;
            $scope.defaultOptions.last_page = pagination_data.last_page;
            $scope.defaultOptions.current_page = pagination_data.current_page;
            $scope.defaultOptions.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.members = resp.data;
            for(var i=0; i<$scope.members.length; i++) {
                if($scope.members[i].last_login){
                    var newMembersLastLogin = new Date($scope.members[i].last_login).toISOString();
                    $scope.members[i].last_login = $filter('date')(newMembersLastLogin, 'dd MMM yyyy HH:mm:ss');
                }
                var newMembersCreateDatetime = new Date($scope.members[i].create_datetime).toISOString();
                $scope.members[i].create_datetime = $filter('date')(newMembersCreateDatetime, 'dd MMM yyyy HH:mm:ss');

                $scope.members[i].no = ((resp.total) - (resp.from + i)) + 1;
            }
            set_pagination(resp);
        };

        var members_query = function(page, per_page, from_date, to_date, search) {
            if(search){
                from_date = ''; $scope.filters.from_date = '';
                to_date = ''; $scope.filters.to_date = '';
            }
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptions.sorting_order+"&order_direction="+$scope.defaultOptions.sorting_direction+"&"+$httpParamSerializer($scope.filters);
            var query = membersFactory.query(query_string);
            query.success(success_callback);
        };

        var set_pagination_pre_approved = function(pagination_data) {
            $scope.defaultOptionsPreApproved.total = pagination_data.total;
            $scope.defaultOptionsPreApproved.last_page = pagination_data.last_page;
            $scope.defaultOptionsPreApproved.current_page = pagination_data.current_page;
            $scope.defaultOptionsPreApproved.per_page = pagination_data.per_page;
        };

        var success_callback_pre_approved = function(resp) {
            $scope.members_pre_approved = resp.data;
            for(var i=0; i<$scope.members_pre_approved.length; i++) {
                if($scope.members_pre_approved[i].last_login){
                    var newMembersLastLogin = new Date($scope.members_pre_approved[i].last_login).toISOString();
                    $scope.members_pre_approved[i].last_login = $filter('date')(newMembersLastLogin, 'dd MMM yyyy HH:mm:ss');
                }
                var newMembersCreateDatetime = new Date($scope.members_pre_approved[i].create_datetime).toISOString();
                $scope.members_pre_approved[i].create_datetime = $filter('date')(newMembersCreateDatetime, 'dd MMM yyyy HH:mm:ss');

                $scope.members_pre_approved[i].no = ((resp.total) - (resp.from + i)) + 1;
            }
            set_pagination_pre_approved(resp);
        };

        var members_pre_approved_query = function(page, per_page, from_date, to_date, search) {
            if(search){
                from_date = ''; $scope.filtersPreApproved.from_date = '';
                to_date = ''; $scope.filtersPreApproved.to_date = '';
            }
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsPreApproved.sorting_order+"&order_direction="+$scope.defaultOptionsPreApproved.sorting_direction+"&"+$httpParamSerializer($scope.filtersPreApproved);
            var query = members_pre_approvedFactory.query(query_string);
            query.success(success_callback_pre_approved);
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            $timeout(function() {
                $scope.$watch('members_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                        // console.log($scope.selected_groups);
                    }
                });
            }, 500);

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
                $scope.filtersPreApproved.groups_id = $scope.groups_list[0].id;
            }
        });

        $scope.toggleStatus = function(theMembers, forceUpdate) {
            functionsFactory.clearError(angular.element('.members-frm'));
            if (theMembers.status == 1) { theMembers.status = 0; } else { theMembers.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                membersFactory.update(theMembers)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        if (forceUpdate === true) {
                            notification("error", settingsFactory.getConstant('server_error'));
                        } else {
                            functionsFactory.handleError(data, angular.element('.members-frm'));
                        }
                    });
            }
        };

        $scope.updateStatus = function(theMembers) {
            if (theMembers.status == 1) { theMembers.status = 0; } else { theMembers.status = 1; }
            membersFactory.updateStatus({'id': theMembers.id, 'status': theMembers.status})
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

        $scope.toggleIsForeign = function(theMembers) {
            functionsFactory.clearError(angular.element('.members-frm'));
            if (theMembers.is_foreign == 1) { theMembers.is_foreign = 0; } else { theMembers.is_foreign = 1; }
            if ($scope.mode == "Edit") {
                membersFactory.update(theMembers)
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
                        functionsFactory.handleError(data, angular.element('.members-frm'));
                    });
            }
        }

        $scope.$watch('defaultOptions.current_page', function(new_page, old_page) {
            if (timeoutCurrentPage) {
                $timeout.cancel(timeoutCurrentPage);
            }

            timeoutCurrentPage = $timeout(function() {
                if (new_page != old_page) {
                    members_query(new_page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
                }
            }, 100);
        });

        $scope.$watch('defaultOptionsPreApproved.current_page', function(new_page, old_page) {
            if (timeoutCurrentPage) {
                $timeout.cancel(timeoutCurrentPage);
            }

            timeoutCurrentPage = $timeout(function() {
                if (new_page != old_page) {
                    members_pre_approved_query(new_page, $scope.defaultOptionsPreApproved.per_page, $scope.filtersPreApproved.from_date, $scope.filtersPreApproved.to_date, $scope.filtersPreApproved.search);
                }
            }, 100);
        });

        $scope.$watch('members_data.sub_groups_id', function () {
            $scope.changeFilterSubGroups();
        });

        $scope.$watch('members_data.inv_corporate_branch', function (new_value, old_value) {
            if (old_value != undefined && new_value != undefined) {
                if (new_value == 0) {
                    $scope.members_data.inv_corporate_branch_no = '00000';
                } else {
                    $scope.members_data.inv_corporate_branch_no = '';
                }
            }
        });

        $scope.setPadBranchNo = function() {
            if (!isNaN($scope.members_data.inv_corporate_branch_no)) {
                $scope.members_data.inv_corporate_branch_no = functionsFactory.str_pad($scope.members_data.inv_corporate_branch_no, 5, "0", "STR_PAD_LEFT");
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.defaultOptions.sorting_order == newSortingOrder) {
                $scope.defaultOptions.sorting_direction = ($scope.defaultOptions.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            members_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.defaultOptions.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        $scope.sort_by_pre_approved = function(newSortingOrder) {
            if ($scope.defaultOptionsPreApproved.sorting_order == newSortingOrder) {
                $scope.defaultOptionsPreApproved.sorting_direction = ($scope.defaultOptionsPreApproved.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            members_pre_approved_query($scope.defaultOptionsPreApproved.page, $scope.defaultOptionsPreApproved.per_page, $scope.filtersPreApproved.from_date, $scope.filtersPreApproved.to_date, $scope.filtersPreApproved.search);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.defaultOptionsPreApproved.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        members_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
        members_pre_approved_query($scope.defaultOptionsPreApproved.page, $scope.defaultOptionsPreApproved.per_page, $scope.filtersPreApproved.from_date, $scope.filtersPreApproved.to_date, $scope.filtersPreApproved.search);

        if (!angular.isUndefined($routeParams.id)) {
            membersFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.members_data = data;
                $scope.mode = "Edit";
            });
        } else {
            if ($scope.admin.groups_id != null) {
                $scope.members_data.groups_id = $scope.admin.groups_id;
            } else {
                admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                    if (data.groups.length == 1) {
                        $scope.members_data.groups_id = data.groups[0].id;
                    }
                });
            }
        }

        $scope.changeFilterSubGroups = function () {
            $scope.level_groups = [];
            if (!angular.isUndefined($scope.members_data.sub_groups_id)) {
                sub_groupsFactory.level_groups({id:$scope.members_data.sub_groups_id}).success(function(data) {
                    for (var i = 0; i < data.owner.length; i++) {
                        $scope.level_groups.push({id: data.owner[i].id, title: data.owner[i].title});
                    }
                    for (var i = 0; i < data.access.length; i++) {
                        $scope.level_groups.push({id: data.access[i].id, title: data.access[i].title});
                    }
                });
            }
        }

        sub_groupsFactory.all().success(function (data) {
            $scope.sub_groups = data;
        });

        $scope.changeFilter = function() {
            members_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, $scope.filters.from_date, $scope.filters.to_date, $scope.filters.search);
        }

        $scope.changeFilterPreApproved = function() {
            members_pre_approved_query($scope.defaultOptionsPreApproved.page, $scope.defaultOptionsPreApproved.per_page, $scope.filtersPreApproved.from_date, $scope.filtersPreApproved.to_date, $scope.filtersPreApproved.search);
        }

        $scope.changeLast7day = function(last_7day) {
            $scope.filters.search = "";
            $scope.filters.from_date = last_7day;
            $scope.filters.to_date = last_7day;
            members_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, last_7day, last_7day, $scope.filters.search);
        }

        $scope.changeLast7dayPreApproved = function(last_7day_pre_approved) {
            $scope.filtersPreApproved.search = "";
            $scope.filtersPreApproved.from_date = last_7day_pre_approved;
            $scope.filtersPreApproved.to_date = last_7day_pre_approved;
            members_pre_approved_query($scope.defaultOptionsPreApproved.page, $scope.defaultOptionsPreApproved.per_page, last_7day_pre_approved, last_7day_pre_approved, $scope.filtersPreApproved.search);
        }

        if (!angular.isUndefined($routeParams.id)) {
            membersFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.members_data = data;
                $scope.mode = "Edit";

                $scope.members_data.member2level_group = new Array();
                if (!angular.isUndefined($scope.members_data.level_groups) && $scope.members_data.level_groups.length != 0) {
                    for (var i = 0; i < $scope.members_data.level_groups.length; i++) {
                        $scope.members_data.member2level_group.push($scope.members_data.level_groups[i].id);
                    }
                }

            })
        }

        // Approve Member
        $scope.approveMember = function($event, theMembers, isReload) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.approve(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    if (isReload == true) {
                        $route.reload();
                    } else {
                        $scope.changeFilter();
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    if (isReload == true) {
                        $route.reload();
                    } else {
                        $scope.changeFilter();
                    }
                });
        };

        // Reject Member
        $scope.rejectMember = function($event, theMembers, isReload) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.reject(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    if (isReload == true) {
                        $route.reload();
                    } else {
                        $scope.changeFilter();
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    if (isReload == true) {
                        $route.reload();
                    } else {
                        $scope.changeFilter();
                    }
                });
        };

        $scope.submitMembers = function(theMembers, nextAction) {
            var $buttonSubmit = angular.element('button[type=submit]').button('loading');

            functionsFactory.clearError(angular.element('.members-frm'));

            if ($scope.selected_groups !== undefined && $scope.selected_groups.internal != 1) {
                delete theMembers.password;
            }

            switch (theMembers.groups_id) {
                case 1:
                    theMembers.position_id = null;
                    theMembers.department = null;
                    theMembers.role = null;
                    theMembers.institution_id = null;
                    theMembers.license_type_id = null;
                    theMembers.license_id = null;
                    theMembers.occupation_id = null;
                    theMembers.education_level_id = null;
                    theMembers.table_number = null;
                    theMembers.chief_name = null;
                break;

                case 2:
                    theMembers.license_type_id = null;
                    theMembers.license_id = null;
                    theMembers.education_degree_id = null;
                    theMembers.occupation_id = null;
                    theMembers.education_level_id = null;
                    theMembers.table_number = null;
                    theMembers.chief_name = null;
                break;

                case 3:
                    theMembers.role = null;
                    // theMembers.education_level_id = null;
                    theMembers.table_number = null;
                    theMembers.chief_name = null;
                break;

                case 4:
                    theMembers.position_id = null;
                    theMembers.department = null;
                    theMembers.role = null;
                    theMembers.license_type_id = null;
                    theMembers.license_id = null;
                    theMembers.education_degree_id = null;
                    theMembers.table_number = null;
                    theMembers.chief_name = null;
                break;

                case 5:
                    theMembers.role = null;
                    theMembers.institution_id = null;
                    theMembers.license_type_id = null;
                    theMembers.license_id = null;
                    theMembers.education_degree_id = null;
                    theMembers.education_level_id = null;
                break;

                default:
                break;
            }

            if ($scope.mode == "Edit") {
                membersFactory.update(theMembers)
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
                        $buttonSubmit.button('reset');
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.members-frm'));
                        $buttonSubmit.button('reset');
                    });
            }else{
                membersFactory.create(theMembers)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('members/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('members'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                        $buttonSubmit.button('reset');
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.members-frm'));
                        $buttonSubmit.button('reset');
                    });
            }
        }

        $scope.deleteMembers = function(theMembers) {
            var id = theMembers.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                membersFactory.delete(theMembers).success(function(data) {
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
