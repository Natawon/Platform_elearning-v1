'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('methodsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'methodsFactory', 'functionsFactory', 'settingsFactory',
        function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, methodsFactory, functionsFactory, settingsFactory) {

        $scope.methods = {};
        $scope.methods_data = {
            'type': 1
        };

        $scope.mode = "Create";

        $scope.base_methods_picture = settingsFactory.getURL('base_methods_picture');

        ///Add on datepicker dateFormat
        $( "#start_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            onSelect:function (date) {
                $scope.methods_data.start_date = date;
            }
        });

        $( "#end_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            onSelect:function (date) {
                $scope.methods_data.end_date = date;
            }
        });
        ///

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

        $scope.methods_types = [
            { "value": 1, "title": "บัตรเครดิต/เดบิต" },
            { "value": 2, "title": "โอนเงิน" },
        ];

        var set_pagination = function(pagination_data) {

            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;

        };

        var success_callback = function(resp) {
            $scope.methods = resp.data;
            for(var i=0; i<$scope.methods.length; i++) {
                var newMethodsModifyDatetime = new Date($scope.methods[i].modify_datetime).toISOString();
                $scope.methods[i].modify_datetime = $filter('date')(newMethodsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var methods_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = methodsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theMethods, forceUpdate) {
            theMethods.admin_id = $scope.admin.id;
            if (theMethods.status == 1) { theMethods.status = 0; } else { theMethods.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                methodsFactory.update(theMethods)
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

        $scope.updateStatus = function(theMethods) {
            if (theMethods.status == 1) { theMethods.status = 0; } else { theMethods.status = 1; }
            methodsFactory.updateStatus({'id': theMethods.id, 'status': theMethods.status})
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
                methods_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            methodsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    methods_query($scope.current_page, $scope.per_page);
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

                methodsFactory.sort(dataSort).success(function() {
                    notification("success", "The methods has been sortable.");
                    methods_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            methods_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        methods_query($scope.page, $scope.per_page);

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            methods_query($scope.page, $scope.per_page);
        };

        $scope.clearFilters = function () {
            $('#btnFiltersClear').button('loading');
            // angular.element('.frm-filter')[0].reset();
            $scope.filters = {};
            $timeout(function() {
                angular.element('select#filter_type').trigger('change');
            }, 10);
            // $scope.changeFilter(false);
        };

        if (!angular.isUndefined($routeParams.id)) {
            methodsFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.methods_data = data;
                    $scope.mode = "Edit";
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        $scope.submitMethods = function(theMethods, nextAction) {
            functionsFactory.clearError(angular.element('.methods-frm'));
            theMethods.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                methodsFactory.update(theMethods)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('methods'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.methods-frm'));
                    });
            }else{
                methodsFactory.create(theMethods)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('methods/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('methods'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.methods-frm'));
                    });
            }
        }

        $scope.deleteMethods = function(theMethods) {
            var id = theMethods.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                methodsFactory.delete(theMethods)
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

        // $scope.sortableOptions = {
        //     stop: function(e, ui) {
        //         for (var index in $scope.methods) {
        //             $scope.methods[index].admin_id = $scope.admin.id
        //             $scope.methods[index].order = parseInt(index) + 1;
        //         }
        //         methodsFactory.orders($scope.methods).success(function() {
        //             methods_query($scope.page, $scope.per_page);
        //             notification("success", "The methods has been sortable.");
        //         });
        //     }
        // };

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
