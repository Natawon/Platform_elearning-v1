'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('documentsCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', 'documentsFactory', 'coursesFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, documentsFactory, coursesFactory, functionsFactory, settingsFactory) {

        $scope.documents = {};
        $scope.documents_data = {};
        $scope.selected_courses = {};

        $scope.mode = "Create";

        $scope.base_documents_file = settingsFactory.getURL('base_documents_file');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        if (!angular.isUndefined($routeParams.courses_id)) {
            $scope.documents_data.courses_id = parseInt($routeParams.courses_id);
        }

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.documents = resp.data;
            for (var i = 0; i < $scope.documents.length; i++) {
                var newDocumentsModifyDatetime = new Date($scope.documents[i].modify_datetime).toISOString();
                $scope.documents[i].modify_datetime = $filter('date')(newDocumentsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.documents[i].no = (resp.from + i);
            }
            set_pagination(resp);
        };

        var documents_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.documents(query_courses, query_string);
            } else {
                var query = documentsFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theDocuments, forceUpdate) {
            theDocuments.admin_id = $scope.admin.id;
            if (theDocuments.status == 1) { theDocuments.status = 0; } else { theDocuments.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                documentsFactory.update(theDocuments)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theDocuments) {
            if (theDocuments.status == 1) { theDocuments.status = 0; } else { theDocuments.status = 1; }
            documentsFactory.updateStatus({'id': theDocuments.id, 'status': theDocuments.status})
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

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                documents_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            documentsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    documents_query($scope.current_page, $scope.per_page);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
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

                documentsFactory.sort(dataSort).success(function() {
                    notification("success", "The documents has been sortable.");
                    documents_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            documents_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        documents_query($scope.page, $scope.per_page);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
            if (_.find($scope.courses, ['id', $scope.documents_data.courses_id]) == undefined) {
                $scope.documents_data.courses_id = null;
            }
        })

        if (!angular.isUndefined($routeParams.id)) {
            documentsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.documents_data = data;
                $scope.mode = "Edit";
            })
        }


        $scope.changeFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/documents');
            } else {
                $location.path('documents');
            }
        }


        $scope.submitDocuments = function (theDocuments, nextAction) {
            functionsFactory.clearError(angular.element('.documents-frm'));
            theDocuments.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                documentsFactory.update(theDocuments)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theDocuments.courses_id ? $location.path('courses/'+ theDocuments.courses_id +'/documents') : $location.path('documents'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.documents-frm'));
                    });
            } else {
                documentsFactory.create(theDocuments)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('documents/'+ data.createdId +'/edit').search({}); break;
                                default                 : theDocuments.courses_id ? $location.path('courses/'+ theDocuments.courses_id +'/documents').search({}) : $location.path('documents').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.documents-frm'));
                    });
            }
        }

        $scope.deleteDocuments = function (theDocuments) {
            var id = theDocuments.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                documentsFactory.delete(theDocuments).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        //notification
        var notification = function (status, alert) {
            if (status == "success") {
                var n = noty({
                    text: '<div class="alert alert-success"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else {
                var n = noty({
                    text: '<div class="alert alert-danger"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            }
        }

    }]);
