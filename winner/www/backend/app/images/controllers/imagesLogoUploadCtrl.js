'use strict';

angular.module('newApp')
    .controller('imagesLogoUploadCtrl', ['$scope', 'FileUploader', 'functionsFactory', 'settingsFactory', function($scope, FileUploader, functionsFactory, settingsFactory) {

        var img_uploader = $scope.img_uploader = new FileUploader({
            url: settingsFactory.getUpload("upload_images_logo")
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
            //console.info('onAfterAddingFile', fileItem);
            var $eleInput = fileItem.uploader._directives.select[0].element;
            if ($scope.certificates_data.groups_id === undefined || $scope.certificates_data.groups_id === null) {
                $eleInput.val('');
                functionsFactory.notification('error', 'กรุณาเลือกกลุ่ม (Group) ก่อนการอัพโหลด');
                fileItem.remove();
            }
        };
        img_uploader.onAfterAddingAll = function(addedFileItems) {
            //console.info('onAfterAddingAll', addedFileItems);
        };
        img_uploader.onBeforeUploadItem = function(item) {
            //console.info('onBeforeUploadItem', item);
        };
        img_uploader.onProgressItem = function(fileItem, progress) {
            //console.info('onProgressItem', fileItem, progress);
        };
        img_uploader.onProgressAll = function(progress) {
            //console.info('onProgressAll', progress);
        };
        img_uploader.onSuccessItem = function(fileItem, response, status, headers) {
            //console.info('onSuccessItem', fileItem, response, status, headers);
            var fileNameSplit = [];
            var imagesField = fileItem.uploader._directives.select[0].element.data('images-field');

            $scope.images_data.type = 1;

            fileNameSplit = functionsFactory.explode('-', response.file_name, 2);
            $scope.images_data.title = fileNameSplit.length == 2 ? fileNameSplit[1] : response.file_name;

            $scope.images_data.picture = response.file_name;
            $scope.certificates_data[imagesField] = response.file_name;
        };
        img_uploader.onErrorItem = function(fileItem, response, status, headers) {
            //console.info('onErrorItem', fileItem, response, status, headers);
            alert(response.message);
            fileItem.remove();
        };
        img_uploader.onCancelItem = function(fileItem, response, status, headers) {
            //console.info('onCancelItem', fileItem, response, status, headers);
        };
        img_uploader.onCompleteItem = function(fileItem, response, status, headers) {
            //console.info('onCompleteItem', fileItem, response, status, headers);
            //console.log(response.file_name);
            // $scope.courses_data.thumbnail = response.file_name;

        };
        img_uploader.onCompleteAll = function() {
            //console.info('onCompleteAll');
        };

    }]);
