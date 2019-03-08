
angular.module('newApp').factory('adminsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('admins') + '?' + queryString);
        },
        get: function(theAdmins) {
            return $http.get(settingsFactory.get('admins') + '/' + theAdmins.id);
        },
        update: function(theAdmins) {
            return $http(
                {
                    url: settingsFactory.get('admins') + '/' + theAdmins.id,
                    method: "PUT",
                    data: theAdmins,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theAdmins) {
            return $http(
                {
                    url: settingsFactory.get('admins') + '/' + theAdmins.id + '/status',
                    method: "PUT",
                    data: theAdmins,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theAdmins) {
            return $http(
                {
                    url: settingsFactory.get('admins'),
                    method: "POST",
                    data: theAdmins,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theAdmins) {
            return $http.delete(settingsFactory.get('admins') + '/' + theAdmins.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('admins') + '/search?' + queryString);
        },
        targets: function() {
            return $http.get(settingsFactory.get('admins') + '/targets');
        }
    }
}]);




