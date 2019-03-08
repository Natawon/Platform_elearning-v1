angular.module('newApp').factory('live_resultsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('live_results') + '?' + queryString);
        },
        get: function(theVideos) {
            return $http.get(settingsFactory.get('live_results') + '/' + theVideos.id);
        },
        update: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('live_results') + '/' + theVideos.id,
                    method: "PUT",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('live_results'),
                    method: "POST",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theVideos) {
            return $http.delete(settingsFactory.get('live_results') + '/' + theVideos.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('live_results') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('live_results') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('live_results') + '/all');
        },
        checkBitrates: function(queryString) {
            return $http.get(settingsFactory.get('ffmpeg') + '/check-bitrates.php?' + queryString);
        },
        createVideo: function(theVideos) {
            return $http(
                {
                    url: settingsFactory.get('live_results') + '/createVideo',
                    method: "POST",
                    data: theVideos,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getVideo: function(dir_name, file) {
            return $http.get(settingsFactory.getUpload("get_video") + '?dir_name=' + dir_name + '&file=' + file);
        },
    }
}]);




