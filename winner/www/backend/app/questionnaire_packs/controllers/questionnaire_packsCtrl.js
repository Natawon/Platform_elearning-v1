'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('questionnaire_packsCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', 'questionnaire_packsFactory', 'groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, questionnaire_packsFactory, groupsFactory, functionsFactory, settingsFactory) {

        $scope.questionnaire_packs = {};
        $scope.questionnaire_packs_data = {};
        $scope.selected_groups = {};

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.type = [
            {'label': 'Pre Test', id:1},
            {'label': 'QuestionnairePacks', id:2},
            {'label': 'Exam', id:3},
            {'label': 'Post Test', id:4},
            {'label': 'Survey', id:5}
        ];

        if (!angular.isUndefined($routeParams.groups_id)) {
            $scope.questionnaire_packs_data.groups_id = parseInt($routeParams.groups_id);
        }

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.questionnaire_packs = resp.data;
            for (var i = 0; i < $scope.questionnaire_packs.length; i++) {
                var newQuestionnairePacksModifyDatetime = new Date($scope.questionnaire_packs[i].modify_datetime).toISOString();
                $scope.questionnaire_packs[i].modify_datetime = $filter('date')(newQuestionnairePacksModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.questionnaire_packs[i].no = (resp.from + i);
                $scope.questionnaire_packs[i].questionnaires_count = $scope.questionnaire_packs[i].questionnaires.length;
            }
            set_pagination(resp);
        };

        var questionnaire_packs_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_groups)) {
                $scope.selected_groups = {id: $routeParams.selected_groups};
                var query_groups = $scope.selected_groups;
                var query = groupsFactory.questionnaire_packs(query_groups, query_string);
            } else {
                var query = questionnaire_packsFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theQuestionnairePacks, forceUpdate) {
            theQuestionnairePacks.admin_id = $scope.admin.id;
            if (theQuestionnairePacks.status == 1) { theQuestionnairePacks.status = 0; } else { theQuestionnairePacks.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                questionnaire_packsFactory.update(theQuestionnairePacks)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            if (forceUpdate === true) {
                                questionnaire_packs_query($scope.current_page, $scope.per_page);
                            }
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

        $scope.updateStatus = function(theQuestionnairePacks, isReload) {
            if (theQuestionnairePacks.status == 1) { theQuestionnairePacks.status = 0; } else { theQuestionnairePacks.status = 1; }
            questionnaire_packsFactory.updateStatus({'id': theQuestionnairePacks.id, 'status': theQuestionnairePacks.status})
                .success(function(data) {
                    if (data.is_error == false) {
                        notification("success",data.message);

                        if (isReload === true) {
                            questionnaire_packs_query($scope.current_page, $scope.per_page);
                        }
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
                questionnaire_packs_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            questionnaire_packsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    questionnaire_packs_query($scope.current_page, $scope.per_page);
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

                questionnaire_packsFactory.sort(dataSort).success(function() {
                    notification("success", "The questionnaire_packs has been sortable.");
                    questionnaire_packs_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            questionnaire_packs_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        questionnaire_packs_query($scope.page, $scope.per_page);

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            if (_.find($scope.groups, ['id', $scope.questionnaire_packs_data.groups_id]) == undefined) {
                $scope.questionnaire_packs_data.groups_id = null;
            }
        })

        if (!angular.isUndefined($routeParams.id)) {
            questionnaire_packsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.questionnaire_packs_data = data;
                $scope.mode = "Edit";
            })
        }


        $scope.changeFilter = function () {
            if ($scope.selected_groups) {
                $location.path('groups/' + $scope.selected_groups + '/questionnaire_packs');
            } else {
                $location.path('questionnaire_packs');
            }
        }

        $scope.submitQuestionnairePacks = function (theQuestionnairePacks, nextAction, isForceChange) {
            functionsFactory.clearError(angular.element('.questionnaire_packs-frm'));
            theQuestionnairePacks.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                if (isForceChange === true) {
                    theQuestionnairePacks.forceChange = true;
                }

                questionnaire_packsFactory.update(theQuestionnairePacks)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theQuestionnairePacks.groups_id ? $location.path('groups/'+ theQuestionnairePacks.groups_id +'/questionnaire_packs') : $location.path('questionnaire_packs'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questionnaire_packs-frm'));
                    });
            } else {
                questionnaire_packsFactory.create(theQuestionnairePacks)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('questionnaire_packs/'+ data.createdId +'/edit').search({}); break;
                                default                 : theQuestionnairePacks.groups_id ? $location.path('groups/'+ theQuestionnairePacks.groups_id +'/questionnaire_packs').search({}) : $location.path('questionnaire_packs').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questionnaire_packs-frm'));
                    });
            }
        }

        $scope.deleteQuestionnairePacks = function (theQuestionnairePacks) {
            var id = theQuestionnairePacks.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                questionnaire_packsFactory.delete(theQuestionnairePacks).success(function (data) {
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
