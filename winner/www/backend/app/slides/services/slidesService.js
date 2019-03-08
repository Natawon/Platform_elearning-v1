
angular.module('newApp').factory('slidesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('slides') + '?' + queryString);
        },
        get: function(theSlides) {
            return $http.get(settingsFactory.get('slides') + '/' + theSlides.id);
        },
        update: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/' + theSlides.id,
                    method: "PUT",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/' + theSlides.id + '/status',
                    method: "PUT",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides'),
                    method: "POST",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/sort',
                    method: "PUT",
                    data: theSlides,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theSlides) {
            return $http.delete(settingsFactory.get('slides') + '/' + theSlides.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('slides') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('slides') + '/search?' + queryString);
        },
        orders: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/orders',
                    method: "POST",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        convertCreate: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/' + 'convert',
                    method: "POST",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getByTopics: function(theSlides, queryString) {
            return $http.get(settingsFactory.get('slides') + '/' + theSlides.id + '/getByTopics?' + queryString);
        },
        updateSlideActive: function(theSlides) {
            return $http(
                {
                    url: settingsFactory.get('slides') + '/' + theSlides.id + '/slide_active',
                    method: "PUT",
                    data: theSlides,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }

    }
}]);