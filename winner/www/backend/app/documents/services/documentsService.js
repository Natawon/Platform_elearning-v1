
angular.module('newApp').factory('documentsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('documents') + '?' + queryString);
        },
        get: function(theDocuments) {
            return $http.get(settingsFactory.get('documents') + '/' + theDocuments.id);
        },
        update: function(theDocuments) {
            return $http(
                {
                    url: settingsFactory.get('documents') + '/' + theDocuments.id,
                    method: "PUT",
                    data: theDocuments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theDocuments) {
            return $http(
                {
                    url: settingsFactory.get('documents') + '/' + theDocuments.id + '/status',
                    method: "PUT",
                    data: theDocuments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theDocuments) {
            return $http(
                {
                    url: settingsFactory.get('documents'),
                    method: "POST",
                    data: theDocuments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theDocuments) {
            return $http(
                {
                    url: settingsFactory.get('documents') + '/sort',
                    method: "PUT",
                    data: theDocuments,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theDocuments) {
            return $http.delete(settingsFactory.get('documents') + '/' + theDocuments.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('documents') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('documents') + '/search?' + queryString);
        },
        orders: function(theDocuments) {
            return $http(
                {
                    url: settingsFactory.get('documents') + '/orders',
                    method: "POST",
                    data: theDocuments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }

    }
}]);