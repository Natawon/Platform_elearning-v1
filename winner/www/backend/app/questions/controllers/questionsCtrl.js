'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('questionsCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', 'questionsFactory', 'quizFactory', '$timeout', 'functionsFactory', 'settingsFactory',
    function ($scope, $sce, $routeParams, $location, $route, $filter, questionsFactory, quizFactory, $timeout, functionsFactory, settingsFactory) {

        $scope.answer = {};
        $scope.questions = {};
        $scope.questions_data = {};
        $scope.selected_quiz = {};

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
            {'label': 'Open Text', id:3}
        ];

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.questions = resp.data;
            for (var i = 0; i < $scope.questions.length; i++) {
                var newQuestionsModifyDatetime = new Date($scope.questions[i].modify_datetime).toISOString();
                $scope.questions[i].modify_datetime = $filter('date')(newQuestionsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.questions[i].no = (resp.from + i);
            }
            set_pagination(resp);
        };

        var questions_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_quiz)) {
                $scope.selected_quiz = {id: $routeParams.selected_quiz};
                var query_quiz = $scope.selected_quiz;
                var query = quizFactory.questions(query_quiz, query_string);
            } else {
                var query = questionsFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theQuestions, forceUpdate) {
            theQuestions.admin_id = $scope.admin.id;
            if (theQuestions.status == 1) { theQuestions.status = 0; } else { theQuestions.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                questionsFactory.update(theQuestions)
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

        $scope.updateStatus = function(theQuestions) {
            if (theQuestions.status == 1) { theQuestions.status = 0; } else { theQuestions.status = 1; }
            questionsFactory.updateStatus({'id': theQuestions.id, 'status': theQuestions.status})
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
                questions_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            questionsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    questions_query($scope.current_page, $scope.per_page);
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

                questionsFactory.sort(dataSort).success(function() {
                    notification("success", "The questions has been sortable.");
                    questions_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            questions_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        questions_query($scope.page, $scope.per_page);

        quizFactory.all().success(function (data) {
            $scope.quiz = data;
        })

        $scope.$on('$routeChangeSuccess', function() {
            $scope.questions_data.quiz_id = parseInt($routeParams.quizID);
            $scope.questions_data.answer = [];
        });

        if (!angular.isUndefined($routeParams.id)) {
            questionsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.questions_data = data;
                $scope.mode = "Edit";
                $timeout(function () {
                    $scope.checkType();
                }, 1);
            })
        }

        $scope.changeFilter = function () {
            if ($scope.selected_quiz) {
                $location.path('quiz/' + $scope.selected_quiz + '/questions');
            } else {
                $location.path('questions');
            }
        }

        $scope.addAnswer = function(theQuestions) {
            $scope.questions_data.answer.push({
                "questions_id": theQuestions.id
            })
            $timeout(function () {
                $scope.checkType();
            }, 1);
        };

        $scope.checkType = function () {
            if($scope.questions_data.type == '1'){
                $('div#answer').show();
                $('div#correct').show();
                $('div#answer-plus').show();
                $('div#answer-header').show();
                $('label#answer-header-correct').show();
            }
            if($scope.questions_data.type == '2'){
                $('div#answer').show();
                $('div#correct').show();
                $('div#answer-plus').show();
                $('div#answer-header').show();
                $('label#answer-header-correct').show();
            }
            if($scope.questions_data.type == '3'){
                $('div#answer').hide();
                $('div#correct').hide();
                $('div#answer-plus').hide();
                $('div#answer-header').hide();
                $('label#answer-header-correct').hide();
            }
        }

        $scope.deleteAnswer = function(theAnswer, theQuestions) {
            if (theAnswer.id === undefined) {
                var index = $scope.questions_data.answer.indexOf(theAnswer);
                $scope.questions_data.answer.splice(index, 1);
            } else {
                var id = theAnswer.id;
                var alert = confirm("Are you sure to delete answer " + theAnswer.answer + " ?");
                if(alert == true) {
                    questionsFactory.delete_answer(theAnswer)
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

        $scope.submitQuestions = function (theQuestions, nextAction) {
            functionsFactory.clearError(angular.element('.questions-frm'));
            theQuestions.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                questionsFactory.update(theQuestions)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theQuestions.quiz_id ? $location.path('quiz/'+ theQuestions.quiz_id +'/questions') : $location.path('questions'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questions-frm'));
                    });
            } else {
                questionsFactory.create(theQuestions)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('questions/'+ data.createdId +'/edit'); break;
                                default                 : theQuestions.quiz_id ? $location.path('quiz/'+ theQuestions.quiz_id +'/questions') : $location.path('questions'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.questions-frm'));
                    });
            }
        }

        $scope.deleteQuestions = function (theQuestions) {
            var id = theQuestions.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                questionsFactory.delete(theQuestions).success(function (data) {
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
