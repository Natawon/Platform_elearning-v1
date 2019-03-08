'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('questionnairesCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', 'questionnairesFactory', 'questionnaire_packsFactory', '$timeout', 'pluginsService', 'functionsFactory', 'settingsFactory',
    function ($scope, $sce, $routeParams, $location, $route, $filter, questionnairesFactory, questionnaire_packsFactory, $timeout, pluginsService, functionsFactory, settingsFactory) {

        $scope.questionnaire_choices = {};
        $scope.questionnaires = {};
        $scope.questionnaires_data = {
            "condition_type": null
        };
        $scope.selected_questionnaire_packs = {};

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.type = [
            {'label': 'Single Answer', id:1},
            {'label': 'Multiple Answer', id:2},
            // {'label': 'Open Text', id:3}
        ];

        $scope.defaultConditionTypes = [
            { "value": null, "title": "-- เลือก --" },
            { "value": "level", "title": "ระดับความรู้" },
            { "value": "code", "title": "รหัสหลักสูตร" }
        ];

        $scope.dataConditionList = null;
        $scope.levelConditionList = ['L1', 'L2', 'L3'];

        $scope.hints = {
            "condition_type": 'เงื่อนไขที่เป็นไปได้จะแบ่งตามประเภทเงื่อนไขการเชื่อมโยงกลุ่มหลักสูตร เช่น <br><br><ul class=""><li>ถ้าประเภทเงื่อนไข คือ <strong>ระดับความรู้</strong> ค่าที่เป็นไปได้คือ <strong>L1</strong>, <strong>L2</strong>, <strong>L3</strong></li><li>ถ้าประเภทเงื่อนไข คือ <strong>รหัสหลักสูตร</strong> ค่าที่เป็นไปได้คือ <strong>WM</strong>, <strong>FD</strong>, <strong>EQ</strong>, <strong>DR</strong>, <strong>MF</strong></li></ul>',
        };

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.questionnaires = resp.data;
            for (var i = 0; i < $scope.questionnaires.length; i++) {
                var newQuestionnairesModifyDatetime = new Date($scope.questionnaires[i].modify_datetime).toISOString();
                $scope.questionnaires[i].modify_datetime = $filter('date')(newQuestionnairesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.questionnaires[i].no = (resp.from + i);
            }
            set_pagination(resp);
        };

        var questionnaires_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_questionnaire_packs)) {
                $scope.selected_questionnaire_packs = {id: $routeParams.selected_questionnaire_packs};
                var query_questionnaire_packs = $scope.selected_questionnaire_packs;
                var query = questionnaire_packsFactory.questionnaires(query_questionnaire_packs, query_string);
            } else {
                var query = questionnairesFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theQuestionnaires, forceUpdate) {
            theQuestionnaires.admin_id = $scope.admin.id;
            if (theQuestionnaires.status == 1) { theQuestionnaires.status = 0; } else { theQuestionnaires.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                questionnairesFactory.update(theQuestionnaires)
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

        $scope.updateStatus = function(theQuestionnaires) {
            if (theQuestionnaires.status == 1) { theQuestionnaires.status = 0; } else { theQuestionnaires.status = 1; }
            questionnairesFactory.updateStatus({'id': theQuestionnaires.id, 'status': theQuestionnaires.status})
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
                questionnaires_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            questionnairesFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    questionnaires_query($scope.current_page, $scope.per_page);
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

                questionnairesFactory.sort(dataSort).success(function() {
                    notification("success", "The questionnaires has been sortable.");
                    questionnaires_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            questionnaires_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        questionnaires_query($scope.page, $scope.per_page);

        questionnaire_packsFactory.all().success(function (data) {
            $scope.questionnaire_packs = data;
        })

        $scope.$on('$routeChangeSuccess', function() {
            $scope.questionnaires_data.questionnaire_packs_id = parseInt($routeParams.questionnaire_packsID);
            $scope.questionnaires_data.questionnaire_choices = [];
        });

        if (!angular.isUndefined($routeParams.id)) {
            questionnairesFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.questionnaires_data = data;
                $scope.mode = "Edit";
                $timeout(function () {
                    pluginsService.inputTags();
                }, 1);
            })
        }

        $scope.changeFilter = function () {
            if ($scope.selected_questionnaire_packs) {
                $location.path('questionnaire_packs/' + $scope.selected_questionnaire_packs + '/questionnaires');
            } else {
                $location.path('questionnaires');
            }
        };

        // $scope.changeCondition = function() {
        //     $scope.dataConditionList = $scope.levelConditionList;
        //     $timeout(function() {
        //         pluginsService.inputTags();
        //     }, 1000);
        // };

        $scope.addAnswer = function(theQuestionnaires) {
            $scope.questionnaires_data.questionnaire_choices.push({
                "questionnaires_id": theQuestionnaires.id,
                "condition_type": null
            })
            $timeout(function () {
                pluginsService.inputSelect();
                pluginsService.inputTags();
            }, 1);
        };

        $scope.deleteAnswer = function(theAnswer, theQuestionnaires) {
            if (theAnswer.id === undefined) {
                var index = $scope.questionnaires_data.questionnaire_choices.indexOf(theAnswer);
                $scope.questionnaires_data.questionnaire_choices.splice(index, 1);
            } else {
                var id = theAnswer.id;
                var alert = confirm("Are you sure to delete questionnaire_choices " + theAnswer.questionnaire_choices + " ?");
                if(alert == true) {
                    questionnairesFactory.delete_questionnaire_choices(theAnswer)
                        .success(function(data) {
                            if(data.is_error == false){
                                $route.reload();
                            }
                            if(data.is_error == true){
                                notification("error",data.message);
                            }
                        })
                        .error(function() {
                            notification("error"," No Access-Control-Allow-Origin");
                        });
                }
            }

        }

        $scope.submitQuestionnaires = function (theQuestionnaires, nextAction, isForceChange) {
            functionsFactory.clearError(angular.element('.questionnaires-frm'));
            theQuestionnaires.admin_id = $scope.admin.id;

            if (isForceChange === true) {
                theQuestionnaires.forceChange = true;
            }

            if ($scope.mode == "Edit") {
                questionnairesFactory.update(theQuestionnaires)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theQuestionnaires.questionnaire_packs_id ? $location.path('questionnaire_packs/'+ theQuestionnaires.questionnaire_packs_id +'/questionnaires') : $location.path('questionnaires'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questionnaires-frm'));
                    });
            } else {
                questionnairesFactory.create(theQuestionnaires)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('questionnaires/'+ data.createdId +'/edit'); break;
                                default                 : theQuestionnaires.questionnaire_packs_id ? $location.path('questionnaire_packs/'+ theQuestionnaires.questionnaire_packs_id +'/questionnaires') : $location.path('questionnaires'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questionnaires-frm'));
                    });
            }
        }

        $scope.deleteQuestionnaires = function (theQuestionnaires) {
            var id = theQuestionnaires.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                questionnairesFactory.delete(theQuestionnaires).success(function (data) {
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
