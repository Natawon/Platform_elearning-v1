
angular.module('newApp').factory('discussionsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('discussions') + '?' + queryString);
        },
        get: function(theDiscussions) {
            return $http.get(settingsFactory.get('discussions') + '/' + theDiscussions.id);
        },
        update: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id,
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id + '/status',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions'),
                    method: "POST",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/sort',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theDiscussions) {
            return $http.delete(settingsFactory.get('discussions') + '/' + theDiscussions.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('discussions') + '/all');
        },
        allExcept: function(theDiscussions) {
            return $http.get(settingsFactory.get('discussions') + '/all/' + theDiscussions.courses_id  + '/except/' + theDiscussions.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('discussions') + '/search?' + queryString);
        },
        orders: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/orders',
                    method: "POST",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        send: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/send',
                    method: "POST",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateIsPublic: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id + '/is_public',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateIsSentInstructor: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id + '/is_sent_instructor',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateIsReject: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id + '/is_reject',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        read: function(theDiscussions) {
            return $http(
                {
                    url: settingsFactory.get('discussions') + '/' + theDiscussions.id + '/read',
                    method: "PUT",
                    data: theDiscussions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },

    }
}]);