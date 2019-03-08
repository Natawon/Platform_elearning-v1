'use strict';

angular.module('newApp')
    .controller('slidesPdfUploadCtrl', ['$scope', '$http', '$q', '$timeout', 'FileUploader', 'functionsFactory', 'settingsFactory', function($scope, $http, $q, $timeout, FileUploader, functionsFactory, settingsFactory) {

        $scope.isProgress = false;
        $scope.isProgressConvert = false;
        $scope.progressFile = 5;

        $scope.totalConvert = 0;
        // $scope.currentPageConvert = 0;

        var files = [];

        /*var startConvert = function(response) {
            $scope.progressFile = 0;
            $scope.totalConvert = response.totalPages;
            $scope.isProgressConvert = true;

            var promise = $q.when();
            var myArr = [];

            for (var i = 1; i <= response.totalPages; i++) {
                myArr.push(i);
            }

            angular.forEach(myArr, function (i) {
                promise = promise.then(function () {
                    return $http.post(settingsFactory.getUpload("upload_slides_pdf"), {
                        file_name: response.file_name,
                        page: i,
                        action: 'convert'
                    }).then(function (resp) {
                        var dataConvert = resp.data;
                        files.push(dataConvert.file);
                        $scope.progressFile = dataConvert.progressFiles;
                        // $scope.currentPageConvert

                        if (parseInt(dataConvert.progressFiles) == 100) {
                            // $scope.isProgress = false;
                            // $scope.isProgressConvert = false;
                            // $scope.slides_data_convert.files = files;
                        }
                    }, function(resp) {
                        console.log(resp);
                    });
                });

                // promise.then(function (resp) {
                //     var dataConvert = resp.data;
                //     files.push(dataConvert.file);
                //     $scope.progressFile = dataConvert.progressFiles;
                //     // $scope.currentPageConvert

                //     console.log(parseInt(dataConvert.progressFiles) == 100);
                //     if (parseInt(dataConvert.progressFiles) == 100) {
                //         console.log('ended');
                //         $scope.isProgress = false;
                //         $scope.isProgressConvert = false;
                //         $scope.slides_data_convert.files = files;
                //     }
                // }, function(resp) {
                //     console.log(resp);
                // });
            });

            promise.then(function() {
                $timeout(function() {
                    $scope.isProgress = false;
                    $scope.isProgressConvert = false;
                    $scope.slides_data_convert.files = files;
                }, 2000, true);
            });
        };*/

        var startConvert = function(response) {
            $scope.progressFile = 0;
            $scope.totalConvert = response.totalPages;
            $scope.isProgressConvert = true;

            var promise = $q.when();
            var myArr = [];

            for (var i = 0; i < response.range.length; i++) {
                myArr.push(i);
            }

            angular.forEach(myArr, function (i) {
                promise = promise.then(function () {
                    return $http.post(settingsFactory.getUpload("upload_slides_pdf"), {
                        file_name: response.file_name,
                        durations: response.range[i],
                        totalPages: response.totalPages,
                        pdf: response.pdf,
                        page: i+1,
                        action: 'convert'
                    }).then(function (resp) {
                        var dataConvert = resp.data;
                        var dataConvertLength = dataConvert.file.length;

                        // console.log(dataConvertLength);

                        for (var i = 0; i < dataConvertLength; i++) {
                            // console.log(dataConvert.file[i]);
                            files.push(dataConvert.file[i]);
                        }

                        // files.push(dataConvert.file);
                        $scope.progressFile = dataConvert.progressFiles;
                        // $scope.currentPageConvert

                        if (parseInt(dataConvert.progressFiles) == 100) {
                            // $scope.isProgress = false;
                            // $scope.isProgressConvert = false;
                            // $scope.slides_data_convert.files = files;
                        }
                    }, function(resp) {
                        console.log(resp);
                    });
                });
            });

            promise.then(function() {
                $timeout(function() {
                    $scope.isProgress = false;
                    $scope.isProgressConvert = false;
                    $scope.slides_data_convert.files = files;
                }, 2000, true);
            });
        };


        var img_uploader = $scope.img_uploader = new FileUploader({
            url: settingsFactory.getUpload("upload_slides_pdf")
        });

        // FILTERS
        img_uploader.filters.push({
            name: 'customFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 2;
            }
        });

        // CALLBACKS
        img_uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
            //console.info('onWhenAddingFileFailed', item, filter, options);
        };
        img_uploader.onAfterAddingFile = function(fileItem) {
            // console.info('onAfterAddingFile', fileItem);
            if ($scope.selected_courses.id !== undefined) {
                fileItem.upload();
            } else {
                functionsFactory.notification("error", "กรุณาเลือกหลักสูตรที่ต้องการอัพโหลดสไลด์");
                angular.element('#upload-convert-pdf').val('');
            }
        };
        img_uploader.onAfterAddingAll = function(addedFileItems) {
            //console.info('onAfterAddingAll', addedFileItems);
        };
        img_uploader.onBeforeUploadItem = function(item) {
            //console.info('onBeforeUploadItem', item);
            $scope.isProgress = true;
        };
        img_uploader.onProgressItem = function(fileItem, progress) {
            //console.info('onProgressItem', fileItem, progress);
        };
        img_uploader.onProgressAll = function(progress) {
            //console.info('onProgressAll', progress);
            $scope.progressFile = progress;
        };
        img_uploader.onSuccessItem = function(fileItem, response, status, headers) {
            // console.info('onSuccessItem', fileItem, response, status, headers);
            // $scope.slides_data.picture = response.file_name;
            $scope.slides_data_convert.pdf = response.file_name;
            console.log(response);
            startConvert(response);
        };
        img_uploader.onErrorItem = function(fileItem, response, status, headers) {
            //console.info('onErrorItem', fileItem, response, status, headers);
            $scope.isProgress = false;

            if (response.message !== undefined) {
                functionsFactory.notification("error", response.message);
            } else if (status === 413) {
                functionsFactory.notification("The file is too large (Maximum allowed file size is 80MB).");
            } else {
                functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
            }

            fileItem.remove();
        };
        img_uploader.onCancelItem = function(fileItem, response, status, headers) {
            //console.info('onCancelItem', fileItem, response, status, headers);
        };
        img_uploader.onCompleteItem = function(fileItem, response, status, headers) {
            //console.info('onCompleteItem', fileItem, response, status, headers);
            //console.log(response.file_name);
            // $scope.slides_data.picture = response.file_name;
            // $scope.isProgress = false;
        };
        img_uploader.onCompleteAll = function() {
            //console.info('onCompleteAll');
        };


    }]);
