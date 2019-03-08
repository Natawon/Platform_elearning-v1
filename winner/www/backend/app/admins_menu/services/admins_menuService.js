
angular.module('newApp').factory('admins_menuFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('admins_menu') + '?' + queryString);
        },
        get: function(theAdminsMenu) {
            return $http.get(settingsFactory.get('admins_menu') + '/' + theAdminsMenu.id);
        },
        update: function(theAdminsMenu) {
            return $http(
                {
                    url: settingsFactory.get('admins_menu') + '/' + theAdminsMenu.id,
                    method: "PUT",
                    data: theAdminsMenu,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theAdminsMenu) {
            return $http(
                {
                    url: settingsFactory.get('admins_menu'),
                    method: "POST",
                    data: theAdminsMenu,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theAdminsMenu) {
            return $http.delete(settingsFactory.get('admins_menu') + '/' + theAdminsMenu.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('admins_menu') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('admins_menu') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('admins_menu') + '/all');
        }
    }
}]);




