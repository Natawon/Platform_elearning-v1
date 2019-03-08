'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('topicsCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', 'topicsFactory', 'coursesFactory', 'livestreamFactory', 'quizFactory', 'functionsFactory', 'pluginsService', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, topicsFactory, coursesFactory, livestreamFactory, quizFactory, functionsFactory, pluginsService, settingsFactory) {

        $scope.topics = {};
        $scope.topics_data = {
            "start_time": "00:00:00",
            "end_time": "00:00:00",
            "state": 'vod'
        };
        $scope.selected_courses = {};
        $scope.selected_quiz = {};

        $scope.collapsedItems = [];

        $scope.mode = "Create";

        $scope.base_courses_thumbnail = settingsFactory.getURL('base_courses_thumbnail');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.state = [
            { "value": 'live', "title": "Live" },
            { "value": 'vod', "title": "VOD" }
        ];

        $scope.loadingParent = false;

        $scope.live_start_datetime_error = false;
        $scope.live_start_datetime_error_message = '';

        $scope.live_end_datetime_error = false;
        $scope.live_end_datetime_error_message = '';

        $scope.format_stop_stream = {
            vod_now: {
                theme: 'theme-primary',
                icon: 'fa fa-video-camera',
                title: 'On Demand Now',
                description: 'เผยแพร่วีดีโอย้อนหลังทันที โดยระบบจะนำวีดีโอที่บันทึกเสร็จแล้ว (ต้นฉบับ) ขึ้นแสดงก่อน',
                value: 'vod_now'
            },
            vod_later: {
                theme: 'theme-warning',
                icon: 'fa fa-clock-o',
                title: 'On Demand Later',
                description: 'หากคุณยังไม่ต้องการเผยแพร่วีดีโอย้อนหลังในตอนนี้',
                value: 'vod_later'
            },
            end_live: {
                theme: 'theme-default',
                icon: 'fa fa-stop',
                title: 'End Live',
                description: 'ไม่เผยแพร่วีดีโอย้อนหลัง และหัวข้อนี้ต่อสาธารณะ',
                value: 'end_live'
            }
        };

        ///Add on datepicker dateFormat
        $('#live_start_datetime').datetimepicker({
            prevText: '<i class="fa fa-angle-left"></i>',
            nextText: '<i class="fa fa-angle-right"></i>',
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            controlType: 'select',
            oneLine: true,
            timeInput: true,
            stepMinute: 1,
            minDate: new Date(),
        });

        $('#live_end_datetime').datetimepicker({
            prevText: '<i class="fa fa-angle-left"></i>',
            nextText: '<i class="fa fa-angle-right"></i>',
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            controlType: 'select',
            oneLine: true,
            timeInput: true,
            stepMinute: 1,
            minDate: new Date(),
        });

        var checkDateTimeLive = function(action_from, start_datetime, end_datetime) {
            // Check with Current Datetime
            var message = '';
            var newDate = new Date();
            var currDate = newDate.getFullYear() + '-'
                            + (newDate.getMonth()+1)  + "-"
                            + newDate.getDate() + " "
                            + newDate.getHours() + ":"
                            + newDate.getMinutes() + ":"
                            + newDate.getSeconds();

            currDate = Date.parse(currDate);
            start_datetime = Date.parse(start_datetime);
            end_datetime = Date.parse(end_datetime);

            if (action_from == 'start') {
                if (start_datetime < currDate) {
                    $scope.live_start_datetime_error = true;
                    message = '- กรุณาเลือกวันเวลาที่มากกว่า <strong>"ปัจจุบัน"</strong>';
                } else {
                    if (start_datetime >= end_datetime && end_datetime > currDate) {
                        $scope.live_start_datetime_error = true;
                        message = '- กรุณาเลือกวันเวลาที่น้อยกว่า <strong>"เวลาสิ้นสุดถ่ายทอดสด"</strong>';
                    } else {
                        $scope.live_start_datetime_error = false;
                        if (end_datetime > currDate) {
                            $scope.live_end_datetime_error = false;

                            angular.element('#live_end_datetime_error_message').addClass('m-0');
                            angular.element('#live_end_datetime').closest('div').removeClass('has-error');
                            angular.element('#live_end_datetime_error_message').html('');
                        }
                    }
                }

                if ($scope.live_start_datetime_error == true) {
                    angular.element('#live_start_datetime').closest('div').addClass('has-error');
                    angular.element('#live_start_datetime_error_message').removeClass('m-0');
                } else {
                    angular.element('#live_start_datetime_error_message').addClass('m-0');
                    angular.element('#live_start_datetime').closest('div').removeClass('has-error');
                }

                angular.element('#live_start_datetime_error_message').html(message);
            } else if (action_from == 'end') {
                if (end_datetime < currDate) {
                    $scope.live_end_datetime_error = true;
                    message = '- กรุณาเลือกวันเวลาที่มากกว่า <strong>"ปัจจุบัน"</strong>';
                } else {
                    if (start_datetime >= end_datetime && start_datetime > currDate) {
                        $scope.live_end_datetime_error = true;
                        message = '- กรุณาเลือกวันเวลาที่มากกว่า <strong>"เวลาเริ่มถ่ายทอดสด"</strong>';
                    } else {
                        $scope.live_end_datetime_error = false;
                        if (start_datetime > currDate) {
                            $scope.live_start_datetime_error = false;

                            angular.element('#live_start_datetime_error_message').addClass('m-0');
                            angular.element('#live_start_datetime').closest('div').removeClass('has-error');
                            angular.element('#live_start_datetime_error_message').html('');
                        }
                    }
                }

                if ($scope.live_end_datetime_error == true) {
                    angular.element('#live_end_datetime').closest('div').addClass('has-error');
                    angular.element('#live_end_datetime_error_message').removeClass('m-0');
                } else {
                    angular.element('#live_end_datetime_error_message').addClass('m-0');
                    angular.element('#live_end_datetime').closest('div').removeClass('has-error');
                }

                angular.element('#live_end_datetime_error_message').html(message);
            }
        }


        $('#live_start_datetime').on('change', function(event) {
            event.preventDefault();

            if ($scope.mode == 'Edit') {
                return false;
            }

            var val = $(this).val();
            $scope.topics_data.live_start_datetime = val;

            angular.element('#live_start_datetime_error_message').html('<span class="text-muted p-0"><i class="fa fa-spinner fa-spin p-0" style="position: static; left: 0 !important; width: auto; height: 14px; line-height: 14px;"></i> <span style="padding-left: 5px;">กำลังตรวจสอบ</span></span>');
            angular.element('#live_start_datetime_error_message').removeClass('m-0');

            $timeout(function() {
                checkDateTimeLive('start', $scope.topics_data.live_start_datetime, $scope.topics_data.live_end_datetime);
            }, 500);
        });

        $('#live_end_datetime').on('change', function(event) {
            event.preventDefault();

            if ($scope.mode == 'Edit') {
                return false;
            }

            var val = $(this).val();
            $scope.topics_data.live_end_datetime = val;

            angular.element('#live_end_datetime_error_message').html('<span class="text-muted p-0"><i class="fa fa-spinner fa-spin p-0" style="position: static; left: 0 !important; width: auto; height: 14px; line-height: 14px;"></i> <span style="padding-left: 5px;">กำลังตรวจสอบ</span></span>');
            angular.element('#live_end_datetime_error_message').removeClass('m-0');

            $timeout(function() {
                checkDateTimeLive('end', $scope.topics_data.live_start_datetime, $scope.topics_data.live_end_datetime);
                return false;
            }, 500);
        });

        if (!angular.isUndefined($routeParams.courses_id)) {
            $scope.topics_data.courses_id = parseInt($routeParams.courses_id);
        }

        if (!angular.isUndefined($routeParams.parent)) {
            $scope.topics_data.parent = parseInt($routeParams.parent);
        }

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.topics = resp.data;
            for (var i = 0; i < $scope.topics.length; i++) {
                var newTopicsModifyDatetime = new Date($scope.topics[i].modify_datetime).toISOString();
                $scope.topics[i].modify_datetime = $filter('date')(newTopicsModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $timeout(function() {
                _.map($scope.collapsedItems, function(n) {
                    angular.element(n).addClass('in');
                });
            }, 500);
        };

        var topics_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.topics(query_courses, query_string);
            } else {
                var query = topicsFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleFree = function (theTopics) {
            theTopics.admin_id = $scope.admin.id;
            if (theTopics.free == 1) { theTopics.free = 0; } else { theTopics.free = 1; }
            if ($scope.mode == "Edit") {
                topicsFactory.update(theTopics)
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

        $scope.toggleStatus = function (theTopics, forceUpdate) {
            theTopics.admin_id = $scope.admin.id;
            if (theTopics.status == 1) { theTopics.status = 0; } else { theTopics.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                functionsFactory.clearError(angular.element('.topics-frm'));
                topicsFactory.update(theTopics)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        if (theTopics.status == 1) { theTopics.status = 0; } else { theTopics.status = 1; }
                        if (angular.isObject(data) && !functionsFactory.isEmpty(data)) {
                            functionsFactory.handleError(data, angular.element('.topics-frm'));
                        } else {
                            notification("error", settingsFactory.getConstant('server_error'));
                        }
                    });
            }
        };

        $scope.updateStatus = function(theTopics) {
            if (theTopics.status == 1) { theTopics.status = 0; } else { theTopics.status = 1; }
            if (theTopics.status == 1 && theTopics.end_time == '00:00:00' && theTopics.state === 'vod') {
                theTopics.status = 0;
                $timeout(function() {
                    $('#statusOnOffSwitch'+theTopics.id).prop('checked', false)
                    notification("error", "This topic has invalid end time.");
                }, 200);
                return false;
            } else {
                topicsFactory.updateStatus({'id': theTopics.id, 'status': theTopics.status})
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
            }
        };

        $scope.toggleAutoQuiz = function (theTopics, forceUpdate) {
            theTopics.admin_id = $scope.admin.id;
            if (theTopics.auto_quiz == 1) { theTopics.auto_quiz = 0; } else { theTopics.auto_quiz = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                topicsFactory.update(theTopics)
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
                topics_query(new_page, $scope.per_page);
            }
        });

        $scope.$watch('topics_data.courses_id', function (new_value, old_value) {
            // angular.element('#parent').val('').trigger('change');
            if (new_value !== undefined && new_value !== null) {
                $scope.changeFilterCourses();

                $scope.loadingParent = true;
                $('#parent').prev().find('.select2-chosen').html('<i class="fa fa-spinner fa-spin p-0"></i> Loading...');
                $timeout(function() {
                    pluginsService.inputSelect();
                    $timeout(function() {
                        $scope.loadingParent = false;
                        // $('#parent').prev().find('.select2-chosen').html('-- เลือกหัวข้อ --');
                        // angular.element('#courses_id').val(new_value).trigger('change');
                    }, 500);
                }, 500);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            topicsFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    topics_query($scope.current_page, $scope.per_page);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                    $scope.enableSortable();
                });
        };

        $scope.checkCollapsed = function(target) {
            var matchedIndex = $scope.collapsedItems.indexOf(target);

            if (matchedIndex > -1) {
                $scope.collapsedItems.splice(matchedIndex, 1);
            } else {
                $scope.collapsedItems.push(target);
            }
        };

        $scope.createSubTopic = function (theTopics) {
            $location.path('topics/create').search({'courses_id': theTopics.courses_id, 'parent': theTopics.id});
        };

        // $scope.sortableOptions = {
        //     stop: function(e, ui) {
        //         var $sorted = ui.item;

        //         var $prev = $sorted.prev();
        //         var $next = $sorted.next();

        //         var dataSort = {
        //             id: $sorted.data('id')
        //         };

        //         if ($prev.length > 0) {
        //             dataSort.type = 'moveAfter';
        //             dataSort.positionEntityId = $prev.data('id');
        //         } else if ($next.length > 0) {
        //             dataSort.type = 'moveBefore';
        //             dataSort.positionEntityId = $next.data('id');
        //         } else {
        //             notification("error"," Something wrong!");
        //         }

        //         topicsFactory.sort(dataSort).success(function() {
        //             notification("success", "The topics has been sortable.");
        //             topics_query($scope.current_page, $scope.per_page);
        //         });
        //     }
        // };

        $scope.sortableOptions = {
            containment: "document",
            items: "> tbody",
            // handle: ".move",
            tolerance: "pointer",
            cursor: "move",
            opacity: 0.7,
            // revert: 300,
            // delay: 150,
            // placeholder: "movable-placeholder",
            // start: function(e, ui) {
            //     ui.placeholder.height(ui.helper.outerHeight());
            // },
            stop: function(e, ui) {
                var $sorted = ui.item;

                var $prev = $sorted.prev();
                var $next = $sorted.next();

                var dataSort = {
                    id: $sorted.data('id')
                };

                if ($prev.length > 0 && $prev.data('id') !== undefined) {
                    dataSort.type = 'moveAfter';
                    dataSort.positionEntityId = $prev.data('id');
                } else if ($next.length > 0 && $next.data('id') !== undefined) {
                    dataSort.type = 'moveBefore';
                    dataSort.positionEntityId = $next.data('id');
                } else {
                    notification("error"," Something wrong!");
                }

                topicsFactory.sort(dataSort).success(function() {
                    notification("success", "The topics has been sortable.");
                    topics_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sortableGroupOptions = {
            items: "> tr",
            tolerance: "pointer",
            containment: "parent",
            opacity: 0.7,
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

                topicsFactory.sort(dataSort).success(function() {
                    notification("success", "The topics has been sortable.");
                    topics_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            topics_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        topics_query($scope.page, $scope.per_page);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
            if (_.find($scope.courses, ['id', $scope.topics_data.courses_id]) == undefined) {
                $scope.topics_data.courses_id = null;
            }
        });

        setTimeout(function () {
            if ($('.courses-loading-data').length) {
                $(".courses-loading-data").select2({
                    placeholder: "-- Search for a course --",
                    minimumInputLength: 3,
                    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                        url: settingsFactory.get('courses') + '/all',
                        dataType: 'json',
                        quietMillis: 250,
                        data: function (term, page) {
                            return {
                                search: term, // search term
                            };
                        },
                        results: function (data, page) { // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to alter the remote JSON data
                            return { results: data };
                        },
                        cache: true
                    },
                    initSelection: function (element, callback) {
                        // the input tag has a value attribute preloaded that points to a preselected repository's id
                        // this function resolves that id attribute to an object that select2 can render
                        // using its formatResult renderer - that way the repository name is shown preselected
                        var id = element.val();
                        if (id !== "") {
                            $.ajax(settingsFactory.get('courses') + '/' + id, {
                                dataType: "json"
                            }).done(function (data) {
                                data.title = data.code + ' - ' + data.title;
                                callback(data);
                            });
                        }
                    },
                    formatResult: function (data) {
                        var markup = '<div class="row"><div class="col-md-12">'+data.title+'</div></div>';
                        return markup;
                    }, // omitted for brevity, see the source of this page
                    formatSelection: function (data) {
                        return data.title;
                    },  // omitted for brevity, see the source of this page
                    dropdownCssClass: "form-white", // apply css that makes the dropdown taller
                    escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
                });
            }
        }, 1000);

        if (!angular.isUndefined($routeParams.id)) {
            topicsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.topics_data = data;
                $scope.mode = "Edit";

                quizFactory.quiz2topic({id:$scope.topics_data.courses_id}).success(function (data) {
                    $scope.quizs = data;
                })

                $scope.topics_data.dir_name = "topics/T" + $scope.topics_data.id;
            })
        }

        $scope.changeFilterCourses = function () {
            topicsFactory.parents($scope.topics_data.courses_id).success(function(data) {
                $scope.parents = data;
                if (_.find($scope.parents, ['id', $scope.topics_data.parent]) == undefined) {
                    $scope.topics_data.parent = null;
                }
            })
        }

        $scope.changeFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/topics');
            } else {
                $location.path('topics');
            }
        }

        $scope.submitTopics = function (theTopics, nextAction) {
            // functionsFactory.clearError(angular.element('.topics-frm'));

            if (theTopics.parent === undefined || theTopics.parent === null) {
                theTopics.start_time = null;
                theTopics.end_time = null;
            }

            theTopics.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                functionsFactory.clearError(angular.element('.topics-frm'));
                topicsFactory.update(theTopics)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : theTopics.courses_id ? $location.path('courses/'+ theTopics.courses_id +'/topics') : $location.path('topics'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                            angular.element(button_id).button('reset');
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.topics-frm'));
                        angular.element(button_id).button('reset');
                    });
            } else {
                if ($scope.live_start_datetime_error || $scope.live_end_datetime_error) {
                    notification("error", 'ไม่สามารถบันทึกข้อมูลได้ กรุณากรอกข้อมูลให้ถูกต้อง');
                    return false;
                }

                functionsFactory.clearError(angular.element('.topics-frm'));

                angular.element('.btn-save').prop('disabled', true);

                var button_id;
                switch (nextAction) {
                    case 'add_another'      : button_id = '#btn-add-another'; break;
                    case 'continue_editing' : button_id = '#btn-continue-editing'; break;
                    default                 : button_id = '#btn-save'; break;
                }

                angular.element(button_id).prop('disabled', false);
                angular.element(button_id).button('loading');

                topicsFactory.create(theTopics)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                            angular.element(button_id).button('reset');
                            angular.element('.btn-save').prop('disabled', false);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('topics/'+ data.createdId +'/edit').search({}); break;
                                default                 : theTopics.courses_id ? $location.path('courses/'+ theTopics.courses_id +'/topics').search({}) : $location.path('topics').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                            $timeout(function() {
                                angular.element(button_id).button('reset');
                                angular.element('.btn-save').prop('disabled', false);
                            }, 500);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.topics-frm'));
                        $timeout(function() {
                            angular.element(button_id).button('reset');
                            angular.element('.btn-save').prop('disabled', false);
                        }, 500);
                    });

            }
        }

        $scope.toggleStreamingStatus = function(theTopics) {
            theTopics.admin_id = $scope.admin.id;
            if (theTopics.streaming_status == 1) { theTopics.streaming_status = 0; } else { theTopics.streaming_status = 1; }
            if ($scope.mode == "Edit") {
                topicsFactory.update(theTopics)
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

        $scope.toggleIsShowSubtitles = function(theTopics) {
            theTopics.admin_id = $scope.admin.id;
            if (theTopics.is_show_subtitles == 1) { theTopics.is_show_subtitles = 0; } else { theTopics.is_show_subtitles = 1; }
            if ($scope.mode == "Edit") {
                topicsFactory.update(theTopics)
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

        $scope.deleteTopics = function (theTopics) {
            var id = theTopics.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                topicsFactory.delete(theTopics).success(function (data) {
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

        $scope.PartStreamingReview = function (theTopics) {
            topicsFactory.get({id: theTopics.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $scope.OriginalStreamingReview = function (theTopics) {
            topicsFactory.get({id: theTopics.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $scope.CutStreamingReview = function (theTopics) {
            topicsFactory.get({id: theTopics.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url_cut,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $scope.generateLiveUrl = function(theTopics) {
            topicsFactory.generateLiveUrl(theTopics)
                .success(function (data) {
                    $scope.topics_data.streaming_url = data.live_url;
                })
                .error(function () {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        angular.element('#modal-basic').on('hidden.bs.modal', function () {
            $timeout(function() {
                var playerInstance = jwplayer("player");
                playerInstance.stop();
            }, 500);
        })

        // $timeout(function(){
        //     $(function () {
        //         $('[data-toggle="tooltip"]').tooltip();
        //     });
        // }, 1000);

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
