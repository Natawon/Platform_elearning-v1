
angular.module('newApp').factory('qaFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('qa') + '?' + queryString);
        },
        get: function(theQA) {
            return $http.get(settingsFactory.get('qa') + '/' + theQA.id);
        },
        update: function(theQA) {
            return $http(
                {
                    url: settingsFactory.get('qa') + '/' + theQA.id,
                    method: "PUT",
                    data: theQA,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theQA) {
            return $http(
                {
                    url: settingsFactory.get('qa') + '/' + theQA.id + '/status',
                    method: "PUT",
                    data: theQA,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theQA) {
            return $http(
                {
                    url: settingsFactory.get('qa'),
                    method: "POST",
                    data: theQA,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theQA) {
            return $http(
                {
                    url: settingsFactory.get('qa') + '/sort',
                    method: "PUT",
                    data: theQA,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theQA) {
            return $http.delete(settingsFactory.get('qa') + '/' + theQA.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('qa') + '/search?' + queryString);
        },
        orders: function(theQA) {
            return $http(
                {
                    url: settingsFactory.get('qa') + '/orders',
                    method: "POST",
                    data: theQA,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




