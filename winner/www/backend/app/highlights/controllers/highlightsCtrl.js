'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('highlightsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', 'highlightsFactory', 'groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, highlightsFactory, groupsFactory, functionsFactory, settingsFactory) {

        $scope.highlights = {};
        $scope.highlights_data = {
            "url": "#"
        };

        $scope.mode = "Create";

        $scope.base_highlights_picture = settingsFactory.getURL('base_highlights_picture');

        ///Add on datepicker dateFormat
        $( "#start_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            onSelect:function (date) {
                $scope.highlights_data.start_date = date;
            }
        });

        $( "#end_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            onSelect:function (date) {
                $scope.highlights_data.end_date = date;
            }
        });
        ///

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        var set_pagination = function(pagination_data) {

            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;

        };

        var success_callback = function(resp) {
            $scope.highlights = resp.data;
            for(var i=0; i<$scope.highlights.length; i++) {
                var newHighlightsModifyDatetime = new Date($scope.highlights[i].modify_datetime).toISOString();
                $scope.highlights[i].modify_datetime = $filter('date')(newHighlightsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var highlights_query = function(page, per_page) {
            var filters = angular.element('.frm-filter').serialize();
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = highlightsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theHighlights, forceUpdate) {
            theHighlights.admin_id = $scope.admin.id;
            if (theHighlights.status == 1) { theHighlights.status = 0; } else { theHighlights.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                highlightsFactory.update(theHighlights)
                    .success(function(data) {
                        notification("success",data.message);
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theHighlights) {
            if (theHighlights.status == 1) { theHighlights.status = 0; } else { theHighlights.status = 1; }
            highlightsFactory.updateStatus({'id': theHighlights.id, 'status': theHighlights.status})
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
                highlights_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theHighlights) {
            highlightsFactory.sort(theHighlights)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    highlights_query($scope.current_page, $scope.per_page);
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

                highlightsFactory.sort(dataSort).success(function() {
                    notification("success", "The highlights has been sortable.");
                    highlights_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            highlights_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        highlights_query($scope.page, $scope.per_page);

        $scope.changeFilter = function() {
            highlights_query($scope.page, $scope.per_page);
        };

        groupsFactory.all().success(function(data) {
            $scope.groups = data;
        });

        if (!angular.isUndefined($routeParams.id)) {
            highlightsFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.highlights_data = data;
                    $scope.mode = "Edit";
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        $scope.submitHighlights = function(theHighlights, nextAction) {
            functionsFactory.clearError(angular.element('.highlights-frm'));
            theHighlights.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                highlightsFactory.update(theHighlights)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('highlights'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.highlights-frm'));
                    });
            }else{
                highlightsFactory.create(theHighlights)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('highlights/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('highlights'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.highlights-frm'));
                    });
            }
        }

        $scope.deleteHighlights = function(theHighlights) {
            var id = theHighlights.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                highlightsFactory.delete(theHighlights)
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
        //         for (var index in $scope.highlights) {
        //             $scope.highlights[index].admin_id = $scope.admin.id
        //             $scope.highlights[index].order = parseInt(index) + 1;
        //         }
        //         highlightsFactory.orders($scope.highlights).success(function() {
        //             highlights_query($scope.page, $scope.per_page);
        //             notification("success", "The highlights has been sortable.");
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
