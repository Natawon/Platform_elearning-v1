'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('level_groupsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'level_groupsFactory', 'membersFactory', 'members_pre_approvedFactory', 'groupsFactory', 'admins_groupsFactory', 'pluginsService', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, level_groupsFactory, membersFactory, members_pre_approvedFactory, groupsFactory, admins_groupsFactory, pluginsService, functionsFactory, settingsFactory) {

        $scope._isArray = angular.isArray;

        $scope.level_groups = {};
        $scope.level_groups_data = {};
        $scope.access_groups = {};
        $scope.waiting_groups = {};
        $scope.sub_groups = [];

        $scope.selected_groups = {};

        $scope.mode = "Create";

        $scope.defaultOptions = {
            "max_size": 5,
            "page": 1,
            "per_page": 10,
            "current_page": 1,
            "sorting_order": 'order',
            "sorting_direction": 'asc',
            "total": 0,
            "last_page": 0
        };

        $scope.defaultOptionsAccessGroups = angular.copy($scope.defaultOptions);
        $scope.defaultOptionsWaitingGroups = angular.copy($scope.defaultOptions);

        $scope.uploadMembersOptions = {
            "exampleDescription": 'ไฟล์ที่จะใช้อัพโหลดจะต้องเป็นไฟล์ที่มีนามสกุล .csv (UTF-8) เท่านั้น ซึ่งผู้ใช้งานสามารถสร้างช้อมูลได้จากโปรแกรมข้อมูลตารางทั่วไป เช่น MS Excel (Windows) หรือ Numbers (OSX) แล้วจึง Export ออกมาเพื่อทำการอัพโหลด<br><br> <strong>หมายเหตุ : </strong><ul class="list-decimal"><li>เครื่องหมาย * ในไฟล์ตัวอย่าง คือฟิล์ดที่จำเป็นต้องใส่ โดยมีรายละเอียดดังนี้<ul><li>เครื่องหมาย * หมายถึง ฟิล์ด Pre-Approved ซึ่งจะต้องใส่ให้ตรงกับฟิล์ด Pre-Approved ที่ระบบกำหนดมาให้ (เพียง 1 ฟิล์ด)</li><li>เครื่องหมาย ** หมายถึง ฟิล์ดที่จำเป็นต้องใส่ เช่น รหัสกลุ่มย่อยหลัก, รหัสกลุ่มย่อย</li></ul></li><li>หากท่านใช้ Microsoft Excel ควรจะใช้เวอร์ชั่นที่รองรับการ Export .csv แบบ UTF-8 หรือถ้าเป็นเวอร์ชั่นที่ไม่รองรับ ท่านจำเป็นต้องตั้งค่าภาษาตามขั้นตอนดังนี้ <ul><li>ไปที่ <b>Start Menu</b> -> คลิก <b>Region</b> -> เลือก <b>Administrative tab</b> -> คลิก <b>Change system locale...</b> -> เลือก <b>Thai (Thailand)</b> -> คลิก <b>OK</b> และ <b>Restart Computer</b></li></ul> </li></ul>',
        };

        $scope.keyword = "";
        $scope.fields_approval = [];

        $scope.filters_members_pre_approved = {};
        $scope.filters_members = {};
        $scope.filters_members_not_approved = {};
        $scope.filters_level_groups = {};
        $scope.filters_access_groups = {};
        $scope.filters_waiting_groups = {};

        var set_pagination = function(pagination_data) {
            $scope.defaultOptions.total = pagination_data.total;
            $scope.defaultOptions.last_page = pagination_data.last_page;
            $scope.defaultOptions.current_page = pagination_data.current_page;
            $scope.defaultOptions.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.level_groups = resp.data;
            for(var i=0; i<$scope.level_groups.length; i++) {
                var newLevelGroupsModifyDatetime = new Date($scope.level_groups[i].modify_datetime).toISOString();
                $scope.level_groups[i].modify_datetime = $filter('date')(newLevelGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersLevelGroupsClear, #btnFiltersLevelGroupsSubmit').button('reset');
        };

        var level_groups_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters_level_groups);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptions.sorting_order+"&order_direction="+$scope.defaultOptions.sorting_direction+filters;
            var query = level_groupsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.changeFilterLevelGroups = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersLevelGroupsSubmit').button('loading');
            }

            level_groups_query($scope.defaultOptions.page, $scope.defaultOptions.per_page);
        };

        $scope.clearFiltersLevelGroups = function () {
            $('#btnFiltersLevelGroupsClear').button('loading');
            $scope.filters_level_groups = {};
            $timeout(function() {
                angular.element('select#filter_level_groups_groups_id').trigger('change');
            }, 10);
            // $scope.changeFilterLevelGroups(false);
        };

        $scope.filtersChangeLevelGroups = function () {
            $scope.level_groups_sub_groups_list = [];
            if (!angular.isUndefined($scope.filters_level_groups.groups_id) && $scope.filters_level_groups.groups_id !== null) {
                groupsFactory.sub_groups({id:$scope.filters_level_groups.groups_id}).success(function(data) {
                    $scope.level_groups_sub_groups_list = data;
                    if ($scope.level_groups_sub_groups_list.length == 1) {
                        $scope.filters_level_groups.sub_groups_id = $scope.level_groups_sub_groups_list[0].id;
                        $timeout(function() {
                            angular.element('select#filter_level_groups_sub_groups_id').trigger('change');
                            $scope.changeFilterLevelGroups();
                        }, 10);
                    }
                });
            }
        };

        $scope.$watch('filters_level_groups.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#filter_level_groups_sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.filtersChangeLevelGroups();
        });

        $scope.toggleApprove = function(theLevelGroups, forceUpdate) {
            theLevelGroups.admin_id = $scope.admin.id;
            if (theLevelGroups.approve == 1) { theLevelGroups.approve = 0; } else { theLevelGroups.approve = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                level_groupsFactory.update(theLevelGroups)
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
            if (new_page != old_page) {
                level_groups_query(new_page, $scope.defaultOptions.per_page);
            }
        });

        $scope.$watch('level_groups_data.groups_id', function (new_value, old_value) {
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

        $scope.sortOrder = function(theLevelGroups) {
            level_groupsFactory.sort(theLevelGroups)
                .success(function(data) {
                    notification("success",data.message);
                    level_groups_query($scope.defaultOptions.current_page, $scope.defaultOptions.per_page);
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

                level_groupsFactory.sort(dataSort).success(function() {
                    notification("success", "The level_groups has been sortable.");
                    level_groups_query($scope.defaultOptions.current_page, $scope.defaultOptions.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.defaultOptions.sorting_order == newSortingOrder) {
                $scope.defaultOptions.sorting_direction = ($scope.defaultOptions.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.defaultOptions.sorting_order = newSortingOrder;
            level_groups_query($scope.defaultOptions.page, $scope.defaultOptions.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.defaultOptions.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        var access_groups_set_pagination = function(pagination_data) {
            $scope.defaultOptionsAccessGroups.total = pagination_data.total;
            $scope.defaultOptionsAccessGroups.last_page = pagination_data.last_page;
            $scope.defaultOptionsAccessGroups.current_page = pagination_data.current_page;
            $scope.defaultOptionsAccessGroups.per_page = pagination_data.per_page;
        };

        var access_groups_success_callback = function(resp) {
            $scope.access_groups = resp.data;
            for(var i=0; i<$scope.access_groups.length; i++) {
                var newLevelGroupsModifyDatetime = new Date($scope.access_groups[i].modify_datetime).toISOString();
                $scope.access_groups[i].modify_datetime = $filter('date')(newLevelGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            access_groups_set_pagination(resp);

            $('#btnFiltersAccessGroupsClear, #btnFiltersAccessGroupsSubmit').button('reset');
        };

        var access_groups_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters_access_groups);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsAccessGroups.sorting_order+"&order_direction="+$scope.defaultOptionsAccessGroups.sorting_direction+filters;
            var query = level_groupsFactory.access_groups(query_string);
            query.success(access_groups_success_callback);
        };

        access_groups_query($scope.defaultOptionsAccessGroups.page, $scope.defaultOptionsAccessGroups.per_page);

        $scope.changeFilterAccessGroups = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersAccessGroupsSubmit').button('loading');
            }

            access_groups_query($scope.defaultOptions.page, $scope.defaultOptions.per_page);
        };

        $scope.clearFiltersAccessGroups = function () {
            $('#btnFiltersAccessGroupsClear').button('loading');
            $scope.filters_access_groups = {};
            $timeout(function() {
                angular.element('select#filter_access_groups_groups_id').trigger('change');
            }, 10);
            // $scope.changeFilterAccessGroups(false);
        };

        $scope.filtersChangeAccessGroups = function () {
            $scope.access_groups_sub_groups_list = [];
            if (!angular.isUndefined($scope.filters_access_groups.groups_id) && $scope.filters_access_groups.groups_id !== null) {
                groupsFactory.sub_groups({id:$scope.filters_access_groups.groups_id}).success(function(data) {
                    $scope.access_groups_sub_groups_list = data;
                    if ($scope.access_groups_sub_groups_list.length == 1) {
                        $scope.filters_access_groups.sub_groups_id = $scope.access_groups_sub_groups_list[0].id;
                        $timeout(function() {
                            angular.element('select#filter_access_groups_sub_groups_id').trigger('change');
                            $scope.changeFilterAccessGroups();
                        }, 10);
                    }
                });
            }
        };

        $scope.$watch('filters_access_groups.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#filter_access_groups_sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.filtersChangeAccessGroups();
        });

        $scope.$watch('defaultOptionsAccessGroups.current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                access_groups_query(new_page, $scope.defaultOptionsAccessGroups.per_page);
            }
        });

        var waiting_groups_set_pagination = function(pagination_data) {
            $scope.defaultOptionsWaitingGroups.total = pagination_data.total;
            $scope.defaultOptionsWaitingGroups.last_page = pagination_data.last_page;
            $scope.defaultOptionsWaitingGroups.current_page = pagination_data.current_page;
            $scope.defaultOptionsWaitingGroups.per_page = pagination_data.per_page;
        };

        var waiting_groups_success_callback = function(resp) {
            $scope.waiting_groups = resp.data;
            for(var i=0; i<$scope.waiting_groups.length; i++) {
                var newLevelGroupsModifyDatetime = new Date($scope.waiting_groups[i].modify_datetime).toISOString();
                $scope.waiting_groups[i].modify_datetime = $filter('date')(newLevelGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            waiting_groups_set_pagination(resp);

            $('#btnFiltersWaitingGroupsClear, #btnFiltersWaitingGroupsSubmit').button('reset');
        };

        var waiting_groups_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters_waiting_groups);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsWaitingGroups.sorting_order+"&order_direction="+$scope.defaultOptionsWaitingGroups.sorting_direction+filters;
            var query = level_groupsFactory.waiting_groups(query_string);
            query.success(waiting_groups_success_callback);
        };

        waiting_groups_query($scope.defaultOptionsWaitingGroups.page, $scope.defaultOptionsWaitingGroups.per_page);

        $scope.changeFilterWaitingGroups = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersWaitingGroupsSubmit').button('loading');
            }

            waiting_groups_query($scope.defaultOptions.page, $scope.defaultOptions.per_page);
        };

        $scope.clearFiltersWaitingGroups = function () {
            $('#btnFiltersWaitingGroupsClear').button('loading');
            $scope.filters_waiting_groups = {};
            $timeout(function() {
                angular.element('select#filter_waiting_groups_groups_id').trigger('change');
            }, 10);
            // $scope.changeFilterWaitingGroups(false);
        };

        $scope.filtersChangeWaitingGroups = function () {
            $scope.waiting_groups_sub_groups_list = [];
            if (!angular.isUndefined($scope.filters_waiting_groups.groups_id) && $scope.filters_waiting_groups.groups_id !== null) {
                groupsFactory.sub_groups({id:$scope.filters_waiting_groups.groups_id}).success(function(data) {
                    $scope.waiting_groups_sub_groups_list = data;
                    if ($scope.waiting_groups_sub_groups_list.length == 1) {
                        $scope.filters_waiting_groups.sub_groups_id = $scope.waiting_groups_sub_groups_list[0].id;
                        $timeout(function() {
                            angular.element('select#filter_waiting_groups_sub_groups_id').trigger('change');
                            $scope.changeFilterWaitingGroups();
                        }, 10);
                    }
                });
            }
        };

        $scope.$watch('filters_waiting_groups.groups_id', function (new_value, old_value) {
            if (old_value !== undefined) {
                $timeout(function() {
                    angular.element('select#filter_waiting_groups_sub_groups_id').val('').trigger('change');
                }, 10);
            }

            $scope.filtersChangeWaitingGroups();
        });

        $scope.$watch('defaultOptionsWaitingGroups.current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                waiting_groups_query(new_page, $scope.defaultOptionsWaitingGroups.per_page);
            }
        });

        groupsFactory.all().success(function(data) {
            $scope.groups = data;
            $scope.level_groups_groups_list = data;
            $scope.access_groups_groups_list = data;
            $scope.waiting_groups_groups_list = data;

            $timeout(function() {
                $scope.$watch('level_groups_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                    }
                });
            }, 500);

            if ($scope.level_groups_groups_list.length == 1) {
                $scope.filters_level_groups.groups_id = $scope.level_groups_groups_list[0].id;
            }
            if ($scope.access_groups_groups_list.length == 1) {
                $scope.filters_access_groups.groups_id = $scope.access_groups_groups_list[0].id;
            }
            if ($scope.waiting_groups_groups_list.length == 1) {
                $scope.filters_waiting_groups.groups_id = $scope.waiting_groups_groups_list[0].id;
            }
        });

        level_groups_query($scope.defaultOptions.page, $scope.defaultOptions.per_page);

        var level_groups_members_pre_approved_query = function(/*page, per_page, filters*/) {
            var filters = "&" + $httpParamSerializer($scope.filters_members_pre_approved)
            // var filters = filters !== undefined ? "&"+filters : "";
            // var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsLevelGroupsMembersPreApproved.sorting_order+"&order_direction="+$scope.defaultOptionsLevelGroupsMembersPreApproved.sorting_direction+filters;
            level_groupsFactory.getMembersPreApproved({id:$routeParams.id}, filters).success(function(resp) {
                $scope.level_groups_data.members_pre_approved = resp.data;
            });
        };

        var level_groups_members_query = function(/*page, per_page, filters*/) {
            var filters = "&" + $httpParamSerializer($scope.filters_members)
            // var filters = filters !== undefined ? "&"+filters : "";
            // var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsLevelGroupsMembers.sorting_order+"&order_direction="+$scope.defaultOptionsLevelGroupsMembers.sorting_direction+filters;
            level_groupsFactory.getMembers({id:$routeParams.id}, filters).success(function(resp) {
                $scope.level_groups_data.members = resp.data;
            });
        };

        var level_groups_members_not_approved_query = function(/*page, per_page, filters*/) {
            var filters = "&" + $httpParamSerializer($scope.filters_members_not_approved)
            // var filters = filters !== undefined ? "&"+filters : "";
            // var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.defaultOptionsLevelGroupsMembersNotApproved.sorting_order+"&order_direction="+$scope.defaultOptionsLevelGroupsMembersNotApproved.sorting_direction+filters;
            level_groupsFactory.getMembersNotApproved({id:$routeParams.id}, filters).success(function(resp) {
                $scope.level_groups_data.members_not_approved = resp.data;
            });
        };

        $scope.getLevelGroups = function() {
            if (!angular.isUndefined($routeParams.id)) {
                level_groupsFactory.get({id:$routeParams.id}).success(function(data) {
                    $scope.level_groups_data = data;
                    $scope.mode = "Edit";

                    $timeout(function() {
                        pluginsService.popoverWithOptions();
                    }, 500);

                    $timeout(function() {
                        level_groups_members_pre_approved_query();
                        level_groups_members_query();
                        level_groups_members_not_approved_query();
                    }, 500);
                });
            } else {
                if ($scope.admin.groups_id != null) {
                    $scope.level_groups_data.groups_id = $scope.admin.groups_id;
                } else {
                    admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                        if (data.groups.length == 1) {
                            $scope.level_groups_data.groups_id = data.groups[0].id;
                        }
                    });
                }
            }
        };

        $scope.changeFilterGroups = function () {
            if (!angular.isUndefined($scope.level_groups_data.groups_id)) {
                $scope.sub_groups = [];
                groupsFactory.sub_groups({id:$scope.level_groups_data.groups_id}).success(function(data) {
                    $scope.sub_groups = data;
                    if ($scope.sub_groups.length == 1) {
                        $scope.level_groups_data.sub_groups_id = $scope.sub_groups[0].id;
                        $timeout(function() {
                            angular.element('select#sub_groups_id').trigger('change');
                        }, 10);
                    }
                });

                $scope.selectedGroup = _.find($scope.groups, ['id', $scope.level_groups_data.groups_id]);
                if (!angular.isUndefined($scope.selectedGroup)) {
                    $scope.fields_approval.push({ "value": "full_name", "title": "ชื่อ - นามสกุล" });
                    $scope.fields_approval.push({ "value": "id_card", "title": "เลขบัตรประจำตัวประชาชน" });

                    if ($scope.selectedGroup.id === 3) {
                        $scope.fields_approval.push({ "value": "license_id", "title": "เลขที่ใบอนุญาต" });
                    }

                    $scope.fields_approval.push({ "value": "occupation_id", "title": $scope.selectedGroup.meaning_of_occupation_id });
                }
            }
        }

        $scope.getLevelGroups();

        $scope.submitLevelGroups = function(theLevelGroups, nextAction) {
            functionsFactory.clearError(angular.element('.level_groups-frm'));
            theLevelGroups.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                level_groupsFactory.update(theLevelGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('level_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.level_groups-frm'));
                    });
            }else{
                level_groupsFactory.create(theLevelGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('level_groups/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('level_groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.level_groups-frm'));
                    });
            }
        }

        $scope.deleteLevelGroups = function(theLevelGroups) {
            var id = theLevelGroups.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                level_groupsFactory.delete(theLevelGroups).success(function(data) {
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

        // Delete Members Pre-Approved
        $scope.deleteMembersPreApproved = function(theMembersPreApproved) {
            var id = theMembersPreApproved.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                members_pre_approvedFactory.delete(theMembersPreApproved).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $scope.getLevelGroups();
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

        // Approve Member
        $scope.approveMember = function($event, theMembers) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.approve(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    $scope.getLevelGroups();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getLevelGroups();
                });
        };

        // Reject Member
        $scope.rejectMember = function($event, theMembers) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.reject(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    $scope.getLevelGroups();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getLevelGroups();
                });
        };

        // Get Result Upload
        $scope.checkResultsUpload = function(results, type) {
            var rejectedMembers = [];
            var updatedMembers = [];
            var uploadedMembers = [];

            if (!angular.isUndefined(results.rejected_members)) {
                rejectedMembers = results.rejected_members;
            }

            if (!angular.isUndefined(results.updated_members)) {
                updatedMembers = results.updated_members;
            }

            if (!angular.isUndefined(results.uploaded_members)) {
                uploadedMembers = results.uploaded_members;
            }

            $timeout(function() {
                console.log(type);
                if (type === "pre-approved") {
                    // console.log(uploadedMembers);
                    // console.log(rejectedMembers);
                    $scope.preApprovedMembersUploaded = uploadedMembers;
                    $scope.preApprovedMembersUpdated = updatedMembers;
                    $scope.preApprovedMembersRejected = rejectedMembers;
                } else {
                    $scope.membersUploaded = uploadedMembers;
                    $scope.membersUpdated = updatedMembers;
                    $scope.membersRejected = rejectedMembers;

                    console.log($scope.membersUpdated);
                }
            }, 100);
        };

        // Upload Pre-Approved Members
        $scope.uploadPreApprovedMembers = function(theLevelGroups) {
            var fileMembers;
            var $btnPreApprovedMembers = angular.element('#btn-upload-pre-approved-members');

            $btnPreApprovedMembers.button('loading');

            fileMembers = angular.element('#preApprovedFile')[0].files[0];
            level_groupsFactory.uploadPreApprovedMembers(theLevelGroups, fileMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    // level_groupsFactory.checkPreApprovedMembers(theLevelGroups).then(function() {
                    //     $scope.getLevelGroups();
                    // }, function() {
                        $scope.getLevelGroups();
                    // });

                    $scope.checkResultsUpload(data, 'pre-approved');
                    $btnPreApprovedMembers.button('reset');
                    angular.element('#removePreApprovedFile').trigger('click');
                })
                .error(function(data) {
                    if (!angular.isUndefined(data.file)) {
                        notification("error", data.file);
                    } else {
                        notification("error", data.message);
                        angular.element('#removePreApprovedFile').trigger('click');
                    }

                    // level_groupsFactory.checkPreApprovedMembers(theLevelGroups).then(function() {
                    //     $scope.getLevelGroups();
                    // }, function() {
                        $scope.getLevelGroups();
                    // });

                    $scope.checkResultsUpload(data, 'pre-approved');
                    $btnPreApprovedMembers.button('reset');

                });
        };

        // Upload Members
        $scope.uploadMembers = function(theLevelGroups) {
            var fileMembers;
            var $btnMembers = angular.element('#btn-upload-members');

            $btnMembers.button('loading');

            fileMembers = angular.element('#membersFile')[0].files[0];
            level_groupsFactory.uploadMembers(theLevelGroups, fileMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    // level_groupsFactory.checkPreApprovedMembers(theLevelGroups).then(function() {
                    //     $scope.getLevelGroups();
                    // }, function() {
                        $scope.getLevelGroups();
                    // });

                    $scope.checkResultsUpload(data);
                    $btnMembers.button('reset');
                    angular.element('#removeMembersFile').trigger('click');
                })
                .error(function(data) {
                    if (!angular.isUndefined(data.file)) {
                        notification("error", data.file);
                    } else {
                        notification("error", data.message);
                        angular.element('#removeMembersFile').trigger('click');
                    }

                    // level_groupsFactory.checkPreApprovedMembers(theLevelGroups).then(function() {
                    //     $scope.getLevelGroups();
                    // }, function() {
                        $scope.getLevelGroups();
                    // });

                    $scope.checkResultsUpload(data);
                    $btnMembers.button('reset');

                });
        };

        $scope.downloadExampleFileUpload = function(groupKey, model) {
            window.location.href = settingsFactory.get(model) + '/' + groupKey + '/example/file';
        };

        $scope.changeFilterMembersPreApproved = function() {
            level_groups_members_pre_approved_query();
        };

        $scope.changeFilterMembers = function() {
            level_groups_members_query();
        };

        $scope.changeFilterMembersNotApproved = function() {
            level_groups_members_not_approved_query();
        };

        $scope.detachMembers = function (theMembers) {
            var theLevelGroups = $scope.level_groups_data;
            var dataDetach = {
                'members': []
            };
            dataDetach.members.push(theMembers);
            var alert = confirm("Are you sure to detach #" + theMembers.id + " ?");
            if (alert == true) {
                level_groupsFactory.detachMembers(theLevelGroups, dataDetach).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }

                    $scope.getLevelGroups();
                })
                .error(function () {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getLevelGroups();
                });
            }
        };

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



