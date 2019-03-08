
angular.module('newApp').factory('slidesTimesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('slides_times') + '?' + queryString);
        },
        get: function(theSlidesTimes) {
            return $http.get(settingsFactory.get('slides_times') + '/' + theSlidesTimes.id);
        },
        update: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times') + '/' + theSlidesTimes.id,
                    method: "PUT",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times'),
                    method: "POST",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times') + '/sort',
                    method: "PUT",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theSlidesTimes) {
            return $http.delete(settingsFactory.get('slides_times') + '/' + theSlidesTimes.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('slides_times') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('slides_times') + '/search?' + queryString);
        },
        orders: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times') + '/orders',
                    method: "POST",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }

    }
}]);