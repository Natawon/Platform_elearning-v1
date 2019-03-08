
angular.module('newApp').factory('membersFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('members') + '?' + queryString);
        },
        get: function(theMembers) {
            return $http.get(settingsFactory.get('members') + '/' + theMembers.id);
        },
        update: function(theMembers) {
            return $http(
                {
                    url: settingsFactory.get('members') + '/' + theMembers.id,
                    method: "PUT",
                    data: theMembers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theMembers) {
            return $http(
                {
                    url: settingsFactory.get('members') + '/' + theMembers.id + '/status',
                    method: "PUT",
                    data: theMembers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theMembers) {
            return $http(
                {
                    url: settingsFactory.get('members'),
                    method: "POST",
                    data: theMembers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theMembers) {
            return $http.delete(settingsFactory.get('members') + '/' + theMembers.id);
        },
        approve: function(theMembers) {
            return $http(
                {
                    url: settingsFactory.get('members') + '/' + theMembers.id + '/approve',
                    method: "PUT",
                    data: theMembers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        reject: function(theMembers) {
            return $http(
                {
                    url: settingsFactory.get('members') + '/' + theMembers.id + '/reject',
                    method: "PUT",
                    data: theMembers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        all: function() {
            return $http.get(settingsFactory.get('members') + '/all');
        },
    }
}]);




