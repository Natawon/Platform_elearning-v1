
angular.module('newApp').factory('topicsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('topics') + '?' + queryString);
        },
        get: function(theTopics) {
            return $http.get(settingsFactory.get('topics') + '/' + theTopics.id);
        },
        update: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics') + '/' + theTopics.id,
                    method: "PUT",
                    data: theTopics,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics') + '/' + theTopics.id + '/status',
                    method: "PUT",
                    data: theTopics,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics'),
                    method: "POST",
                    data: theTopics,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics') + '/sort',
                    method: "PUT",
                    data: theTopics,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theTopics) {
            return $http.delete(settingsFactory.get('topics') + '/' + theTopics.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('topics') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('topics') + '/search?' + queryString);
        },
        topics2parents: function(theTopics) {
            return $http.get(settingsFactory.get('topics') +'/' + theTopics.id  + '/topics2parents');
        },
        topicsHasParents: function(theTopics) {
            return $http.get(settingsFactory.get('topics') +'/' + theTopics.id  + '/topicsHasParents');
        },
        parents: function(theTopics) {
            return $http.get(settingsFactory.get('topics') +'/' + theTopics  + '/parents');
        },
        children: function(theTopics) {
            return $http.get(settingsFactory.get('topics') +'/' + theTopics  + '/children');
        },
        orders: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics') + '/orders',
                    method: "POST",
                    data: theTopics,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getSlides: function(theTopics) {
            return $http.get(settingsFactory.get('topics') +'/' + theTopics.id  + '/getSlides');
        },
        generateLiveUrl: function(theTopics) {
            return $http(
                {
                    url: settingsFactory.get('topics') + '/' + theTopics.id + '/live/url',
                    method: "PUT",
                    data: theTopics,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },

    }
}]);




