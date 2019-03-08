
angular.module('newApp').factory('questionsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('questions') + '?' + queryString);
        },
        get: function(theQuestions) {
            return $http.get(settingsFactory.get('questions') + '/' + theQuestions.id);
        },
        update: function(theQuestions) {
            return $http(
                {
                    url: settingsFactory.get('questions') + '/' + theQuestions.id,
                    method: "PUT",
                    data: theQuestions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theQuestions) {
            return $http(
                {
                    url: settingsFactory.get('questions') + '/' + theQuestions.id + '/status',
                    method: "PUT",
                    data: theQuestions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theQuestions) {
            return $http(
                {
                    url: settingsFactory.get('questions'),
                    method: "POST",
                    data: theQuestions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theQuestions) {
            return $http(
                {
                    url: settingsFactory.get('questions') + '/sort',
                    method: "PUT",
                    data: theQuestions,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theQuestions) {
            return $http.delete(settingsFactory.get('questions') + '/' + theQuestions.id);
        },
        delete_answer: function(theAnswer) {
            return $http.delete(settingsFactory.get('answer') + '/' + theAnswer.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('questions') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('questions') + '/search?' + queryString);
        },
        orders: function(theQuestions) {
            return $http(
                {
                    url: settingsFactory.get('questions') + '/orders',
                    method: "POST",
                    data: theQuestions,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }

    }
}]);