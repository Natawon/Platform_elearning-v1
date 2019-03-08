
angular.module('newApp').factory('my_profileFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        // query: function(queryString) {
        //     return $http.get(settingsFactory.get('my_profile') + '?' + queryString);
        // },
        get: function() {
            return $http.get(settingsFactory.get('my_profile') + '/self');
        },
        update: function(theMyProfile) {
            return $http(
                {
                    url: settingsFactory.get('my_profile') + '/self',
                    method: "PUT",
                    data: theMyProfile,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        changeAccess: function(theMyProfile) {
            return $http(
                {
                    url: settingsFactory.get('my_profile') + '/self/change-access',
                    method: "POST",
                    data: theMyProfile,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },/*
        create: function(theMyProfile) {
            return $http(
                {
                    url: settingsFactory.get('my_profile'),
                    method: "POST",
                    data: theMyProfile,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theMyProfile) {
            return $http.delete(settingsFactory.get('my_profile') + '/' + theMyProfile.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('my_profile') + '/search?' + queryString);
        }*/
    }
}]);




