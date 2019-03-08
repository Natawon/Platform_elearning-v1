angular.module('newApp').factory('videosFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('videos') + '?' + queryString);
        },
        get: function(theVideos) {
            return $http.get(settingsFactory.get('videos') + '/' + theVideos.id);
        },
        update: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('videos') + '/' + theVideos.id,
                    method: "PUT",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('videos'),
                    method: "POST",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theVideos) {
            return $http.delete(settingsFactory.get('videos') + '/' + theVideos.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('videos') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('videos') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('videos') + '/all');
        },
        checkBitrates: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/check-bitrates.php?' + queryString);
        },
        createVideo: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('videos') + '/createVideo',
                    method: "POST",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getVideo: function(dir_name, file) {
            return $http.get(settingsFactory.getUpload("get_video") + '?dir_name=' + dir_name + '&file=' + file);
        },
        subtitles: function(theVideos, queryString) {
            return $http.get(settingsFactory.get('videos') +'/' + theVideos.id  + '/subtitles?' + queryString);
        },
    }
}]);




