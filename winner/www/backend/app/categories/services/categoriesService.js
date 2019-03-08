
angular.module('newApp').factory('categoriesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('categories') + '?' + queryString);
        },
        get: function(theCategories) {
            return $http.get(settingsFactory.get('categories') + '/' + theCategories.id);
        },
        update: function(theCategories) {
            return $http(
                {
                    url: settingsFactory.get('categories') + '/' + theCategories.id,
                    method: "PUT",
                    data: theCategories,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theCategories) {
            return $http(
                {
                    url: settingsFactory.get('categories') + '/' + theCategories.id + '/status',
                    method: "PUT",
                    data: theCategories,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theCategories) {
            return $http(
                {
                    url: settingsFactory.get('categories'),
                    method: "POST",
                    data: theCategories,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theCategories) {
            return $http(
                {
                    url: settingsFactory.get('categories') + '/sort',
                    method: "PUT",
                    data: theCategories,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theCategories) {
            return $http.delete(settingsFactory.get('categories') + '/' + theCategories.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('categories') + '/search?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('categories') + '/all');
        },
        all_categories: function() {
            return $http.get(settingsFactory.get('categories') + '/all_categories');
        },
        orders: function(theCategories) {
            return $http(
                {
                    url: settingsFactory.get('categories') + '/orders',
                    method: "POST",
                    data: theCategories,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




