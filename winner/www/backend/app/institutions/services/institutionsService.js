
angular.module('newApp').factory('institutionsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('institutions') + '?' + queryString);
        },
        get: function(theInstitutions) {
            return $http.get(settingsFactory.get('institutions') + '/' + theInstitutions.id);
        },
        update: function(theInstitutions) {
            return $http(
                {
                    url: settingsFactory.get('institutions') + '/' + theInstitutions.id,
                    method: "PUT",
                    data: theInstitutions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theInstitutions) {
            return $http(
                {
                    url: settingsFactory.get('institutions'),
                    method: "POST",
                    data: theInstitutions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theInstitutions) {
            return $http(
                {
                    url: settingsFactory.get('institutions') + '/sort',
                    method: "PUT",
                    data: theInstitutions,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theInstitutions) {
            return $http.delete(settingsFactory.get('institutions') + '/' + theInstitutions.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('institutions') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('institutions') + '/all');
        },
        orders: function(theInstitutions) {
            return $http(
                {
                    url: settingsFactory.get('institutions') + '/orders',
                    method: "POST",
                    data: theInstitutions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




