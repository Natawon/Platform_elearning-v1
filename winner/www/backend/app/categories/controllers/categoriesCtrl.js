'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('categoriesCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$httpParamSerializer', '$timeout', 'categoriesFactory', 'groupsFactory', 'admins_groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $httpParamSerializer, $timeout, categoriesFactory, groupsFactory, admins_groupsFactory, functionsFactory, settingsFactory) {

        $scope.categories = {};
        $scope.categories_data = {
            "css_class": "t0"
        };
        $scope.selected_groups = {};

        $scope.base_categories_icon = settingsFactory.getURL('base_categories_icon');

        $scope.mode = "Create";

        $scope.defaultColors = [
            { "title": "-- เลือกสี --", "css_class": "t0", "hex_color": "#ffa400" },
            { "title": "t1", "css_class": "t1", "hex_color": "#ffe76d" },
            { "title": "t2", "css_class": "t2", "hex_color": "#999999" },
            { "title": "t3", "css_class": "t3", "hex_color": "#FF9700" },
            { "title": "t4", "css_class": "t4", "hex_color": "#7CACD2" },
            { "title": "t5", "css_class": "t5", "hex_color": "#8ecdbc" }
        ];

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

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.categories = resp.data;
            for(var i=0; i<$scope.categories.length; i++) {
                var newCategoriesModifyDatetime = new Date($scope.categories[i].modify_datetime).toISOString();
                $scope.categories[i].modify_datetime = $filter('date')(newCategoriesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var categories_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = categoriesFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theCategories, forceUpdate) {
            theCategories.admin_id = $scope.admin.id;
            if (theCategories.status == 1) { theCategories.status = 0; } else { theCategories.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                categoriesFactory.update(theCategories)
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

        $scope.updateStatus = function(theCategories) {
            if (theCategories.status == 1) { theCategories.status = 0; } else { theCategories.status = 1; }
            categoriesFactory.updateStatus({'id': theCategories.id, 'status': theCategories.status})
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
                categories_query(new_page, $scope.per_page);
            }
        });

        $scope.$watch('categories_data.css_class', function(new_value, old_value) {
            // if (new_value != old_value) {

                var selectedColor = $scope.defaultColors.find(function(el) {
                    return el.css_class === new_value;
                });

                $scope.categories_data.hex_color = selectedColor.hex_color;

            // }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        })

        $scope.sortOrder = function(theCategories) {
            categoriesFactory.sort(theCategories)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    categories_query($scope.current_page, $scope.per_page);
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

                categoriesFactory.sort(dataSort).success(function() {
                    notification("success", "The categories has been sortable.");
                    categories_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            categories_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        categories_query($scope.page, $scope.per_page);

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            categories_query($scope.page, $scope.per_page);
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
            categoriesFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.categories_data = data;
                $scope.mode = "Edit";
            })
        } else {
            if ($scope.admin.groups_id != null) {
                $scope.categories_data.groups_id = $scope.admin.groups_id;
            } else {
                admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                    if (data.groups.length == 1) {
                        $scope.categories_data.groups_id = data.groups[0].id;
                    }
                });
            }
        }

        $scope.submitCategories = function(theCategories, nextAction) {
            functionsFactory.clearError(angular.element('.categories-frm'));
            theCategories.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                categoriesFactory.update(theCategories)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('categories'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.categories-frm'));
                    });
            }else{
                categoriesFactory.create(theCategories)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('categories/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('categories'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.categories-frm'));
                    });
            }
        }

        $scope.deleteCategories = function(theCategories) {
            var id = theCategories.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                categoriesFactory.delete(theCategories).success(function(data) {
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
