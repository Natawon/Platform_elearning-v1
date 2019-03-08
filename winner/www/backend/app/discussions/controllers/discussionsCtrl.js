'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('discussionsCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$interval', '$httpParamSerializer', 'discussionsFactory', 'coursesFactory', 'groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, $interval, $httpParamSerializer, discussionsFactory, coursesFactory, groupsFactory, functionsFactory, settingsFactory) {

        $scope.discussions = {};
        $scope.discussions_data = {};
        $scope.selected_courses = {};
        $scope.selected_groups = {};

        var promiseInterval;
        $scope.is_realtime = false;

        $scope.mode = "Create";

        $scope.base_discussions_file = settingsFactory.getURL('base_discussions_file');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'create_datetime';
        $scope.sorting_direction = 'desc';
        $scope.keyword = "";

        $scope.filters = {
            "is_unread": 0
        };

        if (!angular.isUndefined($routeParams.courses_id)) {
            $scope.discussions_data.courses_id = parseInt($routeParams.courses_id);
        }

        var $routeChangeStartUnbind = $rootScope.$on('$routeChangeStart', function(event, newUrl) {
            $interval.cancel(promiseInterval);
        });

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.discussions = resp.data;
            for (var i = 0; i < $scope.discussions.length; i++) {
                var newDiscussionsModifyDatetime = new Date($scope.discussions[i].modify_datetime).toISOString();
                $scope.discussions[i].modify_datetime = $filter('date')(newDiscussionsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.discussions[i].no = (resp.from + i);

                if ($scope.discussions[i].is_read == 0) {
                    $scope.discussions[i].unread = true;
                }

                var replyUnread = _.find($scope.discussions[i].replies, ['is_read', 0]);
                if (replyUnread !== undefined) {
                    $scope.discussions[i].unread = true;
                }

                for (var j = 0; j < $scope.discussions[i].replies.length; j++) {
                    var subReplyUnread = _.find($scope.discussions[i].replies[j].replies, ['is_read', 0]);
                    if (subReplyUnread !== undefined) {
                        $scope.discussions[i].unread = true;
                    }
                }
            }
            set_pagination(resp);
        };

        var discussions_query = function (page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction+filters;
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.discussions(query_courses, query_string);
            } else {
                var query = discussionsFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theDiscussions, forceUpdate) {
            theDiscussions.admin_id = $scope.admin.id;
            if (theDiscussions.status == 1) { theDiscussions.status = 0; } else { theDiscussions.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                discussionsFactory.update(theDiscussions)
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

        $scope.toggleIsReject = function (theDiscussions, forceUpdate) {
            theDiscussions.admin_id = $scope.admin.id;
            if (theDiscussions.is_reject == 1) { theDiscussions.is_reject = 0; } else { theDiscussions.is_reject = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                discussionsFactory.update(theDiscussions)
                    .success(function (data) {
                        if (data.is_error == false) {
                            discussionsFactory.get({id: theDiscussions.id}).success(function (data) {
                                $scope.discussions_data.reject_by = data.reject_by;
                                $scope.discussions_data.reject_datetime = data.reject_datetime;
                            });

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

        $scope.toggleIsPublic = function (theDiscussions, forceUpdate) {
            theDiscussions.admin_id = $scope.admin.id;
            if (theDiscussions.is_public == 1) { theDiscussions.is_public = 0; } else { theDiscussions.is_public = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                discussionsFactory.update(theDiscussions)
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

        $scope.toggleIsSentInstructor = function (theDiscussions, forceUpdate) {
            theDiscussions.admin_id = $scope.admin.id;
            if (theDiscussions.is_sent_instructor == 1) { theDiscussions.is_sent_instructor = 0; } else { theDiscussions.is_sent_instructor = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                discussionsFactory.update(theDiscussions)
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

        $scope.updateStatus = function(theDiscussions) {
            if (theDiscussions.status == 1) { theDiscussions.status = 0; } else { theDiscussions.status = 1; }
            discussionsFactory.updateStatus({'id': theDiscussions.id, 'status': theDiscussions.status})
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

        $scope.updateIsReject = function(theDiscussions) {
            if (theDiscussions.is_reject == 1) {
                theDiscussions.is_reject = 0;
                theDiscussions.reject_remark = null;
                theDiscussions.reject_datetime = null;
                theDiscussions.reject_by = null;
            } else {
                theDiscussions.is_reject = 1;
            }
            discussionsFactory.updateIsReject({'id': theDiscussions.id, 'is_reject': theDiscussions.is_reject, 'reject_remark': theDiscussions.reject_remark})
                .success(function(data) {
                    if (data.is_error == false) {
                        angular.element('#rejectRemarkModal').modal('hide');
                        notification("success",data.message);
                    } else {
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.toggleRejectModal = function(theDiscussions) {
            $scope.reject_discussion_data = theDiscussions;

            if (theDiscussions.is_reject != 1) {
                angular.element('#rejectRemarkModal').modal('show');
            } else {
                $scope.updateIsReject($scope.reject_discussion_data);
            }

            return false;
        };

        $scope.toggleIsUnread = function() {
            if ($scope.filters.is_unread == 1) { $scope.filters.is_unread = 0; } else { $scope.filters.is_unread = 1; }
            discussions_query($scope.current_page, $scope.per_page);
        };

        $scope.toggleRealtime = function() {
            if ($scope.is_realtime) { $scope.is_realtime = false; } else { $scope.is_realtime = true; }
        };

        $scope.$watch('is_realtime', function (new_value, old_value) {
            if (new_value != old_value) {
                if (new_value) {
                    promiseInterval = $interval(function() {
                        discussions_query($scope.current_page, $scope.per_page);
                    }, 3000);
                } else {
                    $interval.cancel(promiseInterval);
                }
            }
        });

        $scope.viewRejectRemarkModal = function(theDiscussions) {
            discussionsFactory.get({id: theDiscussions.id}).success(function (data) {
                $scope.show_reject_discussion_data = data;
                angular.element('#viewRejectRemarkModal').modal('show');
            });
        };

        $('#rejectRemarkModal').on('hidden.bs.modal', function (e) {
            if ($scope.reject_discussion_data.is_reject != 1) {
                angular.element('#isRejectOnOffSwitch'+$scope.reject_discussion_data.id).prop('checked', false);
            }
        });

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                discussions_query(new_page, $scope.per_page);
            }
        });

        $scope.$watch('discussions_data.courses_id', function (new_value, old_value) {
            if (new_value !== undefined && new_value !== null) {
                $scope.changeFilterCourses();
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            discussionsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    discussions_query($scope.current_page, $scope.per_page);
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

                discussionsFactory.sort(dataSort).success(function() {
                    notification("success", "The discussions has been sortable.");
                    discussions_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            discussions_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        discussions_query($scope.page, $scope.per_page);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
            if (_.find($scope.courses, ['id', $scope.discussions_data.courses_id]) == undefined) {
                $scope.discussions_data.courses_id = null;
            }
        });

        groupsFactory.all().success(function(data) {
            $scope.groups = data;
            $scope.groups_list = data;

            $timeout(function() {
                $scope.$watch('level_groups_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                    }
                });
            }, 500);

            if ($scope.groups_list.length == 1) {
                $scope.filters_discussion.groups_id = $scope.groups_list[0].id;
            }
        });

        if (!angular.isUndefined($routeParams.id)) {
            discussionsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.discussions_data = data;
                $scope.mode = "Edit";
            })
        }

        $scope.changeFilterCourses = function () {
            discussionsFactory.allExcept($scope.discussions_data).success(function(data) {
                $scope.allExcepts = data;
                if (_.find($scope.allExcepts, ['id', $scope.discussions_data.parent_id]) == undefined) {
                    $scope.discussions_data.parent_id = null;
                }
            })
        }

        $scope.changeFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/discussions');
            } else {
                $location.path('discussions');
            }
        }


        $scope.submitDiscussions = function (theDiscussions, nextAction) {
            functionsFactory.clearError(angular.element('.discussions-frm'));
            theDiscussions.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                discussionsFactory.update(theDiscussions)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theDiscussions.courses_id ? $location.path('courses/'+ theDiscussions.courses_id +'/discussions') : $location.path('discussions'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.discussions-frm'));
                    });
            } else {
                discussionsFactory.create(theDiscussions)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('discussions/'+ data.createdId +'/edit').search({}); break;
                                default                 : theDiscussions.courses_id ? $location.path('courses/'+ theDiscussions.courses_id +'/discussions').search({}) : $location.path('discussions').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.discussions-frm'));
                    });
            }
        }

        $scope.deleteDiscussions = function (theDiscussions) {
            var id = theDiscussions.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                discussionsFactory.delete(theDiscussions).success(function (data) {
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
