
angular.module('newApp').factory('highlightsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('highlights') + '?' + queryString);
        },
        get: function(theHighlights) {
            return $http.get(settingsFactory.get('highlights') + '/' + theHighlights.id);
        },
        update: function(theHighlights) {
            return $http(
                {
                    url: settingsFactory.get('highlights') + '/' + theHighlights.id,
                    method: "PUT",
                    data: theHighlights,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theHighlights) {
            return $http(
                {
                    url: settingsFactory.get('highlights') + '/' + theHighlights.id + '/status',
                    method: "PUT",
                    data: theHighlights,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theHighlights) {
            return $http(
                {
                    url: settingsFactory.get('highlights'),
                    method: "POST",
                    data: theHighlights,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theHighlights) {
            return $http(
                {
                    url: settingsFactory.get('highlights') + '/sort',
                    method: "PUT",
                    data: theHighlights,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theHighlights) {
            return $http.delete(settingsFactory.get('highlights') + '/' + theHighlights.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('highlights') + '/search?' + queryString);
        },
        orders: function(theHighlights) {
            return $http(
                {
                    url: settingsFactory.get('highlights') + '/orders',
                    method: "POST",
                    data: theHighlights,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




