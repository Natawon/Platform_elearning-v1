'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('livestreamsCtrl', ['$scope', '$sce', '$routeParams', '$location', '$timeout', '$route', '$filter', '$interval', 'coursesFactory', 'topicsFactory', 'slidesFactory', 'livestreamFactory', 'ffmpegFactory', 'videosFactory', 'settingsFactory',
        function ($scope, $sce, $routeParams, $location, $timeout, $route, $filter, $interval, coursesFactory, topicsFactory, slidesFactory, livestreamFactory, ffmpegFactory, videosFactory, settingsFactory) {

        $scope.tab = 'info';
        $scope.isRecord = false;
        $scope.hasRecordFile = false;
        $scope.btnEndLive = 'hidden';
        $scope.base_slides_picture = settingsFactory.getURL('base_slides_picture');
        $scope.recorders_data = {
            name: '',
            state: '',
            output: '',
            base_file: '',
            format_file: '',
            current_file: '',

        };

        $scope.stateTopic = 'live';

        $scope.actionStream = 'เริ่ม';
        $scope.hasSignal = false;
        $scope.loadSignal = true;
        $scope.showNotyPlayer = true;
        $scope.showNotyPlayerText = false;

        $scope.showBtnPause = false;

        $scope.btnClassStreamPause = '';
        $scope.txtDisplayVOD = '';

        $scope.isFirstSyncSlide = true;
        $scope.disabledBtnSynSlide = false;

        $scope.btnSyncSlide = 'Start Sync';
        $scope.ctdSyncSlide = '';
        $scope.modalClassAlert = 'bg-red';
        $scope.modalHeaderAlert = '';
        $scope.modalContentAlert = '';

        $scope.testAlert = 1;

        // $scope.btnPauseResume = "<i class='fa fa-pause' aria-hidden='true'></i> Pause Stream";

        $scope.prepareSyncSlide = {
            picture: '080818035912-live-control_1.jpg'
        }

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

        $scope.live_result = {
            video_status: 'progressing'
        };

        var loopGetBroadcastSignal;

        $scope.toggleAlert = function() {
            if ($scope.testAlert == 0) {
                $scope.testAlert = 1;
            } else {
                $scope.testAlert = 0;
            }
        }

        $scope.copySuccess = function(e) {
            e.clearSelection();
            notification("success", 'Text copied successfully.');
        };

        $scope.changeTab = function(tab) {
            $('html, body').animate({
                scrollTop: 0
            }, 500);

            $timeout(function() {
                angular.element(tab+' > a').trigger('click');
            }, 600);
        };

        angular.element('body').on('click', '#main-tabs > .nav-tabs > li > a', function(event) {
            $scope.tab = $(this).closest('li').attr('id');
            if ($scope.tab == 'live-control') {
                angular.element('.wrapper-slides').removeClass('hidden');
            } else {
                angular.element('.wrapper-slides').addClass('hidden');
            }
        });

        //on load get content//
        if (!angular.isUndefined($routeParams.id)) {
            topicsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.topics_data = data;

                if ($scope.topics_data.is_stop_stream == 1) {
                    $scope.changeTab('#live-result');
                } else {
                    $scope.changeTab('#live-control');
                }

                $scope.live_start_datetime = $filter('date')(new Date($scope.topics_data.live_start_datetime.split('-').join('/')), "d MMMM yyyy 'at' HH:mm");
                $scope.live_end_datetime = $filter('date')(new Date($scope.topics_data.live_end_datetime.split('-').join('/')), "d MMMM yyyy 'at' HH:mm");

                $scope.stateTopic = $scope.topics_data.state;

                if ($scope.topics_data.state == 'live' && $scope.topics_data.streaming_status == '1') {
                    $scope.statusStreaming = 'Live';
                    $scope.btnStatus = "<i class='fa fa-stop'></i> Stop Stream";
                    $scope.txtDisplayVOD = "<i class='fa fa-stop'></i> Off";
                    // $scope.btnClassStreamStatus = "active";
                    $scope.btnClassStreamStatus = "";
                    $scope.showBtnPause = true;
                } else if ($scope.topics_data.state == 'live' && $scope.topics_data.streaming_status == '0') {
                    $scope.statusStreaming = 'Waiting for Live';
                    $scope.btnStatus = "<i class='fa fa-play'></i> Stream Now";
                    $scope.txtDisplayVOD = "<i class='fa fa-play'></i> On";
                    $scope.btnClassStreamStatus = "";
                }

                if ($scope.topics_data.state == 'live' && $scope.topics_data.streaming_status == '1' && $scope.topics_data.streaming_pause == 1) {
                    $scope.statusStreaming = 'Pause';
                    $scope.btnPauseResume = "<i class='fa fa-play' aria-hidden='true'></i> Resume Stream";
                    angular.element('#btn-toggle-pause').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Resuming...</span>');
                } else if ($scope.topics_data.state == 'live' && $scope.topics_data.streaming_status == '1' && $scope.topics_data.streaming_pause == 0) {
                    $scope.statusStreaming = 'Live';
                    $scope.btnPauseResume = "<i class='fa fa-pause' aria-hidden='true'></i> Pause Stream";
                    angular.element('#btn-toggle-pause').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Pausing...</span>');
                }

                // VOD
                if ($scope.topics_data.state == 'vod' && $scope.topics_data.streaming_status == 1) {
                    $scope.statusStreaming = 'On Demand Now';
                } else if ($scope.topics_data.state == 'vod' && $scope.topics_data.streaming_status == 0) {
                    $scope.statusStreaming = 'On Demand Later';
                } else if ($scope.topics_data.state == 'live' && $scope.topics_data.streaming_status == 0 && $scope.topics_data.is_stop_stream == 1) {
                    $scope.statusStreaming = 'End Live';
                }

                $scope.getLiveResults();

                $timeout(function() {
                    if ($scope.topics_data.is_stop_stream == 1 && $scope.live_result.video_status != 'complete') {
                        var theLive = {};
                        theLive.id = $routeParams.id;
                        theLive.type = 'topics';
                        theLive.filename = $scope.topics_data.streaming_record_filename;
                        theLive.dir_name = $scope.topics_data.streaming_record_part;

                        $scope.checkOriginalFile(theLive, $scope.live_result.filesize);
                    }
                }, 1000);

                topicsFactory.getSlides({id: $routeParams.id}).success(function (data) {
                    $scope.slides = data;
                });

                // var queryString = '?isFirstSyncSlide=' + $scope.isFirstSyncSlide;
                coursesFactory.slidesActive({id: $scope.topics_data.courses_id}).success(function (data) {
                    $scope.slidesActive = data;
                    if (!jQuery.isEmptyObject($scope.slidesActive)) {
                        $scope.isFirstSyncSlide = false;
                        $scope.btnSyncSlide = 'Next Slide';
                        if ($scope.slidesActive.isFirstSlide == true) {
                            $scope.nextSlides = $scope.slidesActive;
                        } else {
                            $scope.activeSlides = $scope.slidesActive.id;
                            coursesFactory.nextSlide({id: $scope.topics_data.courses_id}, $scope.slidesActive.order).success(function (data) {
                                $scope.nextSlides = data;
                            });
                        }
                    } else {
                        console.log('empty');
                        $scope.slidesActive = $scope.prepareSyncSlide;
                        $scope.activeSlides = null;
                        coursesFactory.firstSlide({id: $scope.topics_data.courses_id}).success(function (data) {
                            $scope.nextSlides = data;
                            // coursesFactory.nextSlide({id: $scope.topics_data.courses_id}, $scope.firstSlide.order).success(function (data) {
                            //     $scope.nextSlides = data;
                            // });
                        });
                    }
                });
            });

            $scope.getLiveResults = function() {
                livestreamFactory.getLiveResults({topic_id: $routeParams.id}).success(function (data) {
                    $scope.live_result = data;

                    if ($scope.live_result.live_start_datetime) {
                        $scope.live_result.live_start_datetime = $filter('date')(new Date($scope.live_result.live_start_datetime.split('-').join('/')), "d MMMM yyyy 'at' HH:mm");
                    }

                    if ($scope.live_result.live_end_datetime) {
                        $scope.live_result.live_end_datetime = $filter('date')(new Date($scope.live_result.live_end_datetime.split('-').join('/')), "d MMMM yyyy 'at' HH:mm");
                    }

                    if ($scope.topics_data.vod_format == 'vod_now') {
                        $scope.live_result.vod_format = $scope.format_stop_stream.vod_now;
                    } else if ($scope.topics_data.vod_format == 'vod_later') {
                        $scope.live_result.vod_format = $scope.format_stop_stream.vod_later;
                    } else if ($scope.topics_data.vod_format == 'end_live') {
                        $scope.live_result.vod_format = $scope.format_stop_stream.end_live;
                    }
                });
            }

            $scope.updateLiveResults = function(theLive) {
                livestreamFactory.updateLiveResults(theLive).success(function (data) {
                    $scope.getLiveResults();
                });
            }

            $scope.loopOpacitySignal = function() {
                setInterval(function () {
                    $('#icon-signal').css('opacity', '0.6');
                    $('#icon-signal').animate({
                        opacity: 1
                    }, 1000);
                }, 3000)
            }

            $scope.getBroadcastSignal = function() {
                livestreamFactory.incomingStream({id: $routeParams.id}).success(function (data) {
                    // console.log(data);
                    $scope.streamsReturn = data;
                    // if ($scope.streamsReturn.IncomingStreams.IncomingStream);
                    var incomingStreamData = $filter('filter')($scope.streamsReturn.IncomingStreams.IncomingStream, {"Name": $scope.topics_data.streaming_streamname});

                    if (incomingStreamData != undefined && incomingStreamData.length > 0) {
                        $scope.hasSignal = true;
                    } else {
                        $scope.hasSignal = false;
                    }

                    if ($scope.hasSignal == false) {
                        $scope.showNotyPlayer = true;
                        $scope.showNotyPlayerText = true;

                        angular.element("#player").remove();
                        angular.element("#wrapper-player").prepend('<div id="player"></di>');

                        loopGetBroadcastSignal = $timeout(function() {
                            $scope.getBroadcastSignal();
                        }, 5000);
                    } else if ($scope.hasSignal == true) {
                        $scope.showNotyPlayer = false;
                        $scope.showNotyPlayer = false;
                        $scope.showNotyPlayerText = false;

                        angular.element("#player").remove();
                        angular.element("#wrapper-player").prepend('<div id="player"></di>');

                        $scope.topics_data.streaming_url_backend = $scope.topics_data.streaming_server + '/' + $scope.topics_data.streaming_applications + '/' + $scope.topics_data.streaming_streamname;

                        var flashvars = {
                            src: $scope.topics_data.streaming_url_backend,
                            plugin_hls: "assets/global/js/grindplayer/HLSDynamicPlugin.swf", // opensource hls
                            streamType: "live", //streamType: "dvr", // recorded | live | dvr
                            verbose: false,
                            bufferTime: 0
                        };
                        var params = {
                            allowFullScreen: true
                            , allowScriptAccess: "always"
                        };
                        var attrs = {
                            name: "player"
                        };
                        //swfobject.embedSWF("grindplayer/GrindPlayer_logging.swf", "player_grind", "640", "360", "10.2", null, flashvars, params, attrs);
                        // swfobject.embedSWF("grindplayer/GrindPlayer.swf", "player_grind", "640", "360", "10.2", null, flashvars, params, attrs);
                        swfobject.embedSWF("assets/global/js/grindplayer/GrindPlayer.swf", "player", "100%", $(window).height(), "10.2", null, flashvars, params, attrs);
                    }

                    $scope.loadSignal = false;
                });
            }

            $timeout(function() {
                $scope.loopOpacitySignal();

                $scope.getBroadcastSignal();
                // return false;

                livestreamFactory.incomingStream({id: $routeParams.id}).success(function (data) {
                    $scope.streamsReturn = data;

                    var recorderName = $scope.topics_data.streaming_streamname;
                    // // Start Find recorderName
                    // var bitrateArray = $scope.streamsReturn.IncomingStreams.IncomingStream.map(x => x.Name);
                    // var recorderName1080p = $scope.topics_data['streaming_prefix_streamname'] + '_1080p';
                    // var recorderName720p = $scope.topics_data['streaming_prefix_streamname'] + '_720p';
                    // var recorderName360p = $scope.topics_data['streaming_prefix_streamname'] + '_360p';
                    // var recorderName240p = $scope.topics_data['streaming_prefix_streamname'] + '_240p';

                    // for (var i = 0; i < bitrateArray.length; i++) {
                    //     if (bitrateArray[i] == recorderName1080p) {
                    //         recorderName = bitrateArray[i];
                    //         break;
                    //     } else if (bitrateArray[i] == recorderName720p) {
                    //         recorderName = bitrateArray[i];
                    //         break;
                    //     }  else if (bitrateArray[i] == recorderName360p) {
                    //         recorderName = bitrateArray[i];
                    //         break;
                    //     }  else if (bitrateArray[i] == recorderName240p) {
                    //         recorderName = bitrateArray[i];
                    //         break;
                    //     }
                    // }

                    // $scope.outputStream = {
                    //     output: []
                    // };

                    // var outputStream1080p = $filter('filter')($scope.streamsReturn.IncomingStreams.IncomingStream, {"Name": recorderName1080p});
                    // var outputStream720p = $filter('filter')($scope.streamsReturn.IncomingStreams.IncomingStream, {"Name": recorderName720p});
                    // var outputStream360p = $filter('filter')($scope.streamsReturn.IncomingStreams.IncomingStream, {"Name": recorderName360p});
                    // var outputStream240p = $filter('filter')($scope.streamsReturn.IncomingStreams.IncomingStream, {"Name": recorderName240p});

                    // if (outputStream1080p.length > 0) {
                    //     $scope.outputStream.output.push({"Name": outputStream1080p[0].Name});
                    // }

                    // if (outputStream720p.length > 0) {
                    //     $scope.outputStream.output.push({"Name": outputStream720p[0].Name});
                    // }

                    // if (outputStream360p.length > 0) {
                    //     $scope.outputStream.output.push({"Name": outputStream360p[0].Name});
                    // }

                    // if (outputStream240p.length > 0) {
                    //     $scope.outputStream.output.push({"Name": outputStream240p[0].Name});
                    // }

                    $scope.recorders_data.name = $scope.topics_data.streaming_streamname;

                    var queryStringRecorderName = '&recorderName=' + recorderName;
                    // End Find recorderName

                    $scope.msgTimer = "00:00:00";
                    if($scope.streamsReturn.Recorders.StreamRecorder){
                        if (angular.isArray($scope.streamsReturn.Recorders.StreamRecorder)) {
                            var matchedRecorder = _.find($scope.streamsReturn.Recorders.StreamRecorder, function(o) { return o.RecorderName == recorderName; });
                            if (!angular.isUndefined(matchedRecorder)) {
                                $scope.statusRecorder = 'Recording...';  $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                                // $scope.btnClassRecord = 'active';
                                $scope.isRecord = true;

                                $scope.recorders_data.state = matchedRecorder.RecorderState;
                                $scope.recorders_data.output = matchedRecorder.OutputPath;
                                $scope.recorders_data.base_file = matchedRecorder.BaseFile;
                                $scope.recorders_data.format_file = matchedRecorder.FileFormat;
                                $scope.recorders_data.current_file = matchedRecorder.CurrentFile;

                                $scope.Timer = $interval(function () {
                                    $scope.isPaused = false;
                                    if(!$scope.isPaused) {
                                        $scope.incomingStreamDuration($routeParams, queryStringRecorderName);
                                        // livestreamFactory.incomingStreamDuration({id: $routeParams.id}, queryStringRecorderName).success(function (data) {
                                        //     var time = data;
                                        //     var milliseconds = parseInt((time % 1000) / 100)
                                        //     , seconds = parseInt((time / 1000) % 60)
                                        //     , minutes = parseInt((time / (1000 * 60)) % 60)
                                        //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                        //     hours = (hours < 10) ? "0" + hours : hours;
                                        //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                                        //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                                        //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                                        // });
                                    }
                                }, 1000);
                            } else {
                                if ($scope.topics_data.state == 'live') {
                                    livestreamFactory.incomeDuration({id: $routeParams.id}).success(function (data) {
                                        // if (data.length == undefined) {
                                        //     data = 0;
                                        // }
                                        var time = data;

                                        // if (time >= 30000) {
                                        //     time = time - 30000;
                                        // }

                                        var milliseconds = parseInt((time % 1000) / 100)
                                        , seconds = parseInt((time / 1000) % 60)
                                        , minutes = parseInt((time / (1000 * 60)) % 60)
                                        , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                        hours = (hours < 10) ? "0" + hours : hours;
                                        minutes = (minutes < 10) ? "0" + minutes : minutes;
                                        seconds = (seconds < 10) ? "0" + seconds : seconds;

                                        $scope.msgTimer = hours+':'+minutes+':'+seconds;
                                    });
                                }

                                $scope.statusRecorder = 'Not record';  $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                                $scope.btnClassRecord = '';
                                $scope.isRecord = false;
                            }
                        } else if ($scope.streamsReturn.Recorders.StreamRecorder.RecorderName == recorderName) {
                            $scope.statusRecorder = 'Recording...';  $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                            // $scope.btnClassRecord = 'active';
                            $scope.isRecord = true;

                            $scope.recorders_data.state = $scope.streamsReturn.Recorders.StreamRecorder.RecorderState;
                            $scope.recorders_data.output = $scope.streamsReturn.Recorders.StreamRecorder.OutputPath;
                            $scope.recorders_data.base_file = $scope.streamsReturn.Recorders.StreamRecorder.BaseFile;
                            $scope.recorders_data.format_file = $scope.streamsReturn.Recorders.StreamRecorder.FileFormat;
                            $scope.recorders_data.current_file = $scope.streamsReturn.Recorders.StreamRecorder.CurrentFile;

                            $scope.Timer = $interval(function () {
                                $scope.isPaused = false;
                                if(!$scope.isPaused) {
                                    $scope.incomingStreamDuration($routeParams, queryStringRecorderName);
                                    // livestreamFactory.incomingStreamDuration({id: $routeParams.id}, queryStringRecorderName).success(function (data) {
                                    //     var time = data;
                                    //     var milliseconds = parseInt((time % 1000) / 100)
                                    //     , seconds = parseInt((time / 1000) % 60)
                                    //     , minutes = parseInt((time / (1000 * 60)) % 60)
                                    //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                    //     hours = (hours < 10) ? "0" + hours : hours;
                                    //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                                    //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                                    //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                                    // });
                                }
                            }, 1000);
                        } else {
                            if ($scope.topics_data.state == 'live') {
                                livestreamFactory.incomeDuration({id: $routeParams.id}).success(function (data) {
                                    // if (data.length == undefined) {
                                    //     data = 0;
                                    // }
                                    var time = data;

                                    // if (time >= 30000) {
                                    //     time = time - 30000;
                                    // }

                                    var milliseconds = parseInt((time % 1000) / 100)
                                    , seconds = parseInt((time / 1000) % 60)
                                    , minutes = parseInt((time / (1000 * 60)) % 60)
                                    , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                    hours = (hours < 10) ? "0" + hours : hours;
                                    minutes = (minutes < 10) ? "0" + minutes : minutes;
                                    seconds = (seconds < 10) ? "0" + seconds : seconds;

                                    $scope.msgTimer = hours+':'+minutes+':'+seconds;
                                });
                            }

                            $scope.statusRecorder = 'Not record';  $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                            $scope.btnClassRecord = '';
                            $scope.isRecord = false;
                        }
                    }else{
                        if ($scope.topics_data.state == 'live') {
                            livestreamFactory.incomeDuration({id: $routeParams.id}).success(function (data) {
                                // if (data == undefined) {
                                //     data = 0;
                                // }
                                var time = data;

                                // if (time >= 30000) {
                                //     time = time - 30000;
                                // }

                                var milliseconds = parseInt((time % 1000) / 100)
                                , seconds = parseInt((time / 1000) % 60)
                                , minutes = parseInt((time / (1000 * 60)) % 60)
                                , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                hours = (hours < 10) ? "0" + hours : hours;
                                minutes = (minutes < 10) ? "0" + minutes : minutes;
                                seconds = (seconds < 10) ? "0" + seconds : seconds;

                                $scope.msgTimer = hours+':'+minutes+':'+seconds;
                            });
                        }

                        $scope.statusRecorder = 'Not record';  $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                        $scope.btnClassRecord = '';
                        $scope.isRecord = false;
                    }

                    if ($scope.isRecord == false) {
                        $scope.checkRecordFile($scope.topics_data);
                    }

                    $timeout(function() {
                        if ($scope.topics_data.is_stop_record == 1) {
                            var time = $scope.topics_data.current_duration_record;

                            // if (time >= 30000) {
                            //     time = time - 30000;
                            // }

                            var milliseconds = parseInt((time % 1000) / 100)
                            , seconds = parseInt((time / 1000) % 60)
                            , minutes = parseInt((time / (1000 * 60)) % 60)
                            , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                            hours = (hours < 10) ? "0" + hours : hours;
                            minutes = (minutes < 10) ? "0" + minutes : minutes;
                            seconds = (seconds < 10) ? "0" + seconds : seconds;

                            $scope.msgTimer = hours+':'+minutes+':'+seconds;
                        }
                    }, 1000);
                });
            },1000);
        }

        //click delete slides time//
        $scope.deleteSlidesTimes = function (theSlidesTimes) {
            var id = theSlidesTimes.id;
            var alert = confirm("Are you sure to delete time " + theSlidesTimes.time + " ?");
            if (alert == true) {
                livestreamFactory.delete_slides_times(theSlidesTimes).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);

                        // $scope.sorting_order = 'order';
                        // $scope.sorting_direction = 'asc';
                        // var query_string = "order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
                        // livestreamFactory.slides_groups({id: $routeParams.id}, query_string).success(function (data) {
                        //     $scope.slides_groups = data;
                        // });

                        topicsFactory.getSlides({id: $routeParams.id}).success(function (data) {
                            $scope.slides = data;
                        });

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

        //click reload//
        $scope.toggleReload = function (theSlides) {
            angular.element('#btn-reload').button('loading');
            $timeout.cancel(loopGetBroadcastSignal);

            $scope.showNotyPlayer = false;
            $scope.showNotyPlayerText = false;

            topicsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.topics_data = data;
                // var playerInstance = jwplayer("player");
                // playerInstance.setup({
                //     file: $scope.topics_data.streaming_url,
                //     aspectratio: "16:9",
                //     width: "100%",
                //     autostart: "true"
                // });
            });

            $scope.getBroadcastSignal();

            livestreamFactory.incomingStream({id: $routeParams.id}).success(function (data) {
                $scope.streamsReturn = data;
            });

            $timeout(function() {
                angular.element('#btn-reload').button('reset');
            }, 1000);
        }

        //click on demand//
        $scope.toggleOnDemand = function (theLive) {
            coursesFactory.getVideo(theLive.streaming_record_part, theLive.streaming_record_filename+".mp4").success(function (data_get_video){
                $scope.get_video = data_get_video;

                if ($scope.get_video.file !== null) {
                    var theVideos = {
                        event_id: theLive.id,
                        dir_name: theLive.streaming_record_part,
                        name: $scope.get_video.file.name,
                        size: $scope.get_video.file.size,
                        extFile: $scope.get_video.file.extFile,
                        type: $scope.get_video.file.type,
                        contentType: $scope.get_video.file.contentType,
                        isVideoType: $scope.get_video.file.isVideoType,
                        url: $scope.get_video.file.url,
                        deleteType: $scope.get_video.file.deleteType,
                        deleteUrl: $scope.get_video.file.deleteUrl,
                        deleteWithCredentials: $scope.get_video.file.deleteWithCredentials,
                        modifiedDateFile: $scope.get_video.file.modifiedDate
                    };

                    videosFactory.createVideo(theVideos).success(function (data_video_create){
                        if (data_video_create.exists) {
                            notification("success", data_video_create.message);
                            $location.path('courses/' + theLive.id + '/edit');
                        } else {
                            var theFFMpegParams = {
                                "filename": theLive.streaming_record_filename+".mp4",
                                "dir_name": theLive.streaming_record_part,
                                "type": "original",
                            };

                            ffmpegFactory.generateSmil(theFFMpegParams).success(function (data_generate_smil){

                                theLive.streaming_url = data_generate_smil.vodPath;

                                livestreamFactory.update_on_demand(theLive).success(function (data_update_on_demand) {
                                    if (data_update_on_demand.is_error == false) {
                                        notification("success", data_update_on_demand.message);
                                        $location.path('courses/' + theLive.id + '/edit');
                                    }
                                    if (data_update_on_demand.is_error == true) {
                                        notification("error", data_update_on_demand.message);
                                    }
                                });

                            });
                        }
                    });
                } else {
                    notification("error", 'No video has been recorded. Please start record.');
                }
            });
        }

        //click update slide//
        $scope.toggleSlideActive = function (theSlides) {
            theSlides.admin_id = $scope.admin.id;
            if (theSlides.slide_active == 1) { theSlides.slide_active = 0; } else { theSlides.slide_active = 1; }
            theSlides.time = $scope.msgTimer;
            slidesFactory.updateSlideActive(theSlides)
                .success(function (data) {
                    if (data.is_error == false) {
                        $scope.isFirstSyncSlide = false;
                        $scope.btnSyncSlide = 'Next Slide';
                        coursesFactory.slidesActive({id: theSlides.courses_id}).success(function (data) {
                            $scope.slidesActive = data;

                            theSlides.slides_id = theSlides.id;
                            theSlides.courses_id = theSlides.courses_id;
                            theSlides.topics_id = $scope.topics_data.id;
                            theSlides.time = $scope.msgTimer;
                            theSlides.state = $scope.topics_data.state;

                            if ($scope.isRecord) {
                                livestreamFactory.create_slides_times(theSlides)
                                    .success(function (data) {
                                        if (data.is_error == false) {
                                            notification("success", "Slide "+ data.time +" update.");
                                        }
                                        if (data.is_error == true) {
                                            notification("error", data.message);
                                        }
                                    })
                                    .error(function () {
                                        notification("error", " No Access-Control-Allow-Origin");
                                    });
                            }

                            coursesFactory.nextSlide({id: $scope.topics_data.courses_id}, $scope.slidesActive.order).success(function (data) {
                                $scope.nextSlides = data;
                            });

                            $scope.activeSlides = theSlides.id;

                            topicsFactory.getSlides({id: $routeParams.id}).success(function (data) {
                                $scope.slides = data;
                            });

                            // $scope.sorting_order = 'order';
                            // $scope.sorting_direction = 'asc';
                            // var query_string = "order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction;
                            // livestreamFactory.slides_groups({id: $routeParams.id}, query_string).success(function (data) {
                            //     $scope.slides_groups = data;
                            // });

                            // $scope.isActive[theSlides.slides_groups_id] = true;

                        });
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                .error(function () {
                    notification("error", " No Access-Control-Allow-Origin");
                });
        }


        //edit time slide//
        $scope.updateSlideTime = function (theSlidesTimes) {
            theSlidesTimes.admin_id = $scope.admin.id;
            livestreamFactory.update_slides_times(theSlidesTimes)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", "Slide "+ theSlidesTimes.time +" update.");
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                });
        }

        $scope.checkRecordFile = function (theLive) {
            livestreamFactory.checkRecordFile(theLive).success(function (data){
                if (data.is_error == false) {
                    if (data.is_exist == true) {
                        $scope.hasRecordFile = true;
                        $scope.btnEndLive = 'hidden';
                        console.log('Video is recorded.');
                    } else if (data.is_exist == false) {
                        $scope.hasRecordFile = false;
                        $scope.btnEndLive = '';
                        console.log('No video is recorded.');
                    }
                }

                $timeout(function() {
                    $(function () {
                        $('[data-toggle="popover"]').popover({
                            html : true,
                            trigger: 'hover',
                            container: 'body'
                        });
                    });
                }, 1000);
            });

        }

        $scope.getTopic = function() {
            topicsFactory.get({id: $routeParams.id}).success(function (data) {
                $scope.topics_data = data;
            });
        }

        $scope.streamNow = function(theLive) {
            theLive.streaming_status = 1;
            livestreamFactory.toggleStreamingStatus(theLive)
                .success(function (data) {
                    $scope.getTopic();
                });
        }

        var checkStreamingStatus = function() {
            if ($scope.topics_data.streaming_status == '1') {
                $scope.statusStreaming = 'Live';
                $scope.btnPauseResume = "<i class='fa fa-pause'></i> Pause Stream";
            } else if ($scope.topics_data.streaming_status == '0') {
                $scope.showBtnPause = false;
                $scope.statusStreaming = 'End';
            }

            // Adjust the display
            $timeout(function() {
                if ($scope.topics_data.streaming_status == '1') {
                    $scope.btnStatus = "<i class='fa fa-stop'></i> Stop Stream";
                    $scope.showBtnPause = true;
                    // $scope.btnClassStreamStatus = "active";

                } else if ($scope.topics_data.streaming_status == '0') {
                    $scope.btnStatus = "<i class='fa fa-play'></i> Stream Now";
                    $scope.btnClassStreamStatus = "";
                }

                angular.element('#btn-toggle-stream').removeClass('disabled');
                angular.element('#btn-toggle-stream').prop('disabled', false);

                $scope.checkRecordFile($scope.topics_data);
            }, 700);
        }

        var updateStreamingStatus = function(theLive) {
            livestreamFactory.toggleStreamingStatus(theLive)
                .success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);

                        topicsFactory.get({id: theLive.id}).success(function (data) {
                            $scope.topics_data = data;

                            var theLiveResults = {};
                            theLiveResults.topic_id = $routeParams.id;
                            theLiveResults.streaming_status = theLive.streaming_status;
                            $scope.updateLiveResults(theLiveResults);
                            // livestreamFactory.updateLiveResults(theLive).success(function (data) {
                            //     $scope.getLiveResults();
                            // });

                            checkStreamingStatus();
                        });
                    } else if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                .error(function () {
                    notification("error", " No Access-Control-Allow-Origin");
                });
        }

        //click live now and stop//
        $scope.toggleStreamingStatus = function (theLive) {
            theLive.admin_id = $scope.admin.id;

            if (theLive.streaming_status == 1) {
                // theLive.streaming_status = 0;
                if ($scope.isRecord) {
                    $scope.modalClassAlert = 'alert-warning';
                    $scope.modalHeaderAlert = 'เกิดข้อผิดพลาดบางอย่าง';
                    $scope.modalContentAlert = '<i class="fa fa-times text-red fa-2x"></i> <span>ไม่สามารถหยุดการถ่ายทอดสดได้! <strong>"กรุณาหยุดบันทึกการถ่ายทอดสดก่อน"</strong></span>';
                    // angular.element('#modal-alert').find('.alert-content').html('มีการบันทึกการถ่ายทอดสดอยู่! กรุณาหยุดบันทึกก่อน');
                    angular.element('#modal-alert').modal('show');
                    // notification('warning', 'กรุณาหยุดบันทึกการถ่ายทอดสด');
                } else {
                    angular.element('#modal-stop-stream').modal('show');
                }

                // angular.element('#btn-toggle-stream').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Stopping...</span>');
            } else {
                if ($scope.showBtnPause) {
                    angular.element('#btn-toggle-pause').prop('disabled', true);
                } else {
                    angular.element('#btn-toggle-pause').prop('disabled', false);
                }

                theLive.streaming_status = 1;
                theLive.streaming_pause = 0;

                angular.element('#btn-toggle-stream').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Streaming...</span>');
                angular.element('#btn-toggle-stream').button('loading');

                updateStreamingStatus(theLive);
            }
        }

        $scope.toggleVODDisplay = function (theLive) {
            theLive.admin_id = $scope.admin.id;

            if (theLive.streaming_status == 1) {
                theLive.streaming_status = 0;

                angular.element('#btn-toggle-vod-display').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Stopping...</span>');

            } else {
                theLive.streaming_status = 1;

                angular.element('#btn-toggle-vod-display').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Starting...</span>');
            }

            angular.element('#btn-toggle-vod-display').button('loading');

            livestreamFactory.toggleStreamingStatus(theLive)
                .success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);

                        topicsFactory.get({id: theLive.id}).success(function (data) {
                            $scope.topics_data = data;

                            // Adjust the display
                            $timeout(function() {
                                if (theLive.streaming_status == '1') {
                                    $scope.txtDisplayVOD = "<i class='fa fa-stop'></i> Off";
                                    $scope.showBtnPause = true;
                                } else if (theLive.streaming_status == '0') {
                                    $scope.txtDisplayVOD = "<i class='fa fa-play'></i> On";
                                }

                                angular.element('#btn-toggle-vod-display').removeClass('disabled');
                                angular.element('#btn-toggle-vod-display').prop('disabled', false);

                            }, 700);
                        });
                    } else if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                .error(function () {
                    notification("error", " No Access-Control-Allow-Origin");
                });
            // updateStreamingStatus(theLive);
        }

        $scope.chooseStopStreamFormat = function(type, btn_id) {
            $('.btn-choose').prop('disabled', true);
            $(btn_id).addClass('loading');
            $(btn_id).prop('disabled', false);
            $(btn_id).button('loading');

            var theTopic = {
                'id': $scope.topics_data.id,
                'courses_id': $scope.topics_data.courses_id,
                'title': $scope.topics_data.title,
                'parent': $scope.topics_data.parent,
                'start_time': $scope.topics_data.start_time,
                'end_time': $scope.msgTimer,
                'status': $scope.topics_data.status,
                'state': 'vod',
                'streaming_status': 0,
                'is_stop_stream': 1,
                'vod_format': type
            };

            if (type == $scope.format_stop_stream.vod_now.value) {
                // Now
                $scope.live_result.vod_format = $scope.format_stop_stream.vod_now;
            } else if (type == $scope.format_stop_stream.vod_later.value) {
                // Later
                $scope.live_result.vod_format = $scope.format_stop_stream.vod_later;
            } else if (type == $scope.format_stop_stream.end_live.value) {
                // End Live
                theTopic.status = 0;
                $scope.live_result.vod_format = $scope.format_stop_stream.end_live;
            }

            // Update topic
            topicsFactory.update(theTopic).success(function (data) {
                if (data.is_error == false) {
                    topicsFactory.get({id: $routeParams.id}).success(function (data) {
                        $scope.topics_data = data;
                    });

                    var theLiveResults = {};

                    var currentdate = new Date();
                    var getFullYear = currentdate.getFullYear();
                    var getMonth = (currentdate.getMonth()+1);

                    if (getMonth < 10) { getMonth = '0' + getMonth }

                    var getDate = currentdate.getDate();

                    if (getDate < 10) { getDate = '0' + getDate }

                    var getHours = currentdate.getHours();
                    var getMinutes = currentdate.getMinutes();
                    var getSeconds = currentdate.getSeconds();

                    var datetime = getFullYear + '-' + getMonth + '-' + getDate + ' ' + getHours + ':' + getMinutes + ':' + getSeconds;

                    theLiveResults.topic_id = $routeParams.id;
                    theLiveResults.live_end_datetime = datetime;
                    theLiveResults.video_name = $scope.topics_data.streaming_record_filename + '.mp4';
                    theLiveResults.video_status = 'complete';
                    $scope.updateLiveResults(theLiveResults);

                    // notification('success', 'อัพเดทการแสดงผลหน้าบ้านเรียบร้อยแล้ว');
                } else {
                    notification('error', data.message);
                    angular.element('#modal-stop-stream').modal('hide');

                    return false;
                }
            });

            $timeout(function() {
                if ($scope.hasRecordFile) {
                    // $scope.copyRecordedFile();
                    $scope.stopStream();
                }

                $scope.changeTab('#live-result');
                angular.element('#modal-stop-stream').modal('hide');
            }, 3000);
        }

        $scope.toggleStreamingPause = function (theLive) {
            theLive.admin_id = $scope.admin.id;

            if (theLive.streaming_pause == 1) {
                // Resume
                theLive.streaming_pause = 0;
                angular.element('#btn-toggle-pause').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Resuming...</span>');
            } else {
                // Pause
                theLive.streaming_pause = 1;
                angular.element('#btn-toggle-pause').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Pausing...</span>');
                if ($scope.isRecord) {
                    $scope.toggleRecordStatus(theLive);
                }
            }

            angular.element('#btn-toggle-pause').button('loading');

            livestreamFactory.toggleStreamingPause(theLive)
                .success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        topicsFactory.get({id: theLive.id}).success(function (data) {
                            $scope.topics_data = data;

                            // console.log(theLive.streaming_pause);

                            // if($scope.topics_data.streaming_pause == '1'){
                            //     $scope.btnPauseResume = "<i class='fa fa-play'></i> Resume Stream";
                            // }

                            // if($scope.topics_data.streaming_pause == '0'){
                            //     $scope.btnPauseResume = "<i class='fa fa-pause'></i> Pause Stream";
                            // }

                            // $scope.toggleRecordStatus($scope.topics_data);

                            if ($scope.topics_data.streaming_pause == '1') {
                                $scope.statusStreaming = 'Pause';
                            } else if($scope.topics_data.streaming_pause == '0') {
                                $scope.statusStreaming = 'Live';
                            }

                            $timeout(function() {
                                if ($scope.topics_data.streaming_pause == '1') {
                                    $scope.btnPauseResume = "<i class='fa fa-play'></i> Resume Stream";
                                } else if($scope.topics_data.streaming_pause == '0') {
                                    $scope.btnPauseResume = "<i class='fa fa-pause'></i> Pause Stream";
                                }

                                angular.element('#btn-toggle-pause').removeClass('disabled');
                                angular.element('#btn-toggle-pause').prop('disabled', false);
                            }, 700);
                        });
                    } else if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                .error(function () {
                    notification("error", " No Access-Control-Allow-Origin");
                });
        }

        //click record and stop/
        $scope.Timer = null;
        $scope.isPaused = null;

        // coursesFactory.recordTime($scope.topics_data).success(function (data) {
        //     $scope.recordTime = data.recordTime;
        // });

        // console.log($scope.recordTime);

        // if ($scope.recordTime != '') {
        //     var $i = $scope.recordTime;
        // } else {
        //     var $i = 0;
        // }

        $scope.incomingStreamDuration = function(theLive, queryStringRecorderName) {
            livestreamFactory.incomingStreamDuration({id: theLive.id}, queryStringRecorderName).success(function (data) {
                var time = data;
                var milliseconds = parseInt((time % 1000) / 100)
                , seconds = parseInt((time / 1000) % 60)
                , minutes = parseInt((time / (1000 * 60)) % 60)
                , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                hours = (hours < 10) ? "0" + hours : hours;
                minutes = (minutes < 10) ? "0" + minutes : minutes;
                seconds = (seconds < 10) ? "0" + seconds : seconds;

                $scope.msgTimer = hours+':'+minutes+':'+seconds;

                // if (minutes == '00' && seconds < 30) {
                //     // var ctd = 30 - seconds;
                //     // $scope.ctdSyncSlide = '(' + ctd + ' Sec.)';
                //     // $scope.disabledBtnSynSlide = true;

                //     // $scope.msgTimer = 'Ready in ' + ctd + ' seconds';
                // } else {
                //     time = time - 30000;
                //     var milliseconds = parseInt((time % 1000) / 100)
                //     , seconds = parseInt((time / 1000) % 60)
                //     , minutes = parseInt((time / (1000 * 60)) % 60)
                //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                //     hours = (hours < 10) ? "0" + hours : hours;
                //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                //     $scope.ctdSyncSlide = '';
                //     $scope.disabledBtnSynSlide = false;
                // }

            });
        }

        var $i = 0;

        $scope.statusBtnRec = false;
        $scope.RecorderName = '';
        $scope.toggleRecordStatus = function (theLive) {

            if ($scope.isRecord == true) {
                angular.element('#btn-toggle-record').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Stop recording...</span>');
            } else if ($scope.isRecord == false) {
                angular.element('#btn-toggle-record').data('loading-text', '<i class="fa fa-refresh fa-spin p-0"></i> <span class="m-l-5">Start recording...</span>');
            }

            $('#btn-toggle-record').button('loading');

            livestreamFactory.incomingStream({id: $routeParams.id}).success(function (data) {
                $scope.streamsReturn = data;

                var recorderName = $scope.topics_data.streaming_streamname;
                // // Start Find recorderName
                // var bitrateArray = $scope.streamsReturn.IncomingStreams.IncomingStream.map(x => x.Name);
                // var recorderName1080p = $scope.topics_data['streaming_prefix_streamname'] + '_1080p';
                // var recorderName720p = $scope.topics_data['streaming_prefix_streamname'] + '_720p';
                // var recorderName360p = $scope.topics_data['streaming_prefix_streamname'] + '_360p';
                // var recorderName240p = $scope.topics_data['streaming_prefix_streamname'] + '_240p';

                // var resultArray = [];
                // for (var i = 0; i < bitrateArray.length; i++) {
                //     if (bitrateArray[i] == recorderName1080p) {
                //         recorderName = bitrateArray[i];
                //         break;
                //     } else if (bitrateArray[i] == recorderName720p) {
                //         recorderName = bitrateArray[i];
                //         break;
                //     }  else if (bitrateArray[i] == recorderName360p) {
                //         recorderName = bitrateArray[i];
                //         break;
                //     }  else if (bitrateArray[i] == recorderName240p) {
                //         recorderName = bitrateArray[i];
                //         break;
                //     }
                // }

                var queryStringRecorderName = '&recorderName=' + recorderName;
                // End Find recorderName

                if (!angular.isUndefined($scope.streamsReturn.Recorders.StreamRecorder)) {
                    if (angular.isArray($scope.streamsReturn.Recorders.StreamRecorder)) {
                        var matchedRecorder = _.find($scope.streamsReturn.Recorders.StreamRecorder, function(o) { return o.RecorderName == recorderName; });
                        if (!angular.isUndefined(matchedRecorder)) {
                             //Stop timer
                            $scope.isPaused = true;
                            if (angular.isDefined($scope.Timer)) {
                                $interval.cancel($scope.Timer);
                            }

                            livestreamFactory.stopRecord({id: theLive.id}, queryStringRecorderName).success(function (data) {
                                livestreamFactory.incomingStream({id: theLive.id}).success(function (data) {
                                    $scope.streamsReturn = data;
                                });
                            });

                            $scope.statusRecorder = 'Not record';
                            // $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                            $scope.btnClassRecord = '';
                            $scope.isRecord = false;
                        } else {
                            livestreamFactory.startRecord({id: theLive.id}, queryStringRecorderName).success(function (data) {
                                livestreamFactory.incomingStream({id: theLive.id}).success(function (data) {
                                    $scope.streamsReturn = data;

                                    var matchedRecorder = _.find($scope.streamsReturn.Recorders.StreamRecorder, function(o) { return o.RecorderName == recorderName; });

                                    $scope.recorders_data.state = matchedRecorder.RecorderState;
                                    $scope.recorders_data.output = matchedRecorder.OutputPath;
                                    $scope.recorders_data.base_file = matchedRecorder.BaseFile;
                                    $scope.recorders_data.format_file = matchedRecorder.FileFormat;
                                    $scope.recorders_data.current_file = matchedRecorder.CurrentFile;

                                    //Run timer
                                    $scope.Timer = $interval(function () {
                                        $scope.isPaused = false;
                                        if(!$scope.isPaused) {
                                            $scope.incomingStreamDuration(theLive, queryStringRecorderName);
                                            // livestreamFactory.incomingStreamDuration({id: theLive.id}, queryStringRecorderName).success(function (data) {
                                            //     var time = data;
                                            //     var milliseconds = parseInt((time % 1000) / 100)
                                            //     , seconds = parseInt((time / 1000) % 60)
                                            //     , minutes = parseInt((time / (1000 * 60)) % 60)
                                            //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                            //     hours = (hours < 10) ? "0" + hours : hours;
                                            //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                                            //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                                            //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                                            // });
                                        }
                                    }, 1000);

                                    /*//Run timer
                                    $scope.Timer = $interval(function () {
                                        $scope.isPaused = false;
                                        if(!$scope.isPaused) {
                                            var time = $i++;
                                            var offset = Math.floor(time);
                                            var sec_num = parseInt(offset, 10); // don't forget the second param
                                            var hours   = Math.floor(sec_num / 3600);
                                            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                                            var seconds = sec_num - (hours * 3600) - (minutes * 60);
                                            if (hours   < 10) {hours   = "0"+hours;}
                                            if (minutes < 10) {minutes = "0"+minutes;}
                                            if (seconds < 10) {seconds = "0"+seconds;}
                                            $scope.msgTimer = hours+':'+minutes+':'+seconds;
                                            $scope.recordTime = time;
                                        }
                                    }, 1000);*/

                                    $scope.statusRecorder = 'Recording...';
                                    // $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                                    // $scope.btnClassRecord = 'active';
                                    $scope.isRecord = true;
                                });
                            });
                        }
                    } else if ($scope.streamsReturn.Recorders.StreamRecorder.RecorderName == recorderName) {
                         //Stop timer
                        $scope.isPaused = true;
                        if (angular.isDefined($scope.Timer)) {
                            $interval.cancel($scope.Timer);
                        }

                        livestreamFactory.stopRecord({id: theLive.id}, queryStringRecorderName).success(function (data) {
                            livestreamFactory.incomingStream({id: theLive.id}).success(function (data) {
                                $scope.streamsReturn = data;
                            });
                        });

                        $scope.statusRecorder = 'Not record';
                        // $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                        $scope.btnClassRecord = '';
                        $scope.isRecord = false;
                    } else {
                        livestreamFactory.startRecord({id: theLive.id}, queryStringRecorderName).success(function (data) {
                            livestreamFactory.incomingStream({id: theLive.id}).success(function (data) {
                                $scope.streamsReturn = data;

                                var matchedRecorder = _.find($scope.streamsReturn.Recorders.StreamRecorder, function(o) { return o.RecorderName == recorderName; });

                                $scope.recorders_data.state = matchedRecorder.RecorderState;
                                $scope.recorders_data.output = matchedRecorder.OutputPath;
                                $scope.recorders_data.base_file = matchedRecorder.BaseFile;
                                $scope.recorders_data.format_file = matchedRecorder.FileFormat;
                                $scope.recorders_data.current_file = matchedRecorder.CurrentFile;

                                //Run timer
                                $scope.Timer = $interval(function () {
                                    $scope.isPaused = false;
                                    if(!$scope.isPaused) {
                                        $scope.incomingStreamDuration(theLive, queryStringRecorderName);
                                        // livestreamFactory.incomingStreamDuration({id: theLive.id}, queryStringRecorderName).success(function (data) {
                                        //     var time = data;
                                        //     var milliseconds = parseInt((time % 1000) / 100)
                                        //     , seconds = parseInt((time / 1000) % 60)
                                        //     , minutes = parseInt((time / (1000 * 60)) % 60)
                                        //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                        //     hours = (hours < 10) ? "0" + hours : hours;
                                        //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                                        //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                                        //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                                        // });
                                    }
                                }, 1000);

                                $scope.statusRecorder = 'Recording...';
                                // $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                                // $scope.btnClassRecord = 'active';
                                $scope.isRecord = true;
                            });
                        });
                    }
                }else{
                    livestreamFactory.startRecord({id: theLive.id}, queryStringRecorderName).success(function (data) {
                        livestreamFactory.incomingStream({id: theLive.id}).success(function (data) {
                            $scope.streamsReturn = data;

                            $scope.recorders_data.state = $scope.streamsReturn.Recorders.StreamRecorder.RecorderState;
                            $scope.recorders_data.output = $scope.streamsReturn.Recorders.StreamRecorder.OutputPath;
                            $scope.recorders_data.base_file = $scope.streamsReturn.Recorders.StreamRecorder.BaseFile;
                            $scope.recorders_data.format_file = $scope.streamsReturn.Recorders.StreamRecorder.FileFormat;
                            $scope.recorders_data.current_file = $scope.streamsReturn.Recorders.StreamRecorder.CurrentFile;

                            //Run timer
                            $scope.Timer = $interval(function () {
                                $scope.isPaused = false;
                                if(!$scope.isPaused) {
                                    $scope.incomingStreamDuration(theLive, queryStringRecorderName);
                                    // livestreamFactory.incomingStreamDuration({id: theLive.id}, queryStringRecorderName).success(function (data) {
                                    //     var time = data;
                                    //     var milliseconds = parseInt((time % 1000) / 100)
                                    //     , seconds = parseInt((time / 1000) % 60)
                                    //     , minutes = parseInt((time / (1000 * 60)) % 60)
                                    //     , hours = parseInt((time / (1000 * 60 * 60)) % 24);

                                    //     hours = (hours < 10) ? "0" + hours : hours;
                                    //     minutes = (minutes < 10) ? "0" + minutes : minutes;
                                    //     seconds = (seconds < 10) ? "0" + seconds : seconds;

                                    //     $scope.msgTimer = hours+':'+minutes+':'+seconds;

                                    // });

                                    /*var time = $i++;

                                    var offset = Math.floor(time);
                                    var sec_num = parseInt(offset, 10); // don't forget the second param
                                    var hours   = Math.floor(sec_num / 3600);
                                    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                                    var seconds = sec_num - (hours * 3600) - (minutes * 60);
                                    if (hours   < 10) {hours   = "0"+hours;}
                                    if (minutes < 10) {minutes = "0"+minutes;}
                                    if (seconds < 10) {seconds = "0"+seconds;}

                                    $scope.msgTimer = hours+':'+minutes+':'+seconds;*/
                                }
                            }, 1000);

                            $scope.statusRecorder = 'Recording...';
                            // $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                            // $scope.btnClassRecord = 'active';
                            $scope.isRecord = true;

//                                if(!angular.isUndefined($scope.streamsReturn.Recorders.StreamRecorder.CurrentFile)){
//
//                                }else{
//
//                                    $('#modal-basic').modal('show');
//
//                                }
                        });
                    });
                }

                // var theLive = {};
                $timeout(function() {
                    // $('#btn-toggle-record').button('reset');
                    if ($scope.isRecord == true) {
                        $scope.btnStatusRecord = "<i class='fa fa-stop'></i> Stop Record";
                    } else if($scope.isRecord == false) {
                        $scope.btnStatusRecord = "<i class='fa fa-circle'></i> Start Record";
                    }

                    angular.element('#btn-toggle-record').removeClass('active');
                    angular.element('#btn-toggle-record').removeClass('disabled');
                    angular.element('#btn-toggle-record').prop('disabled', false);

                    if ($scope.isRecord == true) {
                        var theLiveResults = {};
                        theLiveResults.topic_id = $routeParams.id;
                        theLiveResults.is_record = 1;
                        $scope.updateLiveResults(theLiveResults);

                        notification('info', 'เริ่มบันทึกการถ่ายทอดสด');
                    } else {
                        notification('info', 'หยุดบันทึกการถ่ายทอดสด');
                    }
                }, 1000);



                // livestreamFactory.updateLiveResults(theLive).success(function (data) {
                //     $scope.getLiveResults();
                // });

                $scope.checkRecordFile(theLive);

            });
        };

        $scope.stopStream = function() {
            angular.element('#wrapper-action').addClass('hidden');

            videosFactory.getVideo('topics/' + $scope.topics_data.streaming_record_part, $scope.topics_data.streaming_record_filename + '.mp4').success(function (get_video_data){
                $scope.get_video = get_video_data;

                if ($scope.get_video.file !== null) {

                    if ($scope.get_video.file.type == '' || $scope.get_video.file.type == null) {
                        $scope.get_video.file.type = 'video/mp4';
                    }

                    var theVideos = {
                        topic_id: $routeParams.id,
                        dir_name: 'topics/' + $scope.topics_data.streaming_record_part,
                        name: $scope.get_video.file.name,
                        size: $scope.get_video.file.size,
                        extFile: $scope.get_video.file.extFile,
                        type: $scope.get_video.file.type,
                        contentType: $scope.get_video.file.contentType,
                        isVideoType: $scope.get_video.file.isVideoType,
                        url: $scope.get_video.file.url,
                        deleteType: $scope.get_video.file.deleteType,
                        deleteUrl: $scope.get_video.file.deleteUrl,
                        deleteWithCredentials: $scope.get_video.file.deleteWithCredentials,
                        modifiedDateFile: $scope.get_video.file.modifiedDate
                    };

                    videosFactory.createVideo(theVideos).success(function (data_video_create){
                        if (data_video_create.exists) {
                            notification("success", data_video_create.message);
                            $location.path('topics/' + theLive.id + '/edit');
                        } else {
                            // var theLiveResults = {};
                            // theLiveResults.topic_id = $routeParams.id;
                            // theLiveResults.video_status = 'complete';

                            // $scope.updateLiveResults(theLiveResults);

                            $scope.topics_data.streaming_url = $scope.topics_data.streaming_server_cdn + '/winner-original/_definst_/topics/' + $scope.topics_data.streaming_record_part + '/mp4:' + theVideos.name + '/playlist.m3u8';
                            // if ($scope.topics_data.vod_format != 'end_live') {
                            //     $scope.topics_data.is_auto_convert = 1;
                            // }

                            if ($scope.topics_data.vod_format == 'vod_now') {
                                $scope.topics_data.streaming_status = 1;
                                $scope.topics_data.is_auto_convert = 1;
                            }

                            topicsFactory.update($scope.topics_data).success(function (data) {
                                if (data.is_error == false) {
                                    notification('success', 'จัดเตรียมวีดีโอต้นฉบับเสร็จสิ้น');

                                    if ($scope.topics_data.vod_format != 'end_live') {
                                        $timeout(function() {
                                            notification('info', 'กำลังเปลี่ยนไปหน้าจัดการวีดีโอ...');

                                            $timeout(function() {
                                                $location.path('topics/' + $scope.topics_data.id + '/edit').search({'action': 'video_management'});
                                            }, 3000);
                                        }, 1000);
                                    } else {
                                        angular.element('#wrapper-action').removeClass('hidden');
                                    }
                                } else {
                                    notification("error", data.message);
                                }
                            });


                            // });
                        }
                    });
                }
            });
        }

        $scope.copyRecordedFile = function() {
            // notification('info', '<i class="fa fa-info-circle"></i> กำลังจัดเตรียมวีดีโอต้นฉบับ');

            $('#tr-record').addClass('hidden');

            var theLive = {};
            theLive.id = $routeParams.id;
            theLive.type = 'topics';
            theLive.filename = $scope.topics_data.streaming_record_filename;
            theLive.dir_name = $scope.topics_data.streaming_record_part;

            livestreamFactory.copyRecordedFile(theLive).success(function (data){
                if (data.is_error == false) {
                    var theLiveResults = {};
                    theLiveResults.topic_id = $routeParams.id;
                    theLiveResults.video_name = theLive.filename + '.mp4';
                    theLiveResults.video_status = 'progressing';
                    // theLiveResults.filesize = data.filesize_from;

                    livestreamFactory.updateLiveResults(theLiveResults).success(function (data) {
                        $scope.getLiveResults();
                    });

                    angular.element('#wrapper-action').addClass('hidden');

                    $scope.checkOriginalFile(theLive, data.filesize_from);
                }
            });
        }

        $scope.checkOriginalFile = function (theLive, filesize_from) {
            var queryString = '&type=' + theLive.type + '&filename=' + theLive.filename + '&dir_name=' + theLive.dir_name + '&filesize_from=' + filesize_from;
            livestreamFactory.checkOriginalFile(queryString).success(function (data){
                if (data.is_equal == false) {
                    $timeout(function() {
                        $scope.checkOriginalFile(theLive, filesize_from);
                    }, 10000);
                } else if (data.is_equal == true) {
                    videosFactory.getVideo('topics/' + theLive.dir_name, theLive.filename + '.mp4').success(function (get_video_data){
                        $scope.get_video = get_video_data;

                        if ($scope.get_video.file !== null) {

                            if ($scope.get_video.file.type == '' || $scope.get_video.file.type == null) {
                                $scope.get_video.file.type = 'video/mp4';
                            }

                            var theVideos = {
                                topic_id: theLive.id,
                                dir_name: 'topics/' + theLive.dir_name,
                                name: $scope.get_video.file.name,
                                size: $scope.get_video.file.size,
                                extFile: $scope.get_video.file.extFile,
                                type: $scope.get_video.file.type,
                                contentType: $scope.get_video.file.contentType,
                                isVideoType: $scope.get_video.file.isVideoType,
                                url: $scope.get_video.file.url,
                                deleteType: $scope.get_video.file.deleteType,
                                deleteUrl: $scope.get_video.file.deleteUrl,
                                deleteWithCredentials: $scope.get_video.file.deleteWithCredentials,
                                modifiedDateFile: $scope.get_video.file.modifiedDate
                            };

                            videosFactory.createVideo(theVideos).success(function (data_video_create){
                                if (data_video_create.exists) {
                                    notification("success", data_video_create.message);
                                    $location.path('topics/' + theLive.id + '/edit');
                                } else {
                                    var theLiveResults = {};
                                    theLiveResults.topic_id = $routeParams.id;
                                    theLiveResults.video_status = 'complete';

                                    $scope.updateLiveResults(theLiveResults);

                                    $scope.topics_data.streaming_url = $scope.topics_data.streaming_server_cdn + '/test_elearning_original/_definst_/topics/' + $scope.topics_data.streaming_record_part + '/mp4:' + theVideos.name + '/playlist.m3u8';
                                    // if ($scope.topics_data.vod_format != 'end_live') {
                                    //     $scope.topics_data.is_auto_convert = 1;
                                    // }

                                    if ($scope.topics_data.vod_format == 'vod_now') {
                                        $scope.topics_data.streaming_status = 1;
                                        $scope.topics_data.is_auto_convert = 1;
                                        // $scope.topics_data.state = 'vod';
                                    }

                                    topicsFactory.update($scope.topics_data).success(function (data) {
                                        if (data.is_error == false) {
                                            notification('success', 'จัดเตรียมวีดีโอต้นฉบับเสร็จสิ้น');

                                            if ($scope.topics_data.vod_format != 'end_live') {
                                                $timeout(function() {
                                                    notification('info', 'กำลังเปลี่ยนไปหน้าจัดการวีดีโอ...');

                                                    $timeout(function() {
                                                        $location.path('topics/' + $scope.topics_data.id + '/edit').search({'action': 'video_management'});
                                                    }, 3000);
                                                }, 1000);
                                            } else {
                                                angular.element('#wrapper-action').removeClass('hidden');
                                            }
                                        } else {
                                            notification("error", data.message);
                                        }
                                    });


                                    // });
                                }
                            });
                        }
                    });
                }
            });
        }


        $scope.testNoty = function() {
            // $location.path('topics/' + $scope.topics_data.id + '/edit').search({'action': 'video_management'});
            // $location.path('topics/' + $scope.topics_data.id + '/edit?action=video_management');
            // notification('success', 'จัดเตรียมวีดีโอต้นฉบับเสร็จสิ้น');
        }

        $scope.setTooltip = function () {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
        }

        $timeout(function(){
            $scope.setTooltip();

            $('.dropdown-menu').click(function(e) {
                e.stopPropagation();
            });

            $(document).ready(function() {
                $(document).on('mouseenter', '#btn-on-demand', function() {
                    if (!$(this).is(':disabled')) {
                        if ($(this).closest('.btn-group').find('.dropdown-menu').is(":visible")) {
                            $(this).popover('hide');
                        } else {
                            $(this).popover('show');
                        }
                    }
                }).on('mouseleave', '#btn-on-demand', function(event) {
                    event.preventDefault();
                    /* Act on the event */
                });

                $('.alert-close').on('click', function(event) {
                    event.preventDefault();
                    $(this).closest('.alert').remove();
                    /* Act on the event */
                });

                $('#resultLiveCollapseOne').on('show.bs.collapse', function(event) {
                    $("div[href='#resultLiveCollapseOne']").find('i').addClass('fa-caret-down').removeClass('fa-caret-right');
                });

                $('#resultLiveCollapseOne').on('hide.bs.collapse', function(event) {
                    $("div[href='#resultLiveCollapseOne']").find('i').addClass('fa-caret-right').removeClass('fa-caret-down');
                });

                $('#resultLiveCollapseTwo').on('show.bs.collapse', function(event) {
                    $("div[href='#resultLiveCollapseTwo']").find('i').addClass('fa-caret-down').removeClass('fa-caret-right');
                });

                $('#resultLiveCollapseTwo').on('hide.bs.collapse', function(event) {
                    $("div[href='#resultLiveCollapseTwo']").find('i').addClass('fa-caret-right').removeClass('fa-caret-down');
                });

                // $('.choice-stop-stream').mouseenter(function() {
                //     if ($(this).hasClass('theme-primary')) {
                //         $(this).find('button').removeClass('btn-default');
                //         $(this).find('button').addClass('btn-primary');
                //     } else if ($(this).hasClass('theme-warning')) {
                //         $(this).find('button').removeClass('btn-default');
                //         $(this).find('button').addClass('btn-warning');
                //     }
                // }).mouseleave(function() {
                //     if ($(this).hasClass('theme-primary')) {
                //         $(this).find('button').addClass('btn-default');
                //         $(this).find('button').removeClass('btn-primary');
                //     } else if ($(this).hasClass('theme-warning')) {
                //         $(this).find('button').addClass('btn-default');
                //         $(this).find('button').removeClass('btn-warning');
                //     }
                // });
            });
        }, 1000);

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
            } else if (status == "warning") {
                var n = noty({
                    text: '<div class="alert alert-warning"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else if (status == "info") {
                var n = noty({
                    text: '<div class="alert alert-info"><p><strong> ' + alert + ' </strong></p></div>',
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
