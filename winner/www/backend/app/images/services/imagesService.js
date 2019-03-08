
angular.module('newApp').factory('imagesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('images') + '?' + queryString);
        },
        get: function(theImages) {
            return $http.get(settingsFactory.get('images') + '/' + theImages.id);
        },
        update: function(theImages) {
            return $http(
                {
                    url: settingsFactory.get('images') + '/' + theImages.id,
                    method: "PUT",
                    data: theImages,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theImages) {
            return $http(
                {
                    url: settingsFactory.get('images'),
                    method: "POST",
                    data: theImages,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theImages) {
            return $http(
                {
                    url: settingsFactory.get('images') + '/sort',
                    method: "PUT",
                    data: theImages,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theImages) {
            return $http.delete(settingsFactory.get('images') + '/' + theImages.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('images') + '/search?' + queryString);
        },
        all: function(queryString) {
            return $http.get(settingsFactory.get('images') + '/all' + '?' + queryString);
        },
        all_images: function() {
            return $http.get(settingsFactory.get('images') + '/all_images');
        },
        orders: function(theImages) {
            return $http(
                {
                    url: settingsFactory.get('images') + '/orders',
                    method: "POST",
                    data: theImages,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




