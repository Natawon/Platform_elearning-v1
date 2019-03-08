'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('license_typesCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', 'license_typesFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, license_typesFactory, functionsFactory, settingsFactory) {

        $scope.license_types = {};
        $scope.license_types_data = {};
        $scope.selected_groups = {};

        $scope.base_license_types_icon = settingsFactory.getURL('base_license_types_icon');

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $timeout(function() {
            $('#expire_datetime').datetimepicker({
                prevText: '<i class="fa fa-angle-left"></i>',
                nextText: '<i class="fa fa-angle-right"></i>',
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                controlType: 'select',
                oneLine: true,
                timeInput: true,
                stepMinute: 5,
                startView: 'year'
            });
        }, 500);

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.license_types = resp.data;
            for(var i=0; i<$scope.license_types.length; i++) {
                var newLicenseTypesModifyDatetime = new Date($scope.license_types[i].modify_datetime).toISOString();
                $scope.license_types[i].modify_datetime = $filter('date')(newLicenseTypesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var license_types_query = function(page, per_page) {
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction;
            var query = license_typesFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theLicenseTypes, forceUpdate) {
            theLicenseTypes.admin_id = $scope.admin.id;
            if (theLicenseTypes.status == 1) { theLicenseTypes.status = 0; } else { theLicenseTypes.status = 1; }
            if ($scope.mode == 'Edit' || forceUpdate === true) {
                license_typesFactory.update(theLicenseTypes)
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

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                license_types_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theLicenseTypes) {
            license_typesFactory.sort(theLicenseTypes)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    license_types_query($scope.current_page, $scope.per_page);
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

                license_typesFactory.sort(dataSort).success(function() {
                    notification("success", "The license_types has been sortable.");
                    license_types_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            license_types_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        license_types_query($scope.page, $scope.per_page);

        if (!angular.isUndefined($routeParams.id)) {
            license_typesFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.license_types_data = data;
                $scope.mode = "Edit";
            })
        }

        $scope.submitLicenseTypes = function(theLicenseTypes, nextAction) {
            functionsFactory.clearError(angular.element('.license_types-frm'));
            theLicenseTypes.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                license_typesFactory.update(theLicenseTypes)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('license_types'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.license_types-frm'));
                    });
            }else{
                license_typesFactory.create(theLicenseTypes)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('license_types/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('license_types'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.license_types-frm'));
                    });
            }
        }

        $scope.deleteLicenseTypes = function(theLicenseTypes) {
            var id = theLicenseTypes.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                license_typesFactory.delete(theLicenseTypes).success(function(data) {
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
