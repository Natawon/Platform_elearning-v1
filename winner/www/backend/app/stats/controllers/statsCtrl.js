'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('statsCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$interval', 'statsFactory', 'groupsFactory', 'sub_groupsFactory', 'level_groupsFactory', 'coursesFactory', 'classroomsFactory', 'membersFactory', 'settingsFactory', 'pluginsService',
        function ($scope, $sce, $routeParams, $location, $route, $filter, $timeout, $interval, statsFactory, groupsFactory, sub_groupsFactory, level_groupsFactory, coursesFactory, classroomsFactory, membersFactory, settingsFactory, pluginsService) {


        var timeoutCurrentPage;

        $scope.stats = {};
        $scope.stats_data = {};
        $scope.stats_data = {};
        // $scope.selected_courses = {};
        // $scope.selected_groups = {};
        // $scope.selected_sub_groups = {};
        $scope.selected_level_groups = {};

        $scope.selected_tab = '';

        $scope.level_groups = [];

        $scope.mode = "Create";

        // Stats Lists
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

        $scope.allTasks = {
            "stats_info_query": true,
            "stats_learning_query": true,
            "stats_courses_query": true,
            "stats_quiz_query": true,
            "stats_enroll_query": true,
            "stats_device_query": true,
            "stats_countries_query": true,
            "stats_states_query": true,
            "stats_logs_query": true,
            "stats_query": true
        };

        $scope.objInterval;

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
            $scope.stats = resp.data;
            $scope.allTasks.stats_query = true;
            for (var i = 0; i < $scope.stats.length; i++) {
                $scope.stats[i].no = (resp.from + i);
            }
            set_pagination(resp);
            pluginsService.tableResponsive();
        };

        var stats_query = function (page, per_page, filters, search, status) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.defaultOptions.sorting_order + "&order_direction=" + $scope.defaultOptions.sorting_direction+"&"+filters+"&search="+search+"&status="+status ;
            var query = statsFactory.getStats(query_string);
            query.success(success_callback).error(function(resp) {
                failedDataLoaded()
            });
        };

        $scope.$watch('defaultOptions.current_page', function(new_page, old_page) {
            if (timeoutCurrentPage) {
                $timeout.cancel(timeoutCurrentPage);
            }

            timeoutCurrentPage = $timeout(function() {
                if (new_page != old_page) {
                    var filters = angular.element('.frm-filter').serialize();
                    stats_query(new_page, $scope.defaultOptions.per_page, filters, '', '');
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
        var stats_info_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsInfo(query_string);
            query.success(function(resp) {
                $scope.stats_info = resp;
                $scope.allTasks.stats_info_query = true;
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Enroll
        var stats_enroll_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsEnroll(query_string);
            query.success(function(resp) {
                $scope.stats_enroll = resp;
                $scope.allTasks.stats_enroll_query = true;
                pluginsService.animateNumber();
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Stats Logs
        $scope.stats_logs = {};
        $scope.stats_logs_data = {};
        var stats_logs_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsLogs(query_string);
            query.success(function(resp) {
                $scope.stats_logs = resp.data;
                $scope.allTasks.stats_logs_query = true;

                if (resp.type === "hour") {
                    $scope.stats_logs_hour = [];
                    var countHour = 0;
                    var checkHour = "";

                    for (var i = 0; i < $scope.stats_logs.length; i++) {
                        var hour = $scope.stats_logs[i][0].substr(0, 2);

                        if (checkHour !== hour) {
                            if (i > 0) {
                                $scope.stats_logs_hour.push([
                                    checkHour+":00",
                                    countHour
                                ]);
                            }

                            countHour = $scope.stats_logs[i][1];
                            checkHour = hour;
                        } else {
                            countHour += $scope.stats_logs[i][1];
                            if (i == ($scope.stats_logs.length-1)) {
                                $scope.stats_logs_hour.push([
                                    checkHour+":00",
                                    countHour
                                ]);
                            }
                        }
                    }
                    $scope.stats_logs_data = $scope.stats_logs_hour;
                } else {
                    $scope.stats_logs_data = $scope.stats_logs;
                }

                setTimeout(function () {
                    statsFactory.stockCharts(resp.data_chart);
                }, 200);
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Stats Device Chart
        $scope.stats_device = {};
        var stats_device_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsDevice(query_string);
            query.success(function(resp) {
                $scope.stats_device = resp;
                $scope.allTasks.stats_device_query = true;

                if(angular.isUndefined(resp.all_ios)){ resp.all_ios = 0; }
                if(angular.isUndefined(resp.all_android)){ resp.all_android = 0; }
                if(angular.isUndefined(resp.all_windows)){ resp.all_windows = 0; }
                if(angular.isUndefined(resp.all_osx)){ resp.all_osx = 0; }
                if(angular.isUndefined(resp.all_linux)){ resp.all_linux = 0; }

                $scope.allMobiles = resp.all_mobiles;
                $scope.allDesktops = resp.all_desktops;
                statsFactory.mobileChart(resp.all_ios, resp.all_android);
                statsFactory.desktopChart(resp.all_windows, resp.all_osx, resp.all_linux);
            }).error(function(resp) {
                failedDataLoaded()
            });
        };


        // Stats Countries
        $scope.stats_countries = {};
        var stats_countries_query = function (filters, isReInit) {
            isReInit = false
            $scope.stats_countries_map = [];
            var query_string = "&"+filters;
            var query = statsFactory.getStatsCountries(query_string);
            query.success(function(resp) {
                $scope.stats_countries = resp;
                $scope.allTasks.stats_countries_query = true;

                for (var i = 0; i < $scope.stats_countries.length; i++) {
                    $scope.stats_countries_map.push({
                        "code": $scope.stats_countries[i].isoCode,
                        "name": $scope.stats_countries[i].country,
                        "value": parseInt($scope.stats_countries[i].total_views),
                        "color": "#de4c4f"
                    });
                }

                if (isReInit){
                    statsFactory.reinit($scope.stats_countries_map);
                } else {
                    statsFactory.init($scope.stats_countries_map);
                }

                statsFactory.setHeights();
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Stats State
        $scope.stats_states = {};
        var stats_states_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsStates(query_string);
            query.success(function(resp) {
                $scope.stats_states = resp;
                $scope.allTasks.stats_states_query = true;
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Learning
        var stats_learning_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsLearning(query_string);
            query.success(function(resp) {
                $scope.stats_learning = resp;
                $scope.allTasks.stats_learning_query = true;

                if(angular.isUndefined(resp.not_learning)){ resp.not_learning = 0; }
                if(angular.isUndefined(resp.learning_not_pass)){ resp.learning_not_pass = 0; }
                if(angular.isUndefined(resp.learning_pass_not_exam)){ resp.learning_pass_not_exam = 0; }
                if(angular.isUndefined(resp.exam_not_pass)){ resp.exam_not_pass = 0; }
                if(angular.isUndefined(resp.exam_pass)){ resp.exam_pass = 0; }
                if(angular.isUndefined(resp.learning_pass)){ resp.learning_pass = 0; }
                
                statsFactory.learningChart(resp.quiz_process, resp.not_learning, resp.learning_not_pass, resp.learning_pass_not_exam, resp.exam_not_pass, resp.exam_pass, resp.learning_pass);
                if(resp.quiz_process){
                    statsFactory.passChart(resp.not_certificate, resp.certificate);
                }
                    statsFactory.notPassChart(resp.quiz_process, resp.not_learning, resp.learning_not_pass, resp.learning_pass_not_exam, resp.exam_not_pass);
                pluginsService.animateNumber();
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Courses
        $scope.stats_courses = {};
        var stats_courses_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsCourses(query_string);
            query.success(function(resp) {
                $scope.stats_courses = resp;
                $scope.allTasks.stats_courses_query = true;
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        // Quiz
        $scope.stats_quiz = {};
        var stats_quiz_query = function (filters) {
            var query_string = "&"+filters;
            var query = statsFactory.getStatsQuiz(query_string);
            query.success(function(resp) {
                $scope.stats_quiz = resp;
                $scope.allTasks.stats_quiz_query = true;

                if(angular.isUndefined(resp.exam.exam_pass)){ resp.exam.exam_pass = 0; }
                if(angular.isUndefined(resp.exam.exam_not_pass)){ resp.exam.exam_not_pass = 0; }
                if(angular.isUndefined(resp.pre_test.count)){ resp.pre_test.count = 0; }
                if(angular.isUndefined(resp.compare.over)){ resp.compare.over = 0; }
                
                statsFactory.ExamChart(resp.exam.exam_pass, resp.exam.exam_not_pass);
                statsFactory.PrePostChart(resp.pre_test.count, resp.post_test.count);
                statsFactory.CompareChart(resp.compare.over, resp.compare.under);
                pluginsService.animateNumber();
            }).error(function(resp) {
                failedDataLoaded()
            });
        };

        $scope.enrollExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats/export/enroll?"+filters;
            window.open(url,"_blank");

        };

        $scope.quizExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats/export/quiz?"+filters;
            window.open(url,"_blank");

        };

        $scope.courseExport = function () {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats/export/course?"+filters;
            window.open(url,"_blank");

        };

        $scope.questionsExport = function (quiz_id) {
            var filters = angular.element('.frm-filter').serialize();
            var url = settingsFactory.getConstant("BASE_SERVICE_URL") + "stats/export/questions?quiz_id="+quiz_id+"&"+filters;
            window.open(url,"_blank");

        };

        $scope.changeSearch = function () {
            var filters = angular.element('.frm-filter').serialize();
            stats_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, $scope.defaultOptions.search, '');
        }

        $scope.logFilter = function (status) {
            var filters = angular.element('.frm-filter').serialize();
            if($scope.defaultOptions.search === undefined){
                stats_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', status);
            }else{
                stats_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, $scope.defaultOptions.search, status);
            }
        };

        //Tab Active
        $scope.tabLearning = function () {
            startDataLoad();
            if ($scope.selected_courses) {
                statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_info_query(filters);
            stats_learning_query(filters);
            stats_courses_query(filters);

            $scope.selected_tab = 'learning';

            checkDataLoaded(['stats_info_query', 'stats_learning_query', 'stats_courses_query']);
        };

        $scope.tabQuiz = function () {
            startDataLoad();
            if ($scope.selected_courses){
                statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_info_query(filters);
            stats_quiz_query(filters);

            $scope.selected_tab = 'quiz';

            checkDataLoaded(['stats_info_query', 'stats_quiz_query']);
        };

        $scope.tabEnroll = function () {
            startDataLoad();
            if ($scope.selected_courses){
                statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_info_query(filters);
            stats_enroll_query(filters);
            stats_device_query(filters);
            stats_countries_query(filters, true);
            stats_states_query(filters);
            stats_logs_query(filters);

            $scope.selected_tab = 'enroll';

            checkDataLoaded(['stats_info_query', 'stats_enroll_query', 'stats_device_query', 'stats_countries_query', 'stats_states_query', 'stats_logs_query']);
        };

        $scope.tabData = function () {
            startDataLoad();
            if ($scope.selected_courses){
                statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                    $scope.courses_data = data;
                });
            }

            var filters = angular.element('.frm-filter').serialize();
            stats_info_query(filters);
            stats_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', '');

            $scope.selected_tab = 'data';

            checkDataLoaded(['stats_info_query', 'stats_query']);
        };
        //End Tab Active

        // Filters
        var timeout;
        $scope.changeFilter = function () {
            $scope.clearTimeoutFilters();
            startDataLoad();
            timeout = setTimeout(function() {

                var filters = angular.element('.frm-filter').serialize();

                if ($scope.selected_tab === '' || $scope.selected_tab === 'learning') {
                    if ($scope.selected_courses){
                        statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                            $scope.courses_data = data;
                        });
                    }

                    stats_info_query(filters);
                    stats_learning_query(filters);
                    stats_courses_query(filters);

                    checkDataLoaded(['stats_info_query', 'stats_learning_query', 'stats_courses_query']);
                } else if ($scope.selected_tab === 'quiz') {
                    if ($scope.selected_courses){
                        statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                            $scope.courses_data = data;
                        });
                    }
                    stats_info_query(filters);
                    stats_quiz_query(filters);

                    checkDataLoaded(['stats_info_query', 'stats_quiz_query']);
                } else if ($scope.selected_tab === 'enroll') {
                    if ($scope.selected_courses){
                        statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                            $scope.courses_data = data;
                        });
                    }
                    stats_info_query(filters);
                    stats_enroll_query(filters);
                    stats_device_query(filters);
                    stats_countries_query(filters, true);
                    stats_states_query(filters);
                    stats_logs_query(filters);

                    checkDataLoaded(['stats_info_query', 'stats_enroll_query', 'stats_device_query', 'stats_countries_query', 'stats_states_query', 'stats_logs_query']);
                } else if ($scope.selected_tab === 'data') {
                    if ($scope.selected_courses) {
                        statsFactory.getCourse({id: $scope.selected_courses}).success(function (data) {
                            $scope.courses_data = data;
                        });
                    }
                    stats_info_query(filters);
                    stats_query($scope.defaultOptions.page, $scope.defaultOptions.per_page, filters, '', '');

                    checkDataLoaded(['stats_info_query', 'stats_query']);
                }

                // setTimeout(function () {
                //     $('#btnFiltersSubmit').button('reset');
                // }, 1500);
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
            angular.element('select[name=courses_id]').val('').trigger('change');

            $scope.changeFilter();
        };

        var clearDataLoaded = function() {
            angular.forEach($scope.allTasks, function(value, key) {
                $scope.allTasks[key] = false;
            });
        };

        var startDataLoad = function() {
            clearDataLoaded();
            angular.element('#btnFiltersSubmit').button('loading');
            angular.element('.loading-backdrop').fadeIn('fast');
        };

        var checkDataLoaded = function(tasks) {
            if (!angular.isArray(tasks)) {
                return false;
            }

            var isTasksLoaded = false;
            $scope.objInterval = $interval(function() {
                isTasksLoaded = _.every(tasks, function(task) {
                    return $scope.allTasks[task] === true;
                });

                if (isTasksLoaded) {
                    $interval.cancel($scope.objInterval);
                    angular.element('#btnFiltersSubmit').button('reset');
                    angular.element('.loading-backdrop').fadeOut('fast');
                }
            }, 1000);
        };

        var failedDataLoaded = function() {
            notification("error", "ข้อมูลมีขนาดใหญ่เกินไป กรุณาระบุเงื่อนไขการค้นหาเพิ่มเติม เช่น หลักสูตร, กลุ่ม, ช่วงวันและเวลา เป็นต้น", false);
            $interval.cancel($scope.objInterval);
            angular.element('#btnFiltersSubmit').button('reset');
            angular.element('.loading-backdrop').fadeOut('fast');
        };

        //notification
        var notification = function (status, alert, timeout) {
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
                    timeout: timeout !== undefined ? timeout : 3000
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
                    timeout: timeout !== undefined ? timeout : 3000
                });
            }
        }

        $scope.tabChanged = function(){
            $scope.$apply();
        };


        }]);
