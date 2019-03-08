'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('subtitlesCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$interval', '$httpParamSerializer', 'subtitlesFactory', 'videosFactory', 'functionsFactory', 'pluginsService', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, $interval, $httpParamSerializer, subtitlesFactory, videosFactory, functionsFactory, pluginsService, settingsFactory) {

        $scope.subtitles = {};
        $scope.subtitles_data = {};
        $scope.videos_data = {};
        $scope.videos = {};
        $scope.selected_videos = null;

        $scope.mode = "Create";
        $scope.isPauseOnKey = true;
        $scope.totalDisplayed = 200;

        $scope.subtitles_style_object= {};

        $('#checkboxIsPauseOnKey').iCheck('check');

        // $scope.base_subtitles_picture = settingsFactory.getURL('base_subtitles_picture');

        // $scope.max_size = 5;
        // $scope.page = 1;
        // $scope.per_page = 30;
        // $scope.current_page = 1;
        // $scope.sorting_order = 'order';
        // $scope.sorting_direction = 'asc';
        // $scope.keyword = "";

        // var set_pagination = function (pagination_data) {
        //     $scope.total = pagination_data.total;
        //     $scope.last_page = pagination_data.last_page;
        //     $scope.current_page = pagination_data.current_page;
        //     $scope.per_page = pagination_data.per_page;
        // };

        // var success_callback = function (resp) {
        //     $scope.subtitles = resp.data;
        //     for (var i = 0; i < $scope.subtitles.length; i++) {
        //         var newSubtitlesModifyDatetime = new Date($scope.subtitles[i].modify_datetime).toISOString();
        //         $scope.subtitles[i].modify_datetime = $filter('date')(newSubtitlesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
        //         $scope.subtitles[i].no = (resp.from + i);
        //     }
        //     set_pagination(resp);
        // };

        // var subtitles_query = function (page, per_page) {
        //     var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
        //     if (!angular.isUndefined($routeParams.selected_videos)) {
        //         $scope.selected_videos = {id: $routeParams.selected_videos};
        //         var query_videos = $scope.selected_videos;
        //         var query = videosFactory.subtitles(query_videos, query_string);
        //     } else {
        //         var query = subtitlesFactory.query(query_string);
        //     }
        //     query.success(success_callback);
        // };

        // subtitles_query($scope.page, $scope.per_page);

        $scope.updateSubtitlesStyle = function(style, value) {
            // console.log(style +", "+ value)
            $scope.subtitles_style_object[style] = value;
            jwplayer('player').setCaptions($scope.subtitles_style_object);
        };

        $scope.loadVideo = function() {
            setTimeout(function () {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: $scope.videos_data.smil_url,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true",
                    tracks: [{
                        file: settingsFactory.get('subtitles') + '/videos/' + $scope.videos_data.id + '/file',
                        kind: 'captions',
                        "default": true,
                    }],
                });

                $scope.subtitles_style_object = {
                    'edgeStyle': $scope.videos_data.subtitle_edge_style,
                    'color': $scope.videos_data.subtitle_font_color,
                    'fontOpacity': $scope.videos_data.subtitle_font_opacity,
                    'backgroundColor': $scope.videos_data.subtitle_background_color,
                    'backgroundOpacity': $scope.videos_data.subtitle_background_opacity,
                    'windowColor': $scope.videos_data.subtitle_window_color,
                    'windowOpacity': $scope.videos_data.subtitle_window_opacity,
                };

                playerInstance.setCaptions($scope.subtitles_style_object);
            }, 1000);
        };

        $scope.getSubtitles = function() {
            if (!angular.isUndefined($routeParams.selected_videos)) {
                videosFactory.subtitles({id: $routeParams.selected_videos}).success(function (data) {
                    $scope.videos_data = data;

                    angular.element('#subtitle_font_opacity').data('slider-value', $scope.videos_data.subtitle_font_opacity);
                    angular.element('#subtitle_background_opacity').data('slider-value', $scope.videos_data.subtitle_background_opacity);
                    angular.element('#subtitle_window_opacity').data('slider-value', $scope.videos_data.subtitle_window_opacity);

                    angular.element('#subtitle_font_opacity').val($scope.videos_data.subtitle_font_opacity);
                    angular.element('#subtitle_background_opacity').val($scope.videos_data.subtitle_background_opacity);
                    angular.element('#subtitle_window_opacity').val($scope.videos_data.subtitle_window_opacity);

                    $timeout(function() {
                        console.log(angular.element('#subtitle_font_opacity').val());
                        pluginsService.sliderIOS();
                    }, 1000);

                    // console.log(data);

                    $scope.loadVideo();

                    // playerInstance.onSeek(function(video) {
                    //     console.log(video);
                    //     var offset = Math.floor(video.offset);
                    //     var sec_num = parseInt(offset, 10); // don't forget the second param
                    //     var hours   = Math.floor(sec_num / 3600);
                    //     var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                    //     var seconds = sec_num - (hours * 3600) - (minutes * 60);
                    //     if (hours   < 10) {hours   = "0"+hours;}
                    //     if (minutes < 10) {minutes = "0"+minutes;}
                    //     if (seconds < 10) {seconds = "0"+seconds;}
                    //     $scope.subtitles_data.time = hours+':'+minutes+':'+seconds;
                    //     subtitlesFactory.get({id: $routeParams.id});
                    // });

                });

                videosFactory.get({id: $routeParams.selected_videos})
                        .error(function(data) {
                            $location.path('videos');
                        });
            }
        };

        $scope.getSubtitles();

        $scope.ReviewTimeSync = function (from_time) {
            if (from_time !== undefined) {
                var hms = from_time;
                var a = hms.split(':');
                var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);

                var playerInstance = jwplayer("player");
                console.log(seconds);
                playerInstance.seek(seconds);
            }
        };

        $scope.pauseVideo = function () {
            if ($scope.isPauseOnKey) {
                var playerInstance = jwplayer("player");
                playerInstance.pause(true);
            }
        };

        $('#checkboxIsPauseOnKey').on('ifClicked', function(event){
            console.log(event);
            if ($scope.isPauseOnKey) { $scope.isPauseOnKey = false; } else { $scope.isPauseOnKey = true; }
        });

        $scope.snapTimeCurrent = function (type,subtitleIndex) {
            var currentTime = "";
            var offset = Math.floor(jwplayer("player").getPosition());
            var sec_num = parseInt(offset, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);
            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            // angular.element($video.currentTarget).closest('.input-group').find('input').val(hours+':'+minutes+':'+seconds);

            currentTime = hours+':'+minutes+':'+seconds;

            if (type === "from") {
                $scope.videos_data.subtitles[subtitleIndex].from_time = currentTime;
            } else {
                $scope.videos_data.subtitles[subtitleIndex].to_time = currentTime;
            }
        };

        $scope.changeFilter = function () {
            if ($scope.videos_data.id) {
                // window.location = '#videos/' + $scope.videos_data.id + '/subtitles/create';
                $location.path('videos/' + $scope.videos_data.id + '/subtitles/create');
            } else {
                $location.path('videos');
            }
        };

         $scope.addSubtitle = function(theVideos) {

            $scope.videos_data.subtitles.push({
                "video_id": theVideos.id
            });

            $timeout(function() {
                angular.element('.box-subtitles:last-child').find('.from-time').focus();
            }, 200);
        };

        $scope.deleteSubtitle = function(theSubtitles, theVideos) {
            if (theSubtitles.id === undefined) {
                var index = $scope.videos_data.subtitles.indexOf(theSubtitles);
                $scope.videos_data.subtitles.splice(index, 1);
            } else {
                var id = theSubtitles.id;
                var alert = confirm("Are you sure to delete subtitle " + theSubtitles.from_time + " - " + theSubtitles.to_time + " ?");
                if(alert == true) {
                    subtitlesFactory.delete(theSubtitles)
                        .success(function(data) {
                            if(data.is_error == false){
                                // notification("success",data.message);
                                // $route.reload();
                                $scope.getSubtitles();
                                // $scope.submitSubtitles(theVideos);
                            }
                            if(data.is_error == true){
                                notification("error",data.message);
                            }
                        })
                        .error(function() {
                            functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                        });
                }
            }

        }


        $scope.submitSubtitles = function (theVideos) {

            theVideos.subtitle_font_opacity = angular.element('#subtitle_font_opacity').val();
            theVideos.subtitle_background_opacity = angular.element('#subtitle_background_opacity').val();
            theVideos.subtitle_window_opacity = angular.element('#subtitle_window_opacity').val();
            // console.log(theVideos);
            // return false;
            theVideos.admin_id = $scope.admin.id;
            // if ($scope.mode == "Edit") {
            //     subtitlesFactory.update(theVideos)
            //         .success(function (data) {
            //             if (data.is_error == false) {
            //                 notification("success", data.message);
            //                 $location.path('videos/' + theVideos.videos_id + '/subtitles');
            //             }
            //             if (data.is_error == true) {
            //                 notification("error", data.message);
            //             }
            //         })
            //         .error(function () {
            //             functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
            //         });
            // } else {
                subtitlesFactory.createByVideo(theVideos)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                            // $location.path('videos/' + theVideos.id + '/subtitles/create');
                            $scope.getSubtitles();
                            // $route.reload();
                            // window.location.reload();
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                    });
            // }
        }

        // $scope.deleteSubtitles = function (theSubtitles) {
        //     var id = theSubtitles.id;
        //     var alert = confirm("Are you sure to delete #" + id + " ?");
        //     if (alert == true) {
        //         subtitlesFactory.delete(theSubtitles).success(function (data) {
        //             if (data.is_error == false) {
        //                 notification("success", data.message);
        //                 $route.reload();
        //             }
        //             if (data.is_error == true) {
        //                 notification("error", data.message);
        //             }
        //         })
        //             .error(function () {
        //                 functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
        //             });
        //     }
        // }

        $scope.toggleSubtitlesStatus = function (theVideos) {
            theVideos.admin_id = $scope.admin.id;
            if (theVideos.subtitles_status == 1) { theVideos.subtitles_status = 0; } else { theVideos.subtitles_status = 1; }
            videosFactory.update(theVideos)
                .success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                .error(function () {
                    functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        $scope.downloadFile = function () {
            var url = settingsFactory.get('subtitles') + '/videos/' + $scope.videos_data.id + '/file/download';
            window.open(url,"_self");
        };

        // Upload File
        $scope.uploadFile = function(theVideos) {

            var alert = confirm("การอัพโหลดไฟล์จะเป็นการเขียนทับคำบรรยายเดิม ยืนยันการอัพโหลดใช่หรือไม่?");
            if (alert == true) {
                var file;
                var $btnUploadFile = angular.element('#btn-upload-file');

                $btnUploadFile.button('loading');

                file = angular.element('#file')[0].files[0];
                subtitlesFactory.uploadFile(theVideos, file)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success", data.message);
                            // $route.reload();
                        }
                        if(data.is_error == true){
                            notification("error", data.message);
                        }

                        $scope.getSubtitles();
                        $btnUploadFile.button('reset');
                        angular.element('#removeFile').trigger('click');
                    })
                    .error(function(data) {
                        if (!angular.isUndefined(data.file)) {
                            notification("error", data.file);
                        } else if (!angular.isUndefined(data.message)) {
                            notification("error", data.message);
                            angular.element('#removeFile').trigger('click');
                        } else {
                            functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                        }

                        $scope.getSubtitles();
                        $btnUploadFile.button('reset');

                    });
            }
        };

        $scope.loadMore = function () {
            if ($scope.totalDisplayed < $scope.videos_data.subtitles.length) {
                $scope.totalDisplayed += 100;
                angular.element('#btnLoadMore').blur();
            }
        };

        angular.element('body').on('slideStop', '#subtitle_font_opacity', function(event) {
            event.preventDefault();
            $scope.updateSubtitlesStyle('fontOpacity', $(this).val());
        });
        angular.element('body').on('slideStop', '#subtitle_background_opacity', function(event) {
            event.preventDefault();
            $scope.updateSubtitlesStyle('backgroundOpacity', $(this).val());
        });
        angular.element('body').on('slideStop', '#subtitle_window_opacity', function(event) {
            event.preventDefault();
            $scope.updateSubtitlesStyle('windowOpacity', $(this).val());
        });

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
