
angular.module('newApp').factory('ordersFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('orders') + '?' + queryString);
        },
        get: function(theOrders) {
            return $http.get(settingsFactory.get('orders') + '/' + theOrders.id);
        },
        update: function(theOrders) {
            return $http(
                {
                    url: settingsFactory.get('orders') + '/' + theOrders.id,
                    method: "PUT",
                    data: theOrders,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theOrders) {
            return $http(
                {
                    url: settingsFactory.get('orders'),
                    method: "POST",
                    data: theOrders,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theOrders) {
            return $http.delete(settingsFactory.get('orders') + '/' + theOrders.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('orders') + '/all');
        },
    }
}]);




