
angular.module('newApp').factory('super_usersFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('super_users') + '?' + queryString);
        },
        get: function(theSuperUsers) {
            return $http.get(settingsFactory.get('super_users') + '/' + theSuperUsers.id);
        },
        update: function(theSuperUsers) {
            return $http(
                {
                    url: settingsFactory.get('super_users') + '/' + theSuperUsers.id,
                    method: "PUT",
                    data: theSuperUsers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theSuperUsers) {
            return $http(
                {
                    url: settingsFactory.get('super_users') + '/' + theSuperUsers.id + '/status',
                    method: "PUT",
                    data: theSuperUsers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theSuperUsers) {
            return $http(
                {
                    url: settingsFactory.get('super_users'),
                    method: "POST",
                    data: theSuperUsers,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theSuperUsers) {
            return $http.delete(settingsFactory.get('super_users') + '/' + theSuperUsers.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('super_users') + '/search?' + queryString);
        }
    }
}]);




