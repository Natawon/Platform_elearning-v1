angular.module('newApp').factory('transcodingsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('transcodings') + '?' + queryString);
        },
        get: function(theTranscodings) {
            return $http.get(settingsFactory.get('transcodings') + '/' + theTranscodings.id);
        },
        update: function(theTranscodings) {
            return $http(
                {
                    url: settingsFactory.get('transcodings') + '/' + theTranscodings.id,
                    method: "PUT",
                    data: theTranscodings,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theTranscodings) {
            return $http(
                {
                    url: settingsFactory.get('transcodings'),
                    method: "POST",
                    data: theTranscodings,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        createByBitrates: function(theTranscodings) {
            return $http(
                {
                    url: settingsFactory.get('transcodings') + '/video/',
                    method: "POST",
                    data: theTranscodings,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theTranscodings) {
            return $http.delete(settingsFactory.get('transcodings') + '/' + theTranscodings.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('transcodings') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('transcodings') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('transcodings') + '/all');
        },
        // checkBitrates: function(queryString) {
        //     return $http.get(settingsFactory.get('check_bitrates') + '?' + queryString);
        // }
    }
}]);




