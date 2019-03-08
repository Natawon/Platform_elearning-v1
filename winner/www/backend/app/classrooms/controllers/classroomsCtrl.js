'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('classroomsCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'classroomsFactory', 'level_groupsFactory', 'sub_groupsFactory', 'groupsFactory', 'coursesFactory', 'adminsFactory', 'admins_groupsFactory', 'membersFactory', 'members_pre_approvedFactory', 'pluginsService', 'functionsFactory', 'settingsFactory',
    function ($scope, $sce, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, classroomsFactory, level_groupsFactory, sub_groupsFactory, groupsFactory, coursesFactory, adminsFactory, admins_groupsFactory, membersFactory, members_pre_approvedFactory, pluginsService, functionsFactory, settingsFactory) {

        $scope._isArray = angular.isArray;

        $scope.classrooms = {};
        $scope.classrooms_data = {};
        $scope.level_groups = [];
        $scope.targets = [];
        $scope.courses = [];
        $scope.sub_groups = [];

        $scope.mode = "Create";

        $scope.uploadMembersOptions = {
            "exampleDescription": 'ไฟล์ที่จะใช้อัพโหลดจะต้องเป็นไฟล์ที่มีนามสกุล .csv (UTF-8) เท่านั้น ซึ่งผู้ใช้งานสามารถสร้างช้อมูลได้จากโปรแกรมข้อมูลตารางทั่วไป เช่น MS Excel (Windows) หรือ Numbers (OSX) แล้วจึง Export ออกมาเพื่อทำการอัพโหลด<br><br> <strong>หมายเหตุ : </strong><ul class="list-decimal"><li>เครื่องหมาย * ในไฟล์ตัวอย่าง คือฟิล์ดที่จำเป็นต้องใส่ โดยมีรายละเอียดดังนี้<ul><li>เครื่องหมาย * หมายถึง ฟิล์ด Pre-Approved ซึ่งจะต้องใส่ให้ตรงกับฟิล์ด Pre-Approved ที่ระบบกำหนดมาให้ (เพียง 1 ฟิล์ด)</li><li>เครื่องหมาย ** หมายถึง ฟิล์ดที่จำเป็นต้องใส่ เช่น รหัสกลุ่มย่อยหลัก, รหัสกลุ่มย่อย</li></ul></li><li>หากท่านใช้ Microsoft Excel ควรจะใช้เวอร์ชั่นที่รองรับการ Export .csv แบบ UTF-8 หรือถ้าเป็นเวอร์ชั่นที่ไม่รองรับ ท่านจำเป็นต้องตั้งค่าภาษาตามขั้นตอนดังนี้ <ul><li>ไปที่ <b>Start Menu</b> -> คลิก <b>Region</b> -> เลือก <b>Administrative tab</b> -> คลิก <b>Change system locale...</b> -> เลือก <b>Thai (Thailand)</b> -> คลิก <b>OK</b> และ <b>Restart Computer</b></li></ul> </li></ul>',
            "uploadToGroup": null
        };

        $scope.selected_group_upload = {};
        $scope.groupsExampleFileUpload = [];

        $scope.filters = {
            // "search": "",
        };

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.filters_members_pre_approved = {};
        $scope.filters_members = {};

        $timeout(function() {
            $('#start_datetime, #end_datetime').datetimepicker({
                prevText: '<i class="fa fa-angle-left"></i>',
                nextText: '<i class="fa fa-angle-right"></i>',
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                controlType: 'select',
                oneLine: true,
                timeInput: true,
                stepMinute: 5,

            });
        }, 2000);
        ///

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.classrooms = resp.data;
            for(var i=0; i<$scope.classrooms.length; i++) {
                var newClassRoomsModifyDatetime = new Date($scope.classrooms[i].modify_datetime).toISOString();
                $scope.classrooms[i].modify_datetime = $filter('date')(newClassRoomsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var classrooms_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = classroomsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theClassRooms, forceUpdate) {
            theClassRooms.admin_id = $scope.admin.id;
            if (theClassRooms.status == 1) { theClassRooms.status = 0; } else { theClassRooms.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                classroomsFactory.update(theClassRooms)
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

        $scope.updateStatus = function(theClassRooms) {
            if (theClassRooms.status == 1) { theClassRooms.status = 0; } else { theClassRooms.status = 1; }
            classroomsFactory.updateStatus({'id': theClassRooms.id, 'status': theClassRooms.status})
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

        $scope.$watch('classrooms_data.classroom2sub_group', function (new_value, old_value) {
            $scope.level_groups = _.filter($scope.level_groups, function(o) {
                return _.find($scope.classrooms_data.classroom2sub_group, function(ele) { return ele == o.sub_groups_id; }) !== undefined;
            });

            if (new_value !== undefined) {
                if (angular.isArray(old_value)) {
                    old_value = _.sortBy(old_value, [function(o) { return o; }])
                }
                new_value = _.sortBy(new_value, [function(o) { return o; }])
                if (!angular.equals(new_value, old_value) && angular.isArray(new_value) && new_value.length > 0) {
                    level_groupsFactory.allBySubGroups($httpParamSerializer({"sub_groups[]": new_value})).success(function(data) {
                        for (var i = 0; i < data.owner.length; i++) {
                            if (_.find($scope.level_groups, ['id', data.owner[i].id]) === undefined) {
                                $scope.level_groups.push({id: data.owner[i].id, sub_groups_id: data.owner[i].sub_groups_id, title: data.owner[i].title});
                            }
                        }
                        for (var i = 0; i < data.access.length; i++) {
                            if (_.find($scope.level_groups, ['id', data.access[i].id]) === undefined) {
                                $scope.level_groups.push({id: data.access[i].id, sub_groups_id: data.access[i].sub_groups_id, title: data.access[i].title});
                            }
                        }
                    }).error(function(data) {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
                }
            }
        });

        $scope.$watch('uploadMembersOptions.uploadToGroup', function (new_value, old_value) {
            if (old_value != new_value) {
                if (new_value !== null && new_value !== undefined) {
                    groupsFactory.get({id: new_value}).success(function (data) {
                        $scope.selected_group_upload = data;
                    });
                }

                $timeout(function() {
                    angular.element('select.uploadToGroup').trigger('change');
                }, 10);
            }
        });

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                classrooms_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            classroomsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    classrooms_query($scope.current_page, $scope.per_page);
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

                classroomsFactory.sort(dataSort).success(function() {
                    notification("success", "The classrooms has been sortable.");
                    classrooms_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            classrooms_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        classrooms_query($scope.page, $scope.per_page);

        var classrooms_members_pre_approved_query = function() {
            var filters = "&" + $httpParamSerializer($scope.filters_members_pre_approved)
            classroomsFactory.getMembersPreApproved({id:$routeParams.id}, filters).success(function(resp) {
                $scope.classrooms_data.members_pre_approved = resp.data;
            });
        };

        var classrooms_members_query = function() {
            var filters = "&" + $httpParamSerializer($scope.filters_members)
            classroomsFactory.getMembers({id:$routeParams.id}, filters).success(function(resp) {
                $scope.classrooms_data.members = resp.data;
            });
        };

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            classrooms_query($scope.page, $scope.per_page);
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

        $scope.getClassroom = function() {
            if (!angular.isUndefined($routeParams.id)) {
                classroomsFactory.get({id:$routeParams.id}).success(function(data) {
                    $scope.classrooms_data = data;
                    $scope.mode = "Edit";

                    $timeout(function() {
                        pluginsService.popoverWithOptions();
                        $scope.uploadMembersOptions.uploadToGroup = $scope.classrooms_data.groups_id;
                    }, 500);

                    $scope.classrooms_data.classroom2sub_group = new Array();
                    if (!angular.isUndefined($scope.classrooms_data.sub_groups) && $scope.classrooms_data.sub_groups.length != 0) {
                        for (var i = 0; i < $scope.classrooms_data.sub_groups.length; i++) {
                            $scope.classrooms_data.classroom2sub_group.push($scope.classrooms_data.sub_groups[i].id);
                        }
                    }

                    $scope.classrooms_data.classroom2level_group = new Array();
                    if (!angular.isUndefined($scope.classrooms_data.level_groups) && $scope.classrooms_data.level_groups.length != 0) {
                        for (var i = 0; i < $scope.classrooms_data.level_groups.length; i++) {
                            $scope.classrooms_data.classroom2level_group.push($scope.classrooms_data.level_groups[i].id);
                        }
                    }

                    $scope.classrooms_data.classroom2member = new Array();
                    if (!angular.isUndefined($scope.classrooms_data.members) && $scope.classrooms_data.members.length != 0) {
                        for (var i = 0; i < $scope.classrooms_data.members.length; i++) {
                            $scope.classrooms_data.classroom2member.push($scope.classrooms_data.members[i].id);
                        }
                    }

                    $scope.classrooms_data.classroom2course = new Array();
                    if (!angular.isUndefined($scope.classrooms_data.courses) && $scope.classrooms_data.courses.length != 0) {
                        for (var i = 0; i < $scope.classrooms_data.courses.length; i++) {
                            $scope.classrooms_data.classroom2course.push($scope.classrooms_data.courses[i].id);
                        }
                    }
                })

                $timeout(function() {
                    classrooms_members_pre_approved_query();
                    classrooms_members_query();
                }, 500);
            } else {
                if ($scope.admin.groups_id != null) {
                    $scope.classrooms_data.groups_id = $scope.admin.groups_id;
                } else {
                    admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                        if (data.groups.length == 1) {
                            $scope.classrooms_data.groups_id = data.groups[0].id;
                        }
                    });
                }
            }
        };

        $scope.getClassroom();

        sub_groupsFactory.all().success(function(data) {
            $scope.sub_groups = data;
        });

        // level_groupsFactory.all().success(function(data) {
        //     for (var i = 0; i < data.owner.length; i++) {
        //         $scope.level_groups.push({id: data.owner[i].id, title: data.owner[i].title});
        //     }
        //     for (var i = 0; i < data.access.length; i++) {
        //         $scope.level_groups.push({id: data.access[i].id, title: data.access[i].title});
        //     }
        // });

        coursesFactory.all().success(function (data) {
            for (var i = 0; i < data.length; i++) {
                $scope.courses.push({id: data[i].id, title: data[i].title});
            }
        });

        $scope.changeFilterGroups = function () {
            if (!angular.isUndefined($scope.classrooms_data.groups_id)) {
                $scope.sub_groups = [];
                groupsFactory.sub_groups({id:$scope.classrooms_data.groups_id}).success(function(data) {
                    $scope.sub_groups = data;
                });
            }
        }

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        });

        $scope.submitClassRooms = function(theClassRooms, nextAction) {
            functionsFactory.clearError(angular.element('.classrooms-frm'));
            theClassRooms.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                classroomsFactory.update(theClassRooms)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('classrooms'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.classrooms-frm'));
                    });
            }else{
                classroomsFactory.create(theClassRooms)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('classrooms/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('classrooms'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.classrooms-frm'));
                    });
            }
        }

        $scope.deleteClassRooms = function(theClassRooms) {
            var id = theClassRooms.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                classroomsFactory.delete(theClassRooms).success(function(data) {
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

        $scope.detachMembers = function (theMembers) {
            var theClassRooms = $scope.classrooms_data;
            var dataDetach = {
                'members': []
            };
            dataDetach.members.push(theMembers);
            var alert = confirm("Are you sure to detach #" + theMembers.id + " ?");
            if (alert == true) {
                classroomsFactory.detachMembers(theClassRooms, dataDetach).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }

                    $scope.getClassroom();
                })
                .error(function () {
                    notification("error", " No Access-Control-Allow-Origin");
                    $scope.getClassroom();
                });
            }
        };

        // Delete Members Pre-Approved
        $scope.deleteMembersPreApproved = function(theMembersPreApproved) {
            var id = theMembersPreApproved.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                members_pre_approvedFactory.delete(theMembersPreApproved).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $scope.getClassroom();
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
                    $scope.getClassroom();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getClassroom();
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
                    $scope.getClassroom();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getClassroom();
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
                }
            }, 100);
        };

        // Upload Pre-Approved Members
        $scope.uploadPreApprovedMembers = function(theClassrooms) {
            var fileMembers;
            var $btnPreApprovedMembers = angular.element('#btn-upload-pre-approved-members');

            $btnPreApprovedMembers.button('loading');

            if ($scope.uploadMembersOptions.uploadToGroup === undefined || $scope.uploadMembersOptions.uploadToGroup === null) {
                notification("error", "กรุณาเลือกลุ่ม (Group) ที่จะอัพโหลด");
                $btnPreApprovedMembers.button('reset');
                return false;
            }

            fileMembers = angular.element('#preApprovedFile')[0].files[0];
            classroomsFactory.uploadPreApprovedMembers(theClassrooms, fileMembers, $scope.uploadMembersOptions.uploadToGroup)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    $scope.getClassroom();
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

                    $scope.getClassroom();
                    $scope.checkResultsUpload(data, 'pre-approved');
                    $btnPreApprovedMembers.button('reset');

                });
        };

        // Upload Members
        $scope.uploadMembers = function(theClassrooms) {
            var fileMembers;
            var $btnMembers = angular.element('#btn-upload-members');

            $btnMembers.button('loading');

            if ($scope.uploadMembersOptions.uploadToGroup === undefined || $scope.uploadMembersOptions.uploadToGroup === null) {
                notification("error", "กรุณาเลือกลุ่ม (Group) ที่จะอัพโหลด");
                $btnMembers.button('reset');
                return false;
            }

            fileMembers = angular.element('#membersFile')[0].files[0];
            classroomsFactory.uploadMembers(theClassrooms, fileMembers, $scope.uploadMembersOptions.uploadToGroup)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    $scope.getClassroom();
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

                    $scope.getClassroom();
                    $scope.checkResultsUpload(data);
                    $btnMembers.button('reset');

                });
        };

        $scope.downloadExampleFileUpload = function(groupKey, model) {
            window.location.href = settingsFactory.get(model) + '/' + groupKey + '/example/file';
        };

        $scope.changeFilterMembersPreApproved = function() {
            classrooms_members_pre_approved_query();
        };

        $scope.changeFilterMembers = function() {
            classrooms_members_query();
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
