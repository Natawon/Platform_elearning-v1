'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('slidesCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', 'slidesFactory', 'coursesFactory', 'topicsFactory', 'settingsFactory', function ($scope, $sce, $routeParams, $location, $route, $filter, slidesFactory, coursesFactory, topicsFactory, settingsFactory) {

        $scope.slides = {};
        $scope.slides_data = {};
        $scope.selected_courses = {};
        $scope.selected_topics = {};

        $scope.mode = "Create";

        $scope.base_slides_picture = settingsFactory.getURL('base_slides_picture');

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 30;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.slides = resp.data;
            for (var i = 0; i < $scope.slides.length; i++) {
                var newSlidesModifyDatetime = new Date($scope.slides[i].modify_datetime).toISOString();
                $scope.slides[i].modify_datetime = $filter('date')(newSlidesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.slides[i].no = (resp.from + i);
            }
            set_pagination(resp);
        };

        var slides_query = function (page, per_page) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
            if(!angular.isUndefined($routeParams.selected_topics)){
                $scope.selected_courses = {id: $routeParams.selected_courses};
                $scope.selected_topics = {id: $routeParams.selected_topics};
                var query_topics = $scope.selected_topics;
                var query = topicsFactory.slides(query_topics, query_string);
            }else if (!angular.isUndefined($routeParams.selected_courses)) {
                $scope.selected_courses = {id: $routeParams.selected_courses};
                var query_courses = $scope.selected_courses;
                var query = coursesFactory.slides(query_courses, query_string);
            } else {
                var query = slidesFactory.query(query_string);
            }
            query.success(success_callback);
        };

        $scope.toggleStatus = function (theSlides) {
            theSlides.admin_id = $scope.admin.id;
            if (theSlides.status == 1) { theSlides.status = 0; } else { theSlides.status = 1; }
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
                    notification("error", " No Access-Control-Allow-Origin");
                });
        };

        $scope.ReloadPlayer = function (theSlides) {
            topicsFactory.get({id: theSlides.topics_id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url_cut,
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

            });
        };

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                slides_query(new_page, $scope.per_page);
            }
        });

        $scope.$watch('slides_data.courses_id', function () {
            $scope.changeFilterCourses();
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
                    slides_query($scope.current_page, $scope.per_page);
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
                    slides_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            slides_query($scope.page, $scope.per_page);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        slides_query($scope.page, $scope.per_page);

        coursesFactory.all().success(function (data) {
            $scope.courses = data;
        });

        topicsFactory.topics2parents($scope.selected_courses).success(function(data) {
            $scope.topics = data;
        });

        if (!angular.isUndefined($routeParams.id)) {
            slidesFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.slides_data = data;
                $scope.mode = "Edit";

                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url_cut,
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

            })
        }

        $scope.ReviewTimeSync = function () {
            var hms = $scope.slides_data.time;
            var a = hms.split(':');
            var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);

            var playerInstance = jwplayer("player");
            playerInstance.seek(seconds);
        }

        $scope.changeFilterCourses = function () {
            if($scope.slides_data.courses_id){
                $scope.courses_id = {id: $scope.slides_data.courses_id};
                topicsFactory.topics2parents($scope.courses_id).success(function(data) {
                    $scope.parents = data;
                })
            }
        }

        $scope.changeCoursesFilter = function () {
            if ($scope.selected_courses) {
                $location.path('courses/' + $scope.selected_courses + '/slides');
            } else {
                $location.path('slides');
            }
        }

        $scope.changeTopicsFilter = function () {
            if ($scope.selected_topics) {
                $location.path('courses/' + $scope.selected_courses.id + '/topics/' + $scope.selected_topics + '/slides');
            } else {
                $location.path('slides');
            }
        }


        $scope.submitSlides = function (theSlides) {
            theSlides.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
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
                        notification("error", " No Access-Control-Allow-Origin");
                    });
            } else {
                slidesFactory.create(theSlides)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                            $location.path('courses/' + theSlides.courses_id + '/topics/'+ theSlides.topics_id +'/slides');
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", " No Access-Control-Allow-Origin");
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
                        notification("error", " No Access-Control-Allow-Origin");
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
