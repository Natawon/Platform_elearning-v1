'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('groupsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', 'groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, groupsFactory, functionsFactory, settingsFactory) {

        $scope.groups = {};
        $scope.groups_data = {
            "targetaudience": "O",
            "max_account_age": 90,
            "max_password_age": 90,
            "incorrect_password_limit": 5
        };

        $scope.mode = "Create";

        $scope.base_groups_thumbnail = settingsFactory.getURL('base_groups_thumbnail');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.fields_approval = [
            { "value": "email", "title": "อีเมล์" },
            { "value": "full_name", "title": "ชื่อ - นามสกุล" },
            { "value": "id_card", "title": "เลขบัตรประจำตัวประชาชน" },
            { "value": "license_id", "title": "เลขที่ใบอนุญาต" },
            { "value": "occupation_id", "title": "รหัสประจำตัวของหน่วยงาน" }
        ];

        $scope.targetaudiences = [
            { "value": "B", "title": "Broker" },
            { "value": "L", "title": "Lisetd Company" },
            { "value": "F", "title": "Fund" },
            { "value": "R", "title": "Retail Investor" },
            { "value": "O", "title": "Other" }
        ];

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.groups = resp.data;
            for(var i=0; i<$scope.groups.length; i++) {
                var newGroupsModifyDatetime = new Date($scope.groups[i].modify_datetime).toISOString();
                $scope.groups[i].modify_datetime = $filter('date')(newGroupsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var groups_query = function(page, per_page) {
            var filters = angular.element('.frm-filter').serialize();
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = groupsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleInternal = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.internal == 1) { theGroups.internal = 0; } else { theGroups.internal = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.toggleNeedApproval = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.need_approval == 1) { theGroups.need_approval = 0; } else { theGroups.need_approval = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.toggleIsConnectRegis = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.is_connect_regis == 1) { theGroups.is_connect_regis = 0; } else { theGroups.is_connect_regis = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.togglePage = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.page == 1) { theGroups.page = 0; } else { theGroups.page = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.toggleIsShowRegisterBtn = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.is_show_register_btn == 1) { theGroups.is_show_register_btn = 0; } else { theGroups.is_show_register_btn = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.toggleStatus = function(theGroups, forceUpdate) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.status == 1) { theGroups.status = 0; } else { theGroups.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                groupsFactory.update(theGroups)
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

        $scope.updateStatus = function(theGroups) {
            if (theGroups.status == 1) { theGroups.status = 0; } else { theGroups.status = 1; }
            groupsFactory.updateStatus({'id': theGroups.id, 'status': theGroups.status})
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

        $scope.toggleMultiLangCertificate = function(theGroups) {
            theGroups.admin_id = $scope.admin.id;
            if (theGroups.multi_lang_certificate == 1) { theGroups.multi_lang_certificate = 0; } else { theGroups.multi_lang_certificate = 1; }
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
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

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                groups_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theGroups) {
            groupsFactory.sort(theGroups)
                .success(function(data) {
                    notification("success",data.message);
                    groups_query($scope.current_page, $scope.per_page);
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

                groupsFactory.sort(dataSort).success(function() {
                    notification("success", "The groups has been sortable.");
                    groups_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            groups_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        groups_query($scope.page, $scope.per_page);

        $scope.changeFilter = function() {
            groups_query($scope.page, $scope.per_page);
        };

        if (!angular.isUndefined($routeParams.id)) {
            groupsFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.groups_data = data;
                $scope.mode = "Edit";

                if ($scope.groups_data.id !== 3) {
                    _.remove($scope.fields_approval, function(field) {
                      return field.value === "license_id";
                    });
                }

                _.find($scope.fields_approval, function(field) {
                    if (field.value === "occupation_id") {
                        field.title = $scope.groups_data.meaning_of_occupation_id;
                        return false;
                    }
                });
            })
        }

        $scope.submitGroups = function(theGroups, nextAction) {
            functionsFactory.clearError(angular.element('.groups-frm'));

            if (theGroups.max_account_age == null) {
                theGroups.max_account_age = 90;
            }
            if (theGroups.max_password_age == null) {
                theGroups.max_password_age = 90;
            }

            theGroups.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                groupsFactory.update(theGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.groups-frm'));
                    });
            }else{
                groupsFactory.create(theGroups)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('groups/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('groups'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.groups-frm'));
                    });
            }
        }

        $scope.deleteGroups = function(theGroups) {
            var id = theGroups.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                groupsFactory.delete(theGroups).success(function(data) {
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



