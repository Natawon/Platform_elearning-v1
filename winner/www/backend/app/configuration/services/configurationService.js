
angular.module('newApp').factory('configurationFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        get: function() {
            return $http.get(settingsFactory.get('configuration'));
        },
        update: function(theConfiguration) {
            return $http(
                {
                    url: settingsFactory.get('configuration')+ '/' + theConfiguration.id,
                    method: "PUT",
                    data: theConfiguration,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




