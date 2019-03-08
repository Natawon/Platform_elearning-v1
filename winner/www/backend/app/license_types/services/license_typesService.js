
angular.module('newApp').factory('license_typesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('license_types') + '?' + queryString);
        },
        get: function(theLicenseTypes) {
            return $http.get(settingsFactory.get('license_types') + '/' + theLicenseTypes.id);
        },
        update: function(theLicenseTypes) {
            return $http(
                {
                    url: settingsFactory.get('license_types') + '/' + theLicenseTypes.id,
                    method: "PUT",
                    data: theLicenseTypes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theLicenseTypes) {
            return $http(
                {
                    url: settingsFactory.get('license_types'),
                    method: "POST",
                    data: theLicenseTypes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theLicenseTypes) {
            return $http(
                {
                    url: settingsFactory.get('license_types') + '/sort',
                    method: "PUT",
                    data: theLicenseTypes,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theLicenseTypes) {
            return $http.delete(settingsFactory.get('license_types') + '/' + theLicenseTypes.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('license_types') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('license_types') + '/all');
        },
        orders: function(theLicenseTypes) {
            return $http(
                {
                    url: settingsFactory.get('license_types') + '/orders',
                    method: "POST",
                    data: theLicenseTypes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




