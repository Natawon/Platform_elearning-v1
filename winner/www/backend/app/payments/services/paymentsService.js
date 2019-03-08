
angular.module('newApp').factory('paymentsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('payments') + '?' + queryString);
        },
        get: function(thePayments) {
            return $http.get(settingsFactory.get('payments') + '/' + thePayments.id);
        },
        update: function(thePayments) {
            return $http(
                {
                    url: settingsFactory.get('payments') + '/' + thePayments.id,
                    method: "PUT",
                    data: thePayments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateApproveStatus: function(thePayments) {
            return $http(
                {
                    url: settingsFactory.get('payments') + '/' + thePayments.id + '/approve-status',
                    method: "PUT",
                    data: thePayments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateIsCanceled: function(thePayments) {
            return $http(
                {
                    url: settingsFactory.get('payments') + '/' + thePayments.id + '/is_canceled',
                    method: "PUT",
                    data: thePayments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(thePayments) {
            return $http(
                {
                    url: settingsFactory.get('payments'),
                    method: "POST",
                    data: thePayments,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(thePayments) {
            return $http.delete(settingsFactory.get('payments') + '/' + thePayments.id);
        }
    }
}]);




