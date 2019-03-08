angular.module('newApp').factory('scVideosFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        listVodJobs: function(queryString) {
            return $http.get(settingsFactory.get('sc_videos') + '/list-vod-jobs.php?' + queryString);
        },
        getVodJob: function(queryString) {
            return $http.get(settingsFactory.get('sc_videos') + '/get-vod-job.php?' + queryString);
        },
        getVodJobs: function(queryString) {
            return $http.get(settingsFactory.get('sc_videos') + '/get-vod-job.php?' + queryString);
        },
        createVodJob: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('sc_videos') + '/create-vod-job.php',
                    method: "POST",
                    data: theVideos,
                    // headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        copyFile: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('sc_videos') + '/copy-file.php',
                    method: "POST",
                    data: theVideos,
                    // headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        checkCopyFile: function(queryString) {
            return $http.get(settingsFactory.get('sc_videos') + '/check-copy-file.php?' + queryString);
        },
        deleteTmpFile: function(theTmpFile) {
            return $http(
                {
                    url: settingsFactory.get('sc_videos') + '/delete-tmp-file.php',
                    method: "DELETE",
                    data: theTmpFile,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
    }
}]);
