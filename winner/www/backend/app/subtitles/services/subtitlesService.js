
angular.module('newApp').factory('subtitlesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('subtitles') + '?' + queryString);
        },
        get: function(theSubtitles) {
            return $http.get(settingsFactory.get('subtitles') + '/' + theSubtitles.id);
        },
        getByVideo: function(theVideos) {
            return $http.get(settingsFactory.get('subtitles') + '/videos/' + theVideos.id);
        },
        update: function(theSubtitles) {
            return $http(
                {
                    url: settingsFactory.get('subtitles') + '/' + theSubtitles.id,
                    method: "PUT",
                    data: theSubtitles,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theSubtitles) {
            return $http(
                {
                    url: settingsFactory.get('subtitles'),
                    method: "POST",
                    data: theSubtitles,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        createByVideo: function(theSubtitles) {
            return $http(
                {
                    url: settingsFactory.get('subtitles') + '/videos/' + theSubtitles.id ,
                    method: "POST",
                    data: theSubtitles,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theSubtitles) {
            return $http(
                {
                    url: settingsFactory.get('subtitles') + '/sort',
                    method: "PUT",
                    data: theSubtitles,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theSubtitles) {
            return $http.delete(settingsFactory.get('subtitles') + '/' + theSubtitles.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('subtitles') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('subtitles') + '/search?' + queryString);
        },
        orders: function(theSubtitles) {
            return $http(
                {
                    url: settingsFactory.get('subtitles') + '/orders',
                    method: "POST",
                    data: theSubtitles,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        uploadFile: function(theVideos, file) {
            var fd = new FormData();
            fd.append('file', file);
            return $http.post(settingsFactory.get('subtitles') + '/videos/' + theVideos.id + '/upload', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },

    }
}]);