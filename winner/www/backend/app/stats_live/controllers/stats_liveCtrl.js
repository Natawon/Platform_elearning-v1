'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('stats_liveCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', 'stats_liveFactory', 'groupsFactory', 'sub_groupsFactory', 'level_groupsFactory', 'coursesFactory', 'topicsFactory', 'classroomsFactory', 'membersFactory', 'settingsFactory', 'pluginsService',
        function ($scope, $sce, $routeParams, $location, $route, $filter, $timeout, stats_liveFactory, groupsFactory, sub_groupsFactory, level_groupsFactory, coursesFactory, topicsFactory, classroomsFactory, membersFactory, settingsFactory, pluginsService) {


        var timeoutCurrentPage;

        $scope.stats_live = {};
        $scope.stats_live_data = {};
        // $scope.selected_courses = {};
        // $scope.selected_groups = {};
        // $scope.selected_sub_groups = {};
        $scope.selected_level_groups = {};
        $scope.stats_live_enroll = {
            avg_duration: '-',
            most_enter_class: '-'
        }

        $scope.selected_tab = '';

        $scope.level_groups = [];
        $scope.topics = [];

        $scope.mode = "Create";

        // StatsLive Lists
        $scope.defaultOptions = {
            "max_size": 5,
            "page": 1,
            "per_page": 30,
            "current_page": 1,
            "sorting_order": 'id',
            "sorting_direction": 'desc',
            "total": 0,
            "last_page": 0
        };

        ///Add on datepicker dateFormat
        $("#from_date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#to_date").datepicker({
            dateFormat: "yy-mm-dd"
        });

        $("#from_time").timepicker({
            isRTL: $('body').hasClass('rtl') ? true : false,
            timeFormat: 'HH:mm',
            controlType: 'select',
            timeInput: true,
            oneLine: true,
            showButtonPanel: false
        });

        $("#to_time").timepicker({
            isRTL: $('body').hasClass('rtl') ? true : false,
            timeFormat: 'HH:mm',
            controlType: 'select',
            timeInput: true,
            oneLine: true,
            showButtonPanel: false
        });

        var set_pagination = function(pagination_data) {
            $scope.defaultOptions.total = pagination_data.total;
            $scope.defaultOptions.last_page = pagination_data.last_page;
            $scope.defaultOptions.current_page = pagination_data.current_page;
            $scope.defaultOptions.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            // $scope.stats_live = resp.data;
            $scope.stats_live = resp.data.filter(function (el) { return el != null; });
            for (var i = 0; i < $scope.stats_live.length; i++) {
                $scope.stats_live[i].no = (resp.from + i);
                // var newStatsLiveEnterDatetime = new Date($scope.stats_live[i].enter_datetime).toISOString();
                // $scope.stats_live[i].enter_datetime = $filter('date')(newStatsLiveEnterDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
            pluginsService.tableResponsive();
        };

        var stats_live_query = function (page, per_page, filters, search, status) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.defaultOptions.sorting_order + "&order_direction=" + $scope.defaultOptions.sorting_direction+"&"+filters+"&search="+search+"&status="+status ;
            var query = stats_liveFactory.getStatsLive(query_string);
            query.success(success_callback);
        };

        $scope.$watch('defaultOptions.current_page', function(new_page, old_page) {
            if (timeoutCurrentPage) {
                $timeout.cancel(timeoutCurrentPage);
            }

            timeoutCurrentPage = $timeout(function() {
                if (new_page != old_page) {
                    var filters = angular.element('.frm-filter').serialize();
                    stats_live_query(new_page, $scope.defaultOptions.per_page, filters, '', '');
                }
            }, 100);
        });

        coursesFactory.level_public().success(function (data) {
            $scope.courses = data;
        });

        classroomsFactory.all().success(function (data) {
            $scope.classrooms = data;
        });

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
        });

        $scope.$watch('selected_groups', function () {
            if (!angular.isUndefined($scope.selected_groups)) {
                groupsFactory.sub_groups({id:$scope.selected_groups}).success(function(data) {
                    $scope.sub_groups = data;
                });
            }
        });
        

        $scope.$watch('selected_sub_groups', function () {
            if (!angular.isUndefined($scope.selected_sub_groups)) {
                sub_groupsFactory.level_groups({id:$scope.selected_sub_groups}).success(function(data) {
                    for (var i = 0; i < data.owner.length; i++) {
                        $scope.level_groups.push({id: data.owner[i].id, title: data.owner[i].title});
                    }
                    for (var i = 0; i < data.access.length; i++) {
                        $scope.level_groups.push({id: data.access[i].id, title: data.access[i].title});
                    }
                });
            }
        });

        // Info
        var stats_live_info_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveInfo(query_string);
            query.success(function(resp) {
                $scope.stats_live_info = resp;
            });
        };

        // Enroll
        var stats_live_enroll_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveEnroll(query_string);
            query.success(function(resp) {
                $scope.stats_live_enroll = resp;
                pluginsService.animateNumber();
            });
        };

        // StatsLive Logs
        $scope.stats_live_logs = {};
        $scope.stats_live_logs_data = {};
        var stats_live_logs_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveLogs(query_string);
            query.success(function(resp) {
                $scope.stats_live_logs = resp.data;

                if (resp.type === "hour") {
                    $scope.stats_live_logs_hour = [];
                    var countHour = 0;
                    var checkHour = "";

                    for (var i = 0; i < $scope.stats_live_logs.length; i++) {
                        var hour = $scope.stats_live_logs[i][0].substr(0, 2);

                        if (checkHour !== hour) {
                            if (i > 0) {
                                $scope.stats_live_logs_hour.push([
                                    checkHour+":00",
                                    countHour
                                ]);
                            }

                            countHour = $scope.stats_live_logs[i][1];
                            checkHour = hour;
                        } else {
                            countHour += $scope.stats_live_logs[i][1];
                            if (i == ($scope.stats_live_logs.length-1)) {
                                $scope.stats_live_logs_hour.push([
                                    checkHour+":00",
                                    countHour
                                ]);
                            }
                        }
                    }
                    $scope.stats_live_logs_data = $scope.stats_live_logs_hour;
                } else {
                    $scope.stats_live_logs_data = $scope.stats_live_logs;
                }

                setTimeout(function () {
                    stats_liveFactory.stockCharts(resp.data_chart);
                }, 200);
            });
        };

        // StatsLive Device Chart
        $scope.stats_live_device = {};
        var stats_live_device_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveDevice(query_string);
            query.success(function(resp) {
                $scope.stats_live_device = resp;

                if(angular.isUndefined(resp.all_ios)){ resp.all_ios = 0; }
                if(angular.isUndefined(resp.all_android)){ resp.all_android = 0; }
                if(angular.isUndefined(resp.all_windows)){ resp.all_windows = 0; }
                if(angular.isUndefined(resp.all_osx)){ resp.all_osx = 0; }
                if(angular.isUndefined(resp.all_linux)){ resp.all_linux = 0; }

                $scope.allMobiles = resp.all_mobiles;
                $scope.allDesktops = resp.all_desktops;
                stats_liveFactory.mobileChart(resp.all_ios, resp.all_android);
                stats_liveFactory.desktopChart(resp.all_windows, resp.all_osx, resp.all_linux);
            });
        };


        // StatsLive Countries
        $scope.stats_live_countries = {};
        var stats_live_countries_query = function (filters, isReInit) {
            isReInit = false
            $scope.stats_live_countries_map = [];
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveCountries(query_string);
            query.success(function(resp) {
                $scope.stats_live_countries = resp;

                for (var i = 0; i < $scope.stats_live_countries.length; i++) {
                    $scope.stats_live_countries_map.push({
                        "code": $scope.stats_live_countries[i].isoCode,
                        "name": $scope.stats_live_countries[i].country,
                        "value": parseInt($scope.stats_live_countries[i].total_views),
                        "color": "#de4c4f"
                    });
                }

                if (isReInit){
                    stats_liveFactory.reinit($scope.stats_live_countries_map);
                } else {
                    stats_liveFactory.init($scope.stats_live_countries_map);
                }

                stats_liveFactory.setHeights();
            });
        };

        // StatsLive State
        $scope.stats_live_states = {};
        var stats_live_states_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveStates(query_string);
            query.success(function(resp) {
                $scope.stats_live_states = resp;
            });
        };

        // Learning
        var stats_live_learning_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveLearning(query_string);
            query.success(function(resp) {
                $scope.stats_live_learning = resp;

                if(angular.isUndefined(resp.not_learning)){ resp.not_learning = 0; }
                if(angular.isUndefined(resp.learning_not_pass)){ resp.learning_not_pass = 0; }
                if(angular.isUndefined(resp.learning_pass_not_exam)){ resp.learning_pass_not_exam = 0; }
                if(angular.isUndefined(resp.exam_not_pass)){ resp.exam_not_pass = 0; }
                if(angular.isUndefined(resp.exam_pass)){ resp.exam_pass = 0; }
                if(angular.isUndefined(resp.learning_pass)){ resp.learning_pass = 0; }
                
                stats_liveFactory.learningChart(resp.quiz_process, resp.not_learning, resp.learning_not_pass, resp.learning_pass_not_exam, resp.exam_not_pass, resp.exam_pass, resp.learning_pass);
                if(resp.quiz_process){
                    stats_liveFactory.passChart(resp.not_certificate, resp.certificate);
                }
                    stats_liveFactory.notPassChart(resp.quiz_process, resp.not_learning, resp.learning_not_pass, resp.learning_pass_not_exam, resp.exam_not_pass);
                pluginsService.animateNumber();
            });
        };

        // Courses
        $scope.stats_live_courses = {};
        var stats_live_courses_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveCourses(query_string);
            query.success(function(resp) {
                $scope.stats_live_courses = resp;
            });
        };

        // Quiz
        $scope.stats_live_quiz = {};
        var stats_live_quiz_query = function (filters) {
            var query_string = "&"+filters;
            var query = stats_liveFactory.getStatsLiveQuiz(query_string);
            query.success(function(resp) {
                $scope.stats_live_quiz = resp;

                if(angular.isUndefined(resp.exam.exam_pass)){ resp.exam.exam_pass = 0; }
                if(angular.isUndefined(resp.exam.exam_not_pass)){ resp.exam.exam_not_pass = 0; }
                if(angular.isUndefined(resp.pre_test.count)){ resp.pre_test.count = 0; }
                if(angular.isUndefined(resp.compare.over)){ resp.compare.over = 0; }
                
                stats_liveFactory.ExamChart(resp.exam.exam_pass, resp.exam.exam_not_pass);
                stats_liveFactory.PrePostChart(resp.pre_test.count, resp.post_test.count);
                stats_liveFactory.CompareChart(resp.compare.over, resp.compare.under);
                pluginsService.animateNumber();
            });
        };

        $scope.enrollExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats_live/export/enroll?"+filters;
            window.open(url,"_self");

        };

        $scope.quizExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats_live/export/quiz?"+filters;
            window.open(url,"_self");

        };

        $scope.courseExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats_live/export/course?"+filters;
            window.open(url,"_self");

        };

            $scope.questionsExport = function (quiz_id) {
                console.log(quiz_id);
                var filters = angular.element('.frm-filter').serialize();
                var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats_live/export/questions?quiz_id="+quiz_id+"&"+filters;
                window.open(url,"_self");

            };

        $scope.changeSearch = function () {
            var filters = angular.element('.frm-filter').serialize();
            stats_live_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, $scope.defaultOptions.search, '');
        }

        $scope.logFilter = function (status) {
            var filters = angular.element('.frm-filter').serialize();
            if($scope.defaultOptions.search === undefined){
                stats_live_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', status);
            }else{
                stats_live_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, $scope.defaultOptions.search, status);
            }
        };

        //Tab Active
        $scope.tabLearning = function () {
            if ($scope.selected_courses) {
                stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_live_info_query(filters);
            stats_live_learning_query(filters);
            stats_live_courses_query(filters);

            $scope.selected_tab = 'learning';
        };

        $scope.tabQuiz = function () {
            if ($scope.selected_courses){
                stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_live_info_query(filters);
            stats_live_quiz_query(filters);

            $scope.selected_tab = 'quiz';
        };

        $scope.tabEnroll = function () {
            if ($scope.selected_courses){
                stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_live_info_query(filters);
            stats_live_enroll_query(filters);
            stats_live_device_query(filters);
            stats_live_countries_query(filters, true);
            stats_live_states_query(filters);
            stats_live_logs_query(filters);

            $scope.selected_tab = 'enroll';
        };

        $scope.tabData = function () {
            if ($scope.selected_courses){
                stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_live_info_query(filters);
            stats_live_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', '');

            $scope.selected_tab = 'data';
        };
        //End Tab Active

        // Filters
        var timeout;
        $scope.changeFilter = function () {
            $scope.clearTimeoutFilters();
            $('#btnFiltersSubmit').button('loading');
            timeout = setTimeout(function() {

            var filters = angular.element('.frm-filter').serialize();

            // if($scope.selected_tab === '' || $scope.selected_tab === 'learning'){
            //     if ($scope.selected_courses){
            //         stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
            //             $scope.courses_data = data;
            //         });
            //     }
            //     stats_live_info_query(filters);
            //     stats_live_learning_query(filters);
            //     stats_live_courses_query(filters);
            // }else if($scope.selected_tab === 'quiz'){
            //     if ($scope.selected_courses){
            //         stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
            //             $scope.courses_data = data;
            //         });
            //     }
            //     stats_live_info_query(filters);
            //     stats_live_quiz_query(filters);
            // }else
            if($scope.selected_tab === '' || $scope.selected_tab === 'enroll'){
                if ($scope.selected_courses){
                    stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                        $scope.courses_data = data;
                    });
                }
                stats_live_info_query(filters);
                stats_live_enroll_query(filters);
                stats_live_device_query(filters);
                stats_live_countries_query(filters, true);
                stats_live_states_query(filters);
                stats_live_logs_query(filters);
            }else if($scope.selected_tab === 'data'){
                if ($scope.selected_courses){
                    stats_liveFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                        $scope.courses_data = data;
                    });
                }
                stats_live_info_query(filters);
                stats_live_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', '');
            }

                setTimeout(function () {
                    $('#btnFiltersSubmit').button('reset');
                }, 1500);
            }, 800);
        };

        $scope.clearTimeoutFilters = function () {
            clearTimeout(timeout);
        };

        $scope.clearFilters = function () {
            angular.element('.frm-filter')[0].reset();
            $scope.selected_courses = "";
            $scope.selected_groups = "";
            $scope.selected_sub_groups = "";
            $scope.selected_level_groups = "";
            $scope.selected_classrooms = "";
            $scope.selected_topics = "";
            angular.element('select[name=courses_id]').val('').trigger('change');

            $scope.changeFilter();
        };

        $scope.changeFilterCourses = function () {
            $scope.selected_topics = "";
            angular.element('select[name=topics_id]').val('').trigger('change');
            topicsFactory.topicsHasParents({id: $scope.selected_courses}).success(function (data) {
                $scope.topics = data;
            });
        }

        $scope.changeFilterTopics = function () {
            topicsFactory.get({id: $scope.selected_topics}).success(function (data) {
                $scope.topics_data = data;
                // !angular.isUndefined($scope.selected_groups)
                if ($scope.topics_data.live_start_datetime != null && $scope.topics_data.live_end_datetime != null) {
                    var newTopicsLiveStartDatetime = new Date($scope.topics_data.live_start_datetime).toISOString();
                    var newTopicsLiveEndDatetime = new Date($scope.topics_data.live_end_datetime).toISOString();

                    $scope.from_date = $filter('date')(newTopicsLiveStartDatetime, 'yyyy-MM-dd');
                    $scope.to_date = $filter('date')(newTopicsLiveEndDatetime, "yyyy-MM-dd");

                    $scope.from_time = $filter('date')(newTopicsLiveStartDatetime, "HH:mm");
                    $scope.to_time = $filter('date')(newTopicsLiveEndDatetime, "HH:mm");
                }
                // console.log($scope.topics_data.live_start_datetime);
            });
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

        $scope.tabChanged = function(){
            $scope.$apply();
        };


        }]);
