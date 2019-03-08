'use strict';

angular.module('newApp')
    .controller('coursesVideosUploadCtrl', ['$scope', '$rootScope', '$q', '$timeout', '$interval', '$window', '$location', '$filter', 'coursesFactory', 'ffmpegFactory', 'videosFactory', 'transcodingsFactory', 'settingsFactory', 'functionsFactory',
    function($scope, $rootScope, $q, $timeout, $interval, $window, $location, $filter, coursesFactory, ffmpegFactory, videosFactory, transcodingsFactory, settingsFactory, functionsFactory) {

        $scope.fileOnProgress = '';
        $scope.isUploadingDirty = false;
        $scope.isPrepareUploadExist = false;
        $scope.baseStreamUrlType = 'full';
        // $scope.filters = {};

        // Detect window close
        $window.onbeforeunload = function() {
            if ($scope.isUploadingDirty) {
                return "If you leave this page now, your video uploading will not be saved and may cause problems. Are you sure you want to leave this page?";
            }
        };

        var $routeChangeStartUnbind = $rootScope.$on('$routeChangeStart', function(event, newUrl) {
            if ($scope.isUploadingDirty) {
                $rootScope.pageloaded = true;//prevent loading icon

                var $modalConfirmLeavePage = $('#modal-confirm-leave-page');
                $modalConfirmLeavePage.modal('show');

                $modalConfirmLeavePage.on('click', '.btn-confirm-leave-page', function (e) {
                    $routeChangeStartUnbind();
                    $location.path(newUrl.$$route.originalPath); //Go to page they're interested in
                    $rootScope.pageloaded = false;
                    $scope.unsavedChanges = false;
                    $modalConfirmLeavePage.modal('hide');
                    $timeout(function() {
                        $window.location.href= "#"+newUrl.$$route.originalPath;//$route.reload() does not reload services
                        $scope.isUploadingDirty = false;
                    }, 500);
                });

                $modalConfirmLeavePage.on('hide.bs.modal', function () {
                    angular.element('.nav-sidebar > li').removeClass('active').removeClass('nav-active').find('.children > li').removeClass('active');
                    angular.element('.page-spinner-loader').addClass('hide');
                });

                event.preventDefault();
            }
        });

        var checkStatusTranscode = function() {
            for (var i = 0; i < $scope.videos.length; i++) {
                for (var y = 0; y < $scope.videos[i].transcodings.length; y++) {
                    switch ($scope.videos[i].transcodings[y].transcode_status) {
                        case 'converting':
                            $scope.videos[i].transcodings[y].labelClass = 'label-warning';
                            $scope.videos[i].transcodings[y].enableViewTranscodeProcess = true;
                            $scope.videos.enableConvert = false;
                        break;

                        case 'error':
                            $scope.videos[i].transcodings[y].labelClass = 'label-danger';
                            $scope.videos[i].transcodings[y].enableViewTranscodeProcess = false;
                        break;

                        case 'converted':
                            $scope.videos[i].transcodings[y].labelClass = 'label-success';
                            $scope.videos[i].transcodings[y].enableViewTranscodeProcess = false;
                        break;

                        default:
                            $scope.videos[i].transcodings[y].labelClass = 'label-default';
                            $scope.videos[i].transcodings[y].enableViewTranscodeProcess = false;
                    }
                }
            }
        };

        $scope.checkStatusConvert = function(theVideos, theTranscodings) {
            $('#modal-transcode-processing').modal('show');

            $scope.isUploadingDirty = true;

            functionsFactory.notification("success", 'Bitrate ' +theTranscodings.title + 'P is converting.');

            var deferredCovert = $q.defer();
            chceckTranscodeProcess({"video": theVideos, "transcoding": theTranscodings, "isSpecify": true}).then(function() {
                theTranscodings.transcode_status = 'converted';
                transcodingsFactory.update(theTranscodings).success(function() {
                    checkStatusTranscode();
                    $('#modal-transcode-processing').modal('hide');
                    deferredCovert.resolve(theVideos);
                    $scope.isUploadingDirty = false;
                });
            });
        };

        var videos_query_success = function(videosData) {
            $scope.videos = videosData.data;
            // console.log($scope.videos);
            $scope.videos.enableConvert = true;

            $scope.queue = angular.copy($scope.videos);

            // if ($scope.queue.length > 0) {
            //     angular.element('.fileupload-buttonbar').hide();
            // }

            checkStatusTranscode();
        };

        var videos_query = function (page, per_page, filters) {
            var query_string = "&course_id="+$scope.courses_data.id;


            videosFactory.query(query_string).success(videos_query_success);
        };

        var chceckTranscodeProcess = function(objData) {
            var deferredCheckTranscodeProcess = $q.defer();
            var stop;
            var query;

            $scope.videos.enableConvert = false;

            objData.isSpecify = objData.isSpecify !== undefined ? objData.isSpecify : false;
            objData.isForceConvert = objData.isForceConvert !== undefined ? objData.isForceConvert : false;

            if (objData.transcoding.transcode_status === 'inappropriate' && objData.isForceConvert === false) {
                deferredCheckTranscodeProcess.resolve({"transcoding": objData.transcoding, "isReConvert": objData.isReConvert});
            }

            if (objData.transcoding.log_file !== "" && objData.transcoding.log_file !== null) {
                query = '&log_file='+objData.transcoding.log_file;
                if (objData.isSpecify === true) {
                    query += '&isSpecify=1';
                }
            } else {
                query = '&dir_name='+objData.video.dir_name;
            }

            objData.transcoding.enableViewTranscodeProcess = true;

            stop = $interval(function() {
                ffmpegFactory.getTranscodeProcess(query).success(function(respProcess) {
                    console.log(respProcess.message);

                    $('#modal-transcode-processing').find('.process-bitrate').text(objData.transcoding.title + 'p');
                    $('#modal-transcode-processing').find('.process-detail').text(respProcess.message);

                    if (!respProcess.isFileProcessing) {
                        $interval.cancel(stop);
                        $scope.videos.enableConvert = true;
                        objData.transcoding.enableViewTranscodeProcess = false;
                        deferredCheckTranscodeProcess.resolve({"transcoding": objData.transcoding, "isReConvert": objData.isReConvert});
                    }
                });
            }, 2000);

            return deferredCheckTranscodeProcess.promise;
        };

        var generateSmil = function(theVideos) {
            var deferred = $q.defer();
            var theFFMpegParams = {
                "filename": theVideos.name,
                "dir_name": theVideos.dir_name
            };

            ffmpegFactory.generateSmil(theFFMpegParams).success(function(resp) {
                theVideos.smil_name = resp.smil_name;
                theVideos.smil_url = resp.vodPath;

                videosFactory.update(theVideos)
                    .success(function (data) {
                        if (data.is_error == false) {
                            functionsFactory.notification("success", data.message);
                            deferred.resolve({
                                "streaming":resp,
                                "theVideos": theVideos,
                                "streamUrlType": theVideos.streamUrlType
                            });
                        }
                        if (data.is_error == true) {
                            functionsFactory.notification("error", data.message);
                            deferred.reject(data);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.notification("error", " No Access-Control-Allow-Origin");
                        deferred.reject(data);
                    });

                // deferred.resolve(resp, theVideos);
            }).error(function(resp) {
                deferred.reject(resp);
            });

            return deferred.promise;
        };

        var generateSmilByOriginal = function(theVideos) {
            var deferred = $q.defer();
            var theFFMpegParams = {
                "filename": theVideos.name,
                "dir_name": theVideos.dir_name,
                "type": "original"
            };

            ffmpegFactory.generateSmil(theFFMpegParams).success(function(resp) {
                theVideos.smil_url = resp.vodPath;

                videosFactory.update(theVideos)
                    .success(function (data) {
                        if (data.is_error == false) {
                            functionsFactory.notification("success", data.message);
                            deferred.resolve({
                                "streaming": resp,
                                "theVideos": theVideos,
                                "streamUrlType": theVideos.streamUrlType
                            });
                        }
                        if (data.is_error == true) {
                            functionsFactory.notification("error", data.message);
                            deferred.reject(data);
                        }
                    })
                    .error(function (data) {
                        functionsFactory.notification("error", " No Access-Control-Allow-Origin");
                        deferred.reject(data);
                    });

                // deferred.resolve(resp, theVideos);
            }).error(function(resp) {
                deferred.reject(resp);
            });

            return deferred.promise;
        };

        var updateUrlStream = function(objData) {
            var deferred = $q.defer();

            if (objData.streaming !== undefined && objData.streaming !== null) {
                if (objData.streamUrlType !== undefined) {
                    if (objData.streamUrlType === 'review') {
                        $scope.courses_data.review_streaming_url = objData.streaming.vodPath;
                    } else {
                        $scope.courses_data.streaming_url = objData.streaming.vodPath;
                    }
                } else if ($scope.baseStreamUrlType === 'review') {
                    $scope.courses_data.review_streaming_url = objData.streaming.vodPath;
                } else {
                    $scope.courses_data.streaming_url = objData.streaming.vodPath;
                }
            } else if (objData.streamUrlType !== undefined) {
                if (objData.streamUrlType === 'review') {
                    $scope.courses_data.review_streaming_url = objData.theVideos.smil_url;
                } else {
                    $scope.courses_data.streaming_url = objData.theVideos.smil_url;
                }
            } else if ($scope.baseStreamUrlType === 'review') {
                $scope.courses_data.review_streaming_url = objData.theVideos.smil_url;
            } else {
                $scope.courses_data.streaming_url = objData.theVideos.smil_url;
            }

            coursesFactory.update($scope.courses_data)
                .success(function (data) {
                    if (data.is_error == false) {
                        functionsFactory.notification("success", data.message);
                        deferred.resolve(data);
                    }
                    if (data.is_error == true) {
                        functionsFactory.notification("error", data.message);
                        deferred.reject(data);
                    }
                })
                .error(function (data) {
                    functionsFactory.notification("error", " No Access-Control-Allow-Origin");
                    deferred.reject(data);
                });

            return deferred.promise;
        };

        var getVideoInfo = function(query) {
            var deferred = $q.defer();

            ffmpegFactory.getVideoInfo(query).success(function(data) {
                deferred.resolve(data);
            }).error(function(data) {
                deferred.reject(data);
            });

            return deferred.promise;
        };

        $scope.updateUrlStream = function(objStreamingUrl, theVideos, streamUrlType) {
            updateUrlStream({
                "streaming": objStreamingUrl,
                "theVideos": theVideos,
                "streamUrlType": streamUrlType
            });
        };

        $scope.generateSmil = function(theVideos, streamUrlType) {
            if (streamUrlType === "review") {
                theVideos.streamUrlType = "review";
            } else {
                theVideos.streamUrlType = "full";
            }

            generateSmil(theVideos).then(updateUrlStream);
        };

        $scope.generateSmilByOriginal = function(theVideos, streamUrlType) {
            if (streamUrlType === "review") {
                theVideos.streamUrlType = "review";
            } else {
                theVideos.streamUrlType = "full";
            }

            generateSmilByOriginal(theVideos).then(updateUrlStream);
        };

        var loopTranscode = function(obj) {
            var deferred = $q.defer();
            var promiseTranscode = $q.when();

            console.log(obj.video);
            angular.forEach(obj.video.transcodings, function (transcoding, countTranscoding) {
                promiseTranscode = promiseTranscode.then(function() {
                    var deferredSendTranscode = $q.defer();

                    if (obj.isReConvert === true) {
                        deferredSendTranscode.resolve({"video": obj.video, "transcoding": transcoding, "isReConvert": true});
                    } else {
                        deferredSendTranscode.resolve({"video": obj.video, "transcoding": transcoding});
                    }

                    return deferredSendTranscode.promise;
                }).then(chceckTranscodeProcess).then(function(respTranscodeProcess) {

                    // console.log(respTranscodeProcess);
                    // console.log($scope.videos.enableConvert);

                    var deferredCovert = $q.defer();

                    if ($scope.videos.enableConvert) {
                        if (transcoding.transcode_status === 'waiting' || respTranscodeProcess.isReConvert) {
                            transcoding.transcode_status = 'converting';
                            transcoding.labelClass = 'label-warning';
                            $scope.videos.enableConvert = false;

                            transcodingsFactory.update(transcoding).then(function (resp) {
                                if (resp.data.is_error == false) {
                                    var transcodeParams = '&dir_name='+obj.video.dir_name+'&filename='+obj.video.name+'&preset='+transcoding.title+'p';
                                    ffmpegFactory.convertTranscode(transcodeParams).success(function(respTranscode) {

                                        functionsFactory.notification("success", respTranscode.message);

                                        transcoding.filename = respTranscode.filename;
                                        transcoding.log_file = respTranscode.logs_filename;
                                        transcoding.url = respTranscode.download_url;
                                        transcodingsFactory.update(transcoding).success(function() {
                                            chceckTranscodeProcess({"video": obj.video, "transcoding": transcoding, "isSpecify": true}).then(function() {
                                                transcoding.transcode_status = 'converted';
                                                transcodingsFactory.update(transcoding).success(function() {
                                                    checkStatusTranscode();
                                                    $('#modal-transcode-processing').modal('hide');
                                                    deferredCovert.resolve(obj.video);
                                                });
                                            });
                                        });

                                    }).error(function(respTranscode) {
                                        transcoding.transcode_status = 'error';
                                        transcoding.transcode_status_remark = respTranscode.message;
                                        transcodingsFactory.update(transcoding).success(function() {
                                            checkStatusTranscode();
                                            deferredCovert.reject(respTranscode);
                                        });
                                    });
                                } else {
                                    functionsFactory.notification("error", resp.data.message);
                                    deferredCovert.reject(resp.data);
                                }
                            }, function(resp) {
                                deferredCovert.reject(resp.data);
                            });
                        } else {
                            deferredCovert.resolve(obj.video);
                        }
                    } else {
                        deferredCovert.resolve(obj.video);
                    }

                    return deferredCovert.promise;

                });
            });

            promiseTranscode.then(generateSmil).then(updateUrlStream).then(function(data) {
                // console.log("end");
                // console.log(data);
                $timeout(function() {
                    // console.log(data);
                    $scope.videos.enableConvert = true;
                    deferred.resolve();
                }, 1000);
            }, function(data) {
                console.log('error');
                console.log(data);
            });

            // promiseTranscode.then(function(video) {
            //     $timeout(function() {
            //         $scope.videos.enableConvert = true;
            //         deferred.resolve();
            //         console.log('END');
            //     }, 1000);
            // });

            return deferred.promise;
        };

        var videos_auto_convert = function (obj) {
            var query_string = "&course_id="+$scope.courses_data.id;
            // var promiseTranscode = $q.when();

            videosFactory.query(query_string).success(videos_query_success)
                .then(function(videosData) {
                    var deferred = $q.defer();

                    console.log(videosData);

                    // var arrVideo = videosData.data.data.filter(function(video) {
                    //     return video.id === obj.videoId;
                    // });

                    var arrVideo = $filter('filter')(videosData.data.data, {"id": obj.videoId});

                    if (arrVideo.length > 0) {
                        deferred.resolve({"video": arrVideo[0], "isReConvert": obj.isReConvert});
                    } else {
                        deferred.reject('check videos');
                    }

                    return deferred.promise;
                })
                .then(loopTranscode)
                .then(function(data) {
                    console.log('DONE');
                    $scope.isUploadingDirty = false;
                }, function(reason) {
                    console.log(reason);
                    $scope.isUploadingDirty = false;
                });
        };

        $scope.showVideoInfo = function(theVideos, theTranscodings, type) {
            var $modalTranscodeInfo = $('#modal-transcode-info');
            var query;

            $modalTranscodeInfo.off();

            if (type === undefined) {
                type = "transcode";
            }

            query = "?type="+type+"&dir_name="+theVideos.dir_name+"&filename="+theTranscodings.filename;

            getVideoInfo(query).then(function(data) {
                $modalTranscodeInfo.find('.file-name').html(data.Filename);
                $modalTranscodeInfo.find('.file-detail').html(functionsFactory.syntaxHighlight(data));
                $modalTranscodeInfo.modal('show');
            }, function(data) {
                $modalTranscodeInfo.find('.file-name').html(data.Filename);
                $modalTranscodeInfo.find('.file-detail').html(functionsFactory.syntaxHighlight(data));
                $modalTranscodeInfo.modal('show');
            });
        };

        $scope.convertTranscode = function(theVideos, theTranscodings) {
            var deferred = $q.defer();
            var promiseTranscode = $q.when();

            $scope.isUploadingDirty = true;

            promiseTranscode = promiseTranscode.then(function() {
                var deferredSendTranscode = $q.defer();
                deferredSendTranscode.resolve({"video": theVideos, "transcoding": theTranscodings, "isSpecify": false, "isForceConvert": true});
                return deferredSendTranscode.promise;
            }).then(chceckTranscodeProcess).then(function(respTranscodeProcess) {

                var deferredCovert = $q.defer();

                if ($scope.videos.enableConvert) {
                    // if (theTranscodings.transcode_status === 'waiting') {
                        theTranscodings.transcode_status = 'converting';
                        theTranscodings.labelClass = 'label-warning';
                        $scope.videos.enableConvert = false;

                        transcodingsFactory.update(theTranscodings).then(function (resp) {
                            if (resp.data.is_error == false) {
                                var transcodeParams = '&dir_name='+theVideos.dir_name+'&filename='+theVideos.name+'&preset='+theTranscodings.title+'p';
                                ffmpegFactory.convertTranscode(transcodeParams).success(function(respTranscode) {

                                    functionsFactory.notification("success", respTranscode.message);

                                    theTranscodings.filename = respTranscode.filename;
                                    theTranscodings.log_file = respTranscode.logs_filename;
                                    theTranscodings.url = respTranscode.download_url;
                                    transcodingsFactory.update(theTranscodings).success(function() {
                                        chceckTranscodeProcess({"video": theVideos, "transcoding": theTranscodings, "isSpecify": true}).then(function() {
                                            theTranscodings.transcode_status = 'converted';
                                            transcodingsFactory.update(theTranscodings).success(function() {
                                                checkStatusTranscode();
                                                $('#modal-transcode-processing').modal('hide');
                                                deferredCovert.resolve(theVideos);
                                            });
                                        });
                                    });

                                }).error(function(respTranscode) {
                                    theTranscodings.transcode_status = 'error';
                                    theTranscodings.transcode_status_remark = respTranscode.message;
                                    transcodingsFactory.update(theTranscodings).success(function() {
                                        checkStatusTranscode();
                                        deferredCovert.reject(respTranscode);
                                    });
                                });
                            } else {
                                functionsFactory.notification("error", resp.data.message);
                                deferredCovert.reject(resp.data);
                            }
                        }, function(resp) {
                            deferredCovert.reject(resp.data);
                        });
                    // } else {
                    //     deferredCovert.resolve(theVideos);
                    // }
                } else {
                    deferredCovert.resolve(theVideos);
                }

                return deferredCovert.promise;

            });

            promiseTranscode.then(generateSmil).then(updateUrlStream).then(function(data) {
                $timeout(function() {
                    $scope.videos.enableConvert = true;
                    $scope.isUploadingDirty = false;
                    deferred.resolve(theTranscodings);
                }, 1000);
            }, function(data) {
                console.log('error');
                $scope.videos.enableConvert = true;
                $scope.isUploadingDirty = false;
                deferred.reject(theTranscodings);
            });

            return deferred.promise;
        };

        var initailVideosConvert = function(file, isVideoExist) {
            var dataFiles = {};
            // var subDir = sub_dir !== undefined && sub_dir !== null ? '/' + sub_dir : '';

            if (angular.isArray(file)) {
                dataFiles = file[0];
            } else {
                dataFiles = file;
            }

            dataFiles.isUploadingDirty = true;
            $scope.isUploadingDirty = true;

            isVideoExist = isVideoExist !== undefined ? isVideoExist : false;

            if (isVideoExist === true) {
                if (dataFiles.transcodings.length === 0) {
                    ffmpegFactory.checkBitrates($.param({dir_name: dataFiles.dir_name, filename: dataFiles.name}))
                        .success(function(dataBitrates) {
                            var theTranscodings = {
                                video_id: dataFiles.id,
                                bitrates: dataBitrates
                            };

                            transcodingsFactory.createByBitrates(theTranscodings).success(function(resp) {
                                if (resp.is_error == false) {

                                    videos_auto_convert({"videoId": dataFiles.id});

                                }
                            });
                        });
                } else {
                    videos_auto_convert({"videoId": dataFiles.id, "isReConvert": true});
                }
            } else {
                var theVideos = {
                    course_id: $scope.courses_data.id,
                    dir_name: $scope.courses_data.dir_name,
                    name: dataFiles.name,
                    size: dataFiles.size,
                    extFile: dataFiles.extFile,
                    type: dataFiles.type,
                    contentType: dataFiles.contentType,
                    isVideoType: dataFiles.isVideoType,
                    url: dataFiles.url,
                    deleteType: dataFiles.deleteType,
                    deleteUrl: dataFiles.deleteUrl,
                    deleteWithCredentials: dataFiles.deleteWithCredentials,
                    modifiedDateFile: dataFiles.modifiedDate
                };

                videosFactory.create(theVideos)
                    .success(function (data) {
                        if (data.is_error == false) {
                            functionsFactory.notification("success", data.message);
                            // window.location.reload();
                            // $location.reload();
                            // $route.reload();

                            ffmpegFactory.checkBitrates($.param({dir_name: theVideos.dir_name, filename: theVideos.name}))
                                .success(function(dataBitrates) {
                                    var theTranscodings = {
                                        video_id: data.id,
                                        bitrates: dataBitrates
                                    };

                                    transcodingsFactory.createByBitrates(theTranscodings).success(function(resp) {
                                        if (resp.is_error == false) {

                                            videos_auto_convert({"videoId": data.id});

                                        }
                                    });
                                });
                        }
                        if (data.is_error == true) {
                            functionsFactory.notification("error", data.message);
                        }
                    })
                    .error(function () {
                        functionsFactory.notification("error", " No Access-Control-Allow-Origin");
                    });
            }


            // $.each(dataFiles, function (index) {
            //     console.log(index);
            // });
        };

        $timeout(function() {
            videos_query();

            $scope.options = {
                url: settingsFactory.getUpload("chunk_video") + '?base_dir=courses',
                maxChunkSize: 2000000,
                acceptFileTypes: /(\.|\/)(mp4|ts)$/i,
                formData: function (form) {
                    // return [
                    //     {
                    //         name: '_token', value: $('input[name=_token]').val()
                    //     }
                    // ];
                    return form.serializeArray();
                },
                limitMultiFileUploads: 1,
                prependFiles: true
            };
            // $scope.loadingFiles = true;
            // coursesFactory.getUploadVideos("dir_name=" + $scope.courses_data.dir_name + '/record')
            //     .then(
            //         function (response) {
            //             $scope.loadingFiles = false;
            //             $scope.queueRecord = response.data.files || [];

            //             // if ($scope.queueRecord.length > 0) {
            //             //     angular.element('.fileupload-buttonbar').hide();
            //             // }
            //         },
            //         function () {
            //             $scope.loadingFiles = false;
            //         }
            //     );

            $scope.$on('fileuploadadd', function(e, data){
                // angular.element('.fileupload-buttonbar').hide();
                $scope.isPrepareUploadExist = true;
            });

            $scope.$on('fileuploadstart', function(e, data){
                $scope.isUploadingDirty = true;
                // $scope.videos.enableConvert = false;
            });

            $scope.$on('fileuploaddone', function(e, data){
                functionsFactory.notification("success", "Successfully video uploaded.");
                $scope.isPrepareUploadExist = false;
                initailVideosConvert(data.result.files);
            });

            $scope.$on('fileuploadchunkdone', function(e, data){
                $scope.fileOnProgress = data.result.files[0].name;
            });

            $scope.$on('fileuploadchunkfail', function(e, data){
                if (e.defaultPrevented) {
                    return false;
                }

                if (data.errorThrown === 'abort') {

                    if ($scope.fileOnProgress === '') {
                        $scope.fileOnProgress = data.files[0].name;
                    }

                    $.ajax({
                        url: settingsFactory.getUpload("chunk_video") + "?file=" + $scope.fileOnProgress + "&dir_name=" + $scope.courses_data.dir_name,
                        dataType: 'json',
                        type: 'DELETE',
                        success: function() {
                            $scope.isUploadingDirty = false;
                            $scope.isPrepareUploadExist = false;
                        }
                    });
                }
            });

            $scope.$on('fileuploaddestroyed', function(e, data){
                // $scope.fileOnProgress = data.result.files[0].name;
                console.log("End");
                console.log(e);
                console.log(data);
            });

            $scope.$on('fileuploadfail', function(e, data){
                $scope.isPrepareUploadExist = false;
            });

            $scope.saveConvert = function(video) {
                // videos_auto_convert();
                initailVideosConvert(video, true);
            };

        }, 500);


    }])
    .controller('coursesFileDestroyController', [
        '$scope', '$http', '$q', '$filter', '$timeout', 'coursesFactory', 'videosFactory', 'functionsFactory',
        function ($scope, $http, $q, $filter, $timeout, coursesFactory, videosFactory, functionsFactory) {
            var file = $scope.file,
                state;


            if (file.url) {
                file.$state = function () {
                    return state;
                };
                file.$destroy = function (index) {
                    state = 'pending';
                    /* ===== Start Custom ===== */
                    if (!confirm("Are you sure to delete " + file.name + " ?")) {
                        state = 'rejected';
                        return $q.when();
                    } else {
                        if ($filter('filter')($scope.videos[index].transcodings, {"transcode_status": "converting"}).length > 0) {
                            functionsFactory.notification("error", "Cannot delete this file. The file is busy.")
                            state = 'rejected';
                            return $q.when();
                        } else {
                            videosFactory.delete(file).then(function(data) {
                                $scope.videos.splice(index,1);
                                return $http({
                                    url: file.deleteUrl,
                                    method: file.deleteType
                                });
                            }).then(
                                function () {
                                    state = 'resolved';
                                    $scope.clear(file);
                                    angular.element('.fileupload-buttonbar').show();
                                },
                                function () {
                                    state = 'rejected';
                                }
                            );
                        }
                    }
                    /* ===== End Custom ===== */

                    // return $http({
                    //     url: file.deleteUrl,
                    //     method: file.deleteType
                    // }).then(
                    //     function () {
                    //         state = 'resolved';
                    //         $scope.clear(file);
                    //         angular.element('.fileupload-buttonbar').show();
                    //     },
                    //     function () {
                    //         state = 'rejected';
                    //     }
                    // );
                };
            } else if (!file.$cancel && !file._index) {
                file.$cancel = function () {
                    $scope.clear(file);
                };
            }
        }
    ]);
