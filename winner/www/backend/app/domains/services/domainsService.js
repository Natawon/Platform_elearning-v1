
angular.module('newApp').factory('domainsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('domains') + '?' + queryString);
        },
        get: function(theDomains) {
            return $http.get(settingsFactory.get('domains') + '/' + theDomains.id);
        },
        update: function(theDomains) {
            return $http(
                {
                    url: settingsFactory.get('domains') + '/' + theDomains.id,
                    method: "PUT",
                    data: theDomains,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theDomains) {
            return $http(
                {
                    url: settingsFactory.get('domains'),
                    method: "POST",
                    data: theDomains,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theDomains) {
            return $http(
                {
                    url: settingsFactory.get('domains') + '/sort',
                    method: "PUT",
                    data: theDomains,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theDomains) {
            return $http.delete(settingsFactory.get('domains') + '/' + theDomains.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('domains') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('domains') + '/all');
        },
        parent: function() {
            return $http.get(settingsFactory.get('domains') + '/parent');
        },
        orders: function(theDomains) {
            return $http(
                {
                    url: settingsFactory.get('domains') + '/orders',
                    method: "POST",
                    data: theDomains,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




