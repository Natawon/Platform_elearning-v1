
angular.module('newApp').factory('methodsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('methods') + '?' + queryString);
        },
        get: function(theMethods) {
            return $http.get(settingsFactory.get('methods') + '/' + theMethods.id);
        },
        update: function(theMethods) {
            return $http(
                {
                    url: settingsFactory.get('methods') + '/' + theMethods.id,
                    method: "PUT",
                    data: theMethods,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theMethods) {
            return $http(
                {
                    url: settingsFactory.get('methods') + '/' + theMethods.id + '/status',
                    method: "PUT",
                    data: theMethods,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theMethods) {
            return $http(
                {
                    url: settingsFactory.get('methods'),
                    method: "POST",
                    data: theMethods,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theMethods) {
            return $http(
                {
                    url: settingsFactory.get('methods') + '/sort',
                    method: "PUT",
                    data: theMethods,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theMethods) {
            return $http.delete(settingsFactory.get('methods') + '/' + theMethods.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('methods') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('methods') + '/all');
        },
        orders: function(theMethods) {
            return $http(
                {
                    url: settingsFactory.get('methods') + '/orders',
                    method: "POST",
                    data: theMethods,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);