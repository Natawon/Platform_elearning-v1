angular.module('newApp').factory('ffmpegFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        checkBitrates: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/check-bitrates.php?' + queryString);
        },
        convertTranscode: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/transcode.php?' + queryString);
        },
        getTranscodeProcess: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/get-progress.php?' + queryString);
        },
        getVideoInfo: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/get-info.php' + queryString);
        },
        generateSmil: function(theFFMpegParams) {
            return $http(
                {
                    url: settingsFactory.get('ffmpeg') + '/create-smil.php',
                    method: "POST",
                    data: theFFMpegParams,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        generateSmilLive: function(theFFMpegParams) {
            return $http(
                {
                    url: settingsFactory.get('ffmpeg') + '/create-smil-live.php',
                    method: "POST",
                    data: theFFMpegParams,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




