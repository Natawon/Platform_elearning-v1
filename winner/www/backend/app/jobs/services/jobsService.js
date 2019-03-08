angular.module('newApp').factory('jobsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('jobs') + '?' + queryString);
        },
        get: function(theTranscodings) {
            return $http.get(settingsFactory.get('jobs') + '/' + theTranscodings.id);
        },
        update: function(theTranscodings) {
            return $http(
                {
                    url: settingsFactory.get('jobs') + '/' + theTranscodings.id,
                    method: "PUT",
                    data: theTranscodings,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theTranscodings) {
            return $http(
                {
                    url: settingsFactory.get('jobs'),
                    method: "POST",
                    data: theTranscodings,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theTranscodings) {
            return $http.delete(settingsFactory.get('jobs') + '/' + theTranscodings.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('jobs') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('jobs') + '/search?' + queryString);
        },
        // createJob: function(theJob) {
        //     return $http(
        //         {
        //             url: settingsFactory.get('jobs') + '/createJob',
        //             method: "POST",
        //             data: theJob,
        //             headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        //         }
        //     );
        // },
        // getJobs: function(theVideos) {
        //     // return $http.get(settingsFactory.get('jobs') + "/" + theVideos.video_id + "/video");
        //     return $http(
        //         {
        //             url: settingsFactory.get('jobs') + '/get_jobs',
        //             method: "POST",
        //             data: theVideos,
        //             headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        //         }
        //     );
        // },
    }
}]);
