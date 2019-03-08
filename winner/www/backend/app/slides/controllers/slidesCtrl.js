'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('slidesCtrl', ['$scope', '$rootScope', '$sce', '$timeout', '$routeParams', '$location', '$route', '$filter', 'slidesFactory', 'coursesFactory', 'topicsFactory', 'slidesTimesFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $timeout, $routeParams, $location, $route, $filter, slidesFactory, coursesFactory, topicsFactory, slidesTimesFactory, functionsFactory, settingsFactory) {

        $scope.slides = {};
        $scope.slides_data = {
            slides_times: []
        };

        $scope.slides_topics_data = {
            courses_id: "",
            slides_times: [],
            slides: {},
            topics: {
                id: ""
            }
        };

        $scope.sync_by_topics = {};
        $scope.selected_courses = {};
        $scope.selected_topics = {};

        $scope.parents_edit = [];

        $scope.mode = "Create";

        $scope.base_slides_picture = settingsFactory.getURL('base_slides_picture');
        $scope.base_slides_pdf = settingsFactory.getURL('base_slides_pdf');
        $scope.slides_data_convert = {
            files: []
        };

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.changeCoursesWithTopics = function () {
            $timeout(function() {
                // jwplayer("player").stop(true);
                angular.element('#player-topics').empty();
                $scope.setTopics();
                // $scope.slides_topics_data.topics.id = "";
                // angular.element('select[name=topics]').val('').trigger('change');
            }, 500);
        };

        if (!angular.isUndefined($routeParams.courses_id)) {
            $scope.slides_topics_data.courses_id = parseInt($routeParams.courses_id);
            $scope.changeCoursesWithTopics();
        }

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.slides = resp.data;
            for (var i = 0; i < $scope.slides.length; i++) {
                var firstTopic = _.first(_.sortBy($scope.slides[i].slides_times, ['topics_id']));
                var newSlidesModifyDatetime = new Date($scope.slides[i].modify_datetime).toISOString();
                $scope.slides[i].modify_datetime = $filter('date')(newSlidesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.slides[i].no = (resp.from + i);

                if ($scope.topics_id) {
                    $scope.slides[i].editTopic = $scope.topics_id;
                } else if (firstTopic !== undefined) {
                    $scope.slides[i].editTopic = firstTopic.topics_id;
                } else if ($scope.slides[i].courses.first_topic !== null && $scope.slides[i].courses.first_topic.id !== undefined) {
                    $scope.slides[i].editTopic = $scope.slides[i].courses.first_topic.id
                } else {
                    $scope.slides[i].editTopic = null;
                }
            }
            set_pagination(resp);
        };

        var slides_query = function (page, per_page, filter) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            /*if (!angular.isUndefined($routeParams.selected_courses) && !angular.isUndefined($routeParams.selected_topics)) {
                angular.element('.slide_active').show();
                $scope.selected_courses = {id: $routeParams.selected_courses};
                $scope.selected_topics = {id: $routeParams.selected_topics};
                var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction + "&courses_id=" + $scope.selected_courses.id + "&topics_id=" + $scope.selected_topics.id;
            } else */
            if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                topicsFactory.children($scope.selected_courses.id).success(function(data) {
                    $scope.parents = data;
                })
                var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction + "&courses_id=" + $scope.selected_courses.id + "&" + filter;
            }
            var query = slidesFactory.query(query_string);

            query.success(success_callback);
        };

        $scope.toggleStatus = function (theSlides, forceUpdate) {
            theSlides.admin_id = $scope.admin.id;
            if (theSlides.status == 1) { theSlides.status = 0; } else { theSlides.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate) {
                slidesFactory.update(theSlides)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        if ($scope.mode == 'Edit') {
                            notification("error", settingsFactory.getConstant('server_error'));
                        }
                    });
            }
        };

        $scope.updateStatus = function(theSlides) {
            if (theSlides.status == 1) { theSlides.status = 0; } else { theSlides.status = 1; }
            slidesFactory.updateStatus({'id': theSlides.id, 'status': theSlides.status})
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
                var filters = angular.element('.frm-filter').serialize();
                slides_query(new_page, $scope.per_page, filters);
                $scope.mode = "Create";
            }
        });

        /*$scope.$watch('slides_data.courses_id', function () {
            $scope.changeFilterCourses();
        });*/

        $scope.$watch('slides_data_convert.files', function (new_files, old_files) {
            if (new_files != old_files && $scope.slides_data_convert.files.length > 0) {
                $scope.slides_data_convert.admin_id = $scope.admin.id;
                $scope.slides_data_convert.courses_id = $scope.selected_courses.id;

                slidesFactory.convertCreate($scope.slides_data_convert)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                            setTimeout(function() {
                                $route.reload();
                            }, 500);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }

                        $scope.slides_data_convert.files = [];

                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                        $scope.slides_data_convert.files = [];
                    });
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            slidesFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    var filters = angular.element('.frm-filter').serialize();
                    slides_query($scope.current_page, $scope.per_page, filters);
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

                slidesFactory.sort(dataSort).success(function() {
                    notification("success", "The slides has been sortable.");
                    var filters = angular.element('.frm-filter').serialize();
                    slides_query($scope.current_page, $scope.per_page, filters);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            var filters = angular.element('.frm-filter').serialize();
            slides_query($scope.page, $scope.per_page, filters);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        var filters = angular.element('.frm-filter').serialize();
        slides_query($scope.page, $scope.per_page, filters);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
            if (_.find($scope.courses, ['id', $scope.slides_topics_data.courses_id]) == undefined) {
                $scope.slides_topics_data.courses_id = null;
            }

            $timeout(function() {
                angular.element('select#courses_id').trigger('change');
            }, 500);
        });

        if (!angular.isUndefined($routeParams.id)){
            slidesFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.mode = "Edit";
                $scope.slides_data = data;
                $scope.slides_topics_data.id = $scope.slides_data.id;
                $scope.slides_topics_data.courses_id = $scope.slides_data.courses_id;
                $scope.slides_topics_data.picture = $scope.slides_data.picture;
                $scope.sync_by_topics.picture = $scope.slides_data.picture;

                angular.element('#slides-courses').attr('disabled', '');
                angular.element('#slides-courses').removeClass('form-white');
                angular.element('#courses_id').attr('disabled', '');
                angular.element('#courses_id').removeClass('form-white');

                coursesFactory.slidesForSync({id: $scope.slides_data.courses_id}).success(function (data_slidesForSync) {
                    $scope.slides_for_sync = data_slidesForSync;
                });

                coursesFactory.nextSlide({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                    $scope.nextSlides = data.id;
                });

                coursesFactory.previousSlides({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                    $scope.previousSlides = data.id;
                });

                if (data.courses.streaming_url) {
                    var playerInstance = jwplayer("player");
                    playerInstance.setup({
                        file: data.courses.streaming_url,
                        aspectratio: "16:9",
                        width: "100%",
                        autostart: "true"
                    });

                    playerInstance.onSeek(function(event) {
                        var offset = Math.floor(event.offset);
                        var sec_num = parseInt(offset, 10); // don't forget the second param
                        var hours   = Math.floor(sec_num / 3600);
                        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                        var seconds = sec_num - (hours * 3600) - (minutes * 60);
                        if (hours   < 10) {hours   = "0"+hours;}
                        if (minutes < 10) {minutes = "0"+minutes;}
                        if (seconds < 10) {seconds = "0"+seconds;}
                        $scope.slides_data.time = hours+':'+minutes+':'+seconds;
                        slidesFactory.get({id: $routeParams.id});
                    });
                }

                $scope.setTopics();
            })

        }

        angular.element('body').on('click', '#slides-tabs > .nav-tabs > li > a', function(event) {
            var currentTab = $(this).closest('li').attr('id');
            $scope.currentTab = $(this).closest('li').attr('id');
            if (currentTab === 'sync-topics-tab') {
                    jwplayer("player").stop(true);
            } else {
                if ($scope.mode == 'Edit') {
                    jwplayer("player-topics").stop(true);
                    jwplayer("player").play(true);
                }
            }
        });

        $scope.changeTopics = function () {
            if ($scope.slides_topics_data.topics.id == null) {
                // jwplayer("player-topics").stop(true);
                angular.element('#player-topics').empty();
                return true;
            }

            if ($scope.mode == 'Create') {
                var theTopics = {id: $scope.slides_topics_data.topics.id};
                topicsFactory.get(theTopics).success(function (data) {
                    // $scope.slides_topics_data = data;

                    var playerInstance_topics = jwplayer("player-topics");
                    playerInstance_topics.setup({
                        file: data.streaming_url_cut,
                        aspectratio: "16:9",
                        width: "100%",
                        autostart: "true"
                    });

                    playerInstance_topics.onSeek(function(event) {
                        var offset = Math.floor(event.offset);
                        var sec_num = parseInt(offset, 10); // don't forget the second param
                        var hours   = Math.floor(sec_num / 3600);
                        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                        var seconds = sec_num - (hours * 3600) - (minutes * 60);
                        if (hours   < 10) {hours   = "0"+hours;}
                        if (minutes < 10) {minutes = "0"+minutes;}
                        if (seconds < 10) {seconds = "0"+seconds;}
                        // $scope.slides_topics_data.time = hours+':'+minutes+':'+seconds;
                        // slidesFactory.get({id: $routeParams.id});
                    });
                });
            } else {

                var query_string = '&topics_id=' + $scope.slides_topics_data.topics.id + "&type=" + $scope.mode;
                var theSlides = {id: $scope.slides_data.id};

                slidesFactory.getByTopics(theSlides, query_string).success(function (data) {
                    $scope.slides_topics_data = data;
                    var playerInstance_topics = jwplayer("player-topics");
                    playerInstance_topics.setup({
                        file: $scope.slides_topics_data.topics.streaming_url_cut,
                        aspectratio: "16:9",
                        width: "100%",
                        autostart: "true"
                    });

                    playerInstance_topics.onSeek(function(event) {
                        var offset = Math.floor(event.offset);
                        var sec_num = parseInt(offset, 10); // don't forget the second param
                        var hours   = Math.floor(sec_num / 3600);
                        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                        var seconds = sec_num - (hours * 3600) - (minutes * 60);
                        if (hours   < 10) {hours   = "0"+hours;}
                        if (minutes < 10) {minutes = "0"+minutes;}
                        if (seconds < 10) {seconds = "0"+seconds;}
                        $scope.slides_topics_data.time = hours+':'+minutes+':'+seconds;
                        // slidesFactory.get({id: $routeParams.id});
                    });
                });
            }
        }

        $scope.changeCourses = function () {
            coursesFactory.get({id: $scope.slides_data.courses_id}).success(function (data) {

                if (data.streaming_url) {
                    var playerInstance = jwplayer("player");
                    playerInstance.setup({
                        file: data.streaming_url,
                        aspectratio: "16:9",
                        width: "100%",
                        autostart: "true"
                    });

                    playerInstance.onSeek(function(event) {
                        var offset = Math.floor(event.offset);
                        var sec_num = parseInt(offset, 10); // don't forget the second param
                        var hours   = Math.floor(sec_num / 3600);
                        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                        var seconds = sec_num - (hours * 3600) - (minutes * 60);
                        if (hours   < 10) {hours   = "0"+hours;}
                        if (minutes < 10) {minutes = "0"+minutes;}
                        if (seconds < 10) {seconds = "0"+seconds;}
                        // data.time = hours+':'+minutes+':'+seconds;
                        // slidesFactory.get({id: $routeParams.id});
                    });
                 }
            })
        }

        $scope.ReviewTimeSync = function (timeSync) {
            var hms = timeSync;
            var a = hms.split(':');
            var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);

            var playerInstance = jwplayer("player-topics");
            // console.log(seconds);
            playerInstance.seek(seconds);
            $('html, body').animate({
                scrollTop: ($("#player-topics").offset().top - 100)
            }, 500);
        };

        $scope.setTopics = function () {
            topicsFactory.children($scope.slides_topics_data.courses_id).success(function(data) {
                $scope.parents_edit = data;
                if (!angular.isUndefined($routeParams.topics_id)) {
                    $timeout(function() {
                        $scope.slides_topics_data.topics.id = parseInt($routeParams.topics_id);
                        angular.element('select[name=topics]').val($scope.slides_topics_data.topics.id).trigger('change');
                        // $scope.changeTopics();
                    }, 1000);
                }
                if ($scope.mode == 'Edit') {
                    // $scope.slides_topics_data.topics.id = $scope.parents_edit[0].id;
                }
            })
        };

        $scope.changeSlide = function (slide_id){
            $scope.slides_data = "";
            slidesFactory.get({id: slide_id}).success(function (data) {
                $scope.slides_data = data;
                $scope.slides_topics_data.id = $scope.slides_data.id;
                $scope.slides_topics_data.courses_id = $scope.slides_data.courses_id;
                $scope.slides_topics_data.picture = $scope.slides_data.picture;
                $scope.sync_by_topics.picture = $scope.slides_data.picture;

                angular.element('#slides-courses').attr('disabled', '');
                angular.element('#slides-courses').removeClass('form-white');
                angular.element('#courses_id').attr('disabled', '');
                angular.element('#courses_id').removeClass('form-white');

                coursesFactory.slidesForSync({id: $scope.slides_data.courses_id}).success(function (data_slidesForSync) {
                    $scope.slides_for_sync = data_slidesForSync;
                });

                coursesFactory.nextSlide({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                    $scope.nextSlides = data.id;
                });

                coursesFactory.previousSlides({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                    $scope.previousSlides = data.id;
                });

                var query_string = '&topics_id=' + $scope.slides_topics_data.topics.id + "&type=Edit";
                var theSlides = {id: $scope.slides_data.id};

                slidesFactory.getByTopics(theSlides, query_string).success(function (data) {
                    $scope.slides_topics_data = data;
                });

            });
        };

        $scope.changeFilterCourses = function () {
            if ($scope.selected_courses == null) {
                $location.path('slides');
            } else {
                if ($scope.selected_courses.id) {
                    $location.path('slides/' + $scope.selected_courses.id + '/courses');
                } else {
                    $location.path('slides/' + $scope.selected_courses + '/courses');
                }
            }
        }

        $scope.changeFilterTopics = function () {
            var filters = angular.element('.frm-filter').serialize();

            slides_query($scope.page, $scope.per_page, filters);
        }

        $scope.toggleReload = function () {
            var playerInstance = jwplayer("player");
            playerInstance.setup({
                file: $scope.slides_data.courses.streaming_url,
                aspectratio: "16:9",
                width: "100%",
                autostart: "true"
            });
        }

        $scope.createSlide = function() {
            var queryString = {};

            if ($scope.selected_courses.id !== undefined) {
                queryString.courses_id = $scope.selected_courses.id;
            }

            if ($scope.topics_id !== undefined) {
                queryString.topics_id = $scope.topics_id;
            }

            $location.path('slides/create').search(queryString);
        };

        $scope.submitSlides = function (theSlides, type, nextAction) {
            // console.log(theSlides);
            // return false;
            functionsFactory.clearError(angular.element('.slides-frm'));
            theSlides.type = type;
            theSlides.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {

                if (theSlides.type == 'sync_by_topics') {
                    theSlides.picture = $scope.slides_topics_data.picture;
                } else {
                    theSlides.topics = null;
                }

                slidesFactory.update(theSlides)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            $scope.slides_data = "";
                            slidesFactory.get({id: theSlides.id}).success(function (data) {
                                $scope.slides_data = data;
                                $scope.slides_topics_data.id = $scope.slides_data.id;
                                $scope.slides_topics_data.courses_id = $scope.slides_data.courses_id;
                                $scope.slides_topics_data.picture = $scope.slides_data.picture;
                                $scope.sync_by_topics.picture = $scope.slides_data.picture;

                                angular.element('#slides-courses').attr('disabled', '');
                                angular.element('#slides-courses').removeClass('form-white');
                                angular.element('#courses_id').attr('disabled', '');
                                angular.element('#courses_id').removeClass('form-white');

                                coursesFactory.slidesForSync({id: $scope.slides_data.courses_id}).success(function (data_slidesForSync) {
                                    $scope.slides_for_sync = data_slidesForSync;
                                });

                                coursesFactory.nextSlide({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                                    $scope.nextSlides = data.id;
                                });

                                coursesFactory.previousSlides({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                                    $scope.previousSlides = data.id;
                                });

                                var query_string = '&topics_id=' + $scope.slides_topics_data.topics.id + "&type=Edit";
                                var theSlides = {id: $scope.slides_data.id};

                                slidesFactory.getByTopics(theSlides, query_string).success(function (data) {
                                    $scope.slides_topics_data = data;
                                });

                            });

                            switch (nextAction) {
                                case 'continue_editing' : break;
                                default                 : theSlides.courses_id ? $location.path('slides/'+ theSlides.courses_id +'/courses').search({}) : $location.path('slides').search({}); break;
                            }

                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.slides-frm'));
                    });

            } else {

                if (theSlides.type == 'sync_by_topics') {
                    theSlides.picture = $scope.sync_by_topics.picture;
                } else {
                    theSlides.topics = null;
                }

                slidesFactory.create(theSlides)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('slides/'+ data.createdId +'/edit').search({}); break;
                                default                 : theSlides.courses_id ? $location.path('slides/'+ theSlides.courses_id +'/courses').search({}) : $location.path('slides').search({}); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.handleError(data, angular.element('.slides-frm'));
                    });
            }
        }

        $scope.deleteSlides = function (theSlides) {
            var id = theSlides.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                slidesFactory.delete(theSlides).success(function (data) {
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

        $scope.syncTimeCurrent = function (slidesIndex) {
            var currentTime = "";
            var offset = Math.floor(jwplayer("player").getPosition());
            var sec_num = parseInt(offset, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);
            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            currentTime = hours+':'+minutes+':'+seconds;

            $scope.slides_data.slides_times[slidesIndex].time = currentTime;
        };

        $scope.addSync = function(theSlides) {
            var currentTime = "";
            var offset = Math.floor(jwplayer("player").getPosition());
            var sec_num = parseInt(offset, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);
            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            currentTime = hours+':'+minutes+':'+seconds;

            $scope.slides_data.slides_times.push({ 'slides_id': theSlides.id, 'courses_id': theSlides.courses_id, 'time': currentTime });
        };

        $scope.deleteSyncSlide = function(theSlides) {
            if (theSlides.id === undefined) {
                var index = $scope.slides_data.slides_times.indexOf(theSlides);
                $scope.slides_data.slides_times.splice(index, 1);
            } else {
                var id = theSlides.id;
                var alert = confirm("Are you sure to delete time - " + theSlides.time + " ?");
                if(alert == true) {
                    slidesTimesFactory.delete(theSlides)
                        .success(function(data) {
                            if(data.is_error == false){
                                // notification("success",data.message);
                                $route.reload();
                                // $scope.submitCaptions(theSlides);
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

        $scope.toggleReloadTopics = function () {
            var playerInstance = jwplayer("player-topics");
            playerInstance.setup({
                file: $scope.slides_topics_data.topics.streaming_url_cut,
                aspectratio: "16:9",
                width: "100%",
                autostart: "true"
            });
        }

        $scope.syncTimeCurrentTopics = function (slidesIndex) {
            var currentTime = "";
            var offset = Math.floor(jwplayer("player-topics").getPosition());
            var sec_num = parseInt(offset, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);
            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            currentTime = hours+':'+minutes+':'+seconds;

            $scope.slides_topics_data.slides_times[slidesIndex].time = currentTime;
        };

        $scope.addSyncTopics = function(theSlides, type) {
            var currentTime = "";
            var offset = Math.floor(jwplayer("player-topics").getPosition());
            var sec_num = parseInt(offset, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);
            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            currentTime = hours+':'+minutes+':'+seconds;
            if ($scope.mode == 'Create') {
                $scope.slides_topics_data.slides_times.push({ 'slides_id': null, 'courses_id': theSlides.courses_id, 'time': currentTime });
            } else {
                 $scope.slides_topics_data.slides_times.push({ 'slides_id': theSlides.id, 'courses_id': theSlides.courses_id, 'time': currentTime });
            }

        };

        $scope.deleteSyncSlideTopics = function(theSlides) {
            if (theSlides.id === undefined) {
                var index = $scope.slides_topics_data.slides_times.indexOf(theSlides);
                $scope.slides_topics_data.slides_times.splice(index, 1);
            } else {
                var id = theSlides.id;
                var alert = confirm("Are you sure to delete time - " + theSlides.time + " ?");
                if(alert == true) {
                    slidesTimesFactory.delete(theSlides)
                        .success(function(data) {
                            if(data.is_error == false){

                                notification("success",data.message);

                                $scope.slides_data = "";
                                slidesFactory.get({id: theSlides.slides_id}).success(function (data) {
                                    $scope.slides_data = data;
                                    $scope.slides_topics_data.id = $scope.slides_data.id;
                                    $scope.slides_topics_data.courses_id = $scope.slides_data.courses_id;
                                    $scope.slides_topics_data.picture = $scope.slides_data.picture;
                                    $scope.sync_by_topics.picture = $scope.slides_data.picture;

                                    angular.element('#slides-courses').attr('disabled', '');
                                    angular.element('#slides-courses').removeClass('form-white');
                                    angular.element('#courses_id').attr('disabled', '');
                                    angular.element('#courses_id').removeClass('form-white');

                                    coursesFactory.slidesForSync({id: $scope.slides_data.courses_id}).success(function (data_slidesForSync) {
                                        $scope.slides_for_sync = data_slidesForSync;
                                    });

                                    coursesFactory.nextSlide({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                                        $scope.nextSlides = data.id;
                                    });

                                    coursesFactory.previousSlides({id: $scope.slides_data.courses_id}, $scope.slides_data.order).success(function (data) {
                                        $scope.previousSlides = data.id;
                                    });

                                    var query_string = '&topics_id=' + $scope.slides_topics_data.topics.id + "&type=Edit";
                                    var theSlides = {id: $scope.slides_data.id};

                                    slidesFactory.getByTopics(theSlides, query_string).success(function (data) {
                                        $scope.slides_topics_data = data;
                                    });

                                });

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

        $scope.toggleSlideActive = function (theSlides) {
            theSlides.admin_id = $scope.admin.id;
            if (theSlides.slide_active == 1) { theSlides.slide_active = 0; } else { theSlides.slide_active = 1; }
            if ($scope.mode == "Edit") {
                slidesFactory.updateSlideActive(theSlides)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                            slides_query($scope.current_page, $scope.per_page);
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
