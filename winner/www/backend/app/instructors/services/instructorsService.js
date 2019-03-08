
angular.module('newApp').factory('instructorsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('instructors') + '?' + queryString);
        },
        get: function(theInstructors) {
            return $http.get(settingsFactory.get('instructors') + '/' + theInstructors.id);
        },
        update: function(theInstructors) {
            return $http(
                {
                    url: settingsFactory.get('instructors') + '/' + theInstructors.id,
                    method: "PUT",
                    data: theInstructors,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theInstructors) {
            return $http(
                {
                    url: settingsFactory.get('instructors') + '/' + theInstructors.id + '/status',
                    method: "PUT",
                    data: theInstructors,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theInstructors) {
            return $http(
                {
                    url: settingsFactory.get('instructors'),
                    method: "POST",
                    data: theInstructors,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theInstructors) {
            return $http(
                {
                    url: settingsFactory.get('instructors') + '/sort',
                    method: "PUT",
                    data: theInstructors,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theInstructors) {
            return $http.delete(settingsFactory.get('instructors') + '/' + theInstructors.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('instructors') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('instructors') + '/all');
        },
        parent: function() {
            return $http.get(settingsFactory.get('instructors') + '/parent');
        },
        orders: function(theInstructors) {
            return $http(
                {
                    url: settingsFactory.get('instructors') + '/orders',
                    method: "POST",
                    data: theInstructors,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




