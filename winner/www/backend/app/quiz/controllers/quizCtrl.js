'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('quizCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', 'quizFactory', 'coursesFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, quizFactory, coursesFactory, functionsFactory, settingsFactory) {

        $scope.quiz = {};
        $scope.quiz_data = {};
        $scope.selected_courses = {};

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
            {'label': 'Quiz', id:2},
            {'label': 'Exam', id:3},
            {'label': 'Post Test', id:4},
            {'label': 'Survey', id:5}
        ];

        if (!angular.isUndefined($routeParams.courses_id)) {
            $scope.quiz_data.courses_id = parseInt($routeParams.courses_id);
        }

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.quiz = resp.data;
            for (var i = 0; i < $scope.quiz.length; i++) {
                var newQuizModifyDatetime = new Date($scope.quiz[i].modify_datetime).toISOString();
                $scope.quiz[i].modify_datetime = $filter('date')(newQuizModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.quiz[i].no = (resp.from + i);
                $scope.quiz[i].questions_count = $scope.quiz[i].questions.length;
            }
            set_pagination(resp);
        };

        var quiz_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.quiz(query_courses, query_string);
            } else {
                var query = quizFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.togglePass = function (theQuiz) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.pass == 1) { theQuiz.pass = 0; } else { theQuiz.pass = 1; }
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
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
        }

        $scope.toggleAnswer = function (theQuiz) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.answer == 1) { theQuiz.answer = 0; } else { theQuiz.answer = 1; }
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
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
        }

        $scope.toggleAnswerSubmit = function (theQuiz) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.answer_submit == 1) { theQuiz.answer_submit = 0; } else { theQuiz.answer_submit = 1; }
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
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
        }

        $scope.toggleStatus = function (theQuiz, forceUpdate) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.status == 1) { theQuiz.status = 0; } else { theQuiz.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                quizFactory.update(theQuiz)
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

        $scope.updateStatus = function(theQuiz) {
            if (theQuiz.status == 1) { theQuiz.status = 0; } else { theQuiz.status = 1; }
            quizFactory.updateStatus({'id': theQuiz.id, 'status': theQuiz.status})
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

        $scope.toggleRandomQuestions = function (theQuiz) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.random_questions == 1) { theQuiz.random_questions = 0; } else { theQuiz.random_questions = 1; }
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
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
        }

        $scope.toggleRandomAnswer = function (theQuiz) {
            theQuiz.admin_id = $scope.admin.id;
            if (theQuiz.random_answer == 1) { theQuiz.random_answer = 0; } else { theQuiz.random_answer = 1; }
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
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
        }

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                quiz_query(new_page, $scope.per_page);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            quizFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    quiz_query($scope.current_page, $scope.per_page);
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

                quizFactory.sort(dataSort).success(function() {
                    notification("success", "The quiz has been sortable.");
                    quiz_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            quiz_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        quiz_query($scope.page, $scope.per_page);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
            if (_.find($scope.courses, ['id', $scope.quiz_data.courses_id]) == undefined) {
                $scope.quiz_data.courses_id = null;
            }
        })

        if (!angular.isUndefined($routeParams.id)) {
            quizFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.quiz_data = data;
                $scope.mode = "Edit";
            })
        }


        $scope.changeFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/quiz');
            } else {
                $location.path('quiz');
            }
        }

        $scope.submitQuiz = function (theQuiz, nextAction) {
            functionsFactory.clearError(angular.element('.quiz-frm'));
            theQuiz.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                quizFactory.update(theQuiz)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theQuiz.courses_id ? $location.path('courses/'+ theQuiz.courses_id +'/quiz') : $location.path('quiz'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.quiz-frm'));
                    });
            } else {
                quizFactory.create(theQuiz)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('quiz/'+ data.createdId +'/edit').search({}); break;
                                default                 : theQuiz.courses_id ? $location.path('courses/'+ theQuiz.courses_id +'/quiz').search({}) : $location.path('quiz').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.quiz-frm'));
                    });
            }
        }

        $scope.deleteQuiz = function (theQuiz) {
            var id = theQuiz.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                quizFactory.delete(theQuiz).success(function (data) {
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
