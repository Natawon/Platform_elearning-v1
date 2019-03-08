
angular.module('newApp').factory('quizFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('quiz') + '?' + queryString);
        },
        get: function(theQuiz) {
            return $http.get(settingsFactory.get('quiz') + '/' + theQuiz.id);
        },
        update: function(theQuiz) {
            return $http(
                {
                    url: settingsFactory.get('quiz') + '/' + theQuiz.id,
                    method: "PUT",
                    data: theQuiz,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theQuiz) {
            return $http(
                {
                    url: settingsFactory.get('quiz') + '/' + theQuiz.id + '/status',
                    method: "PUT",
                    data: theQuiz,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theQuiz) {
            return $http(
                {
                    url: settingsFactory.get('quiz'),
                    method: "POST",
                    data: theQuiz,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theQuiz) {
            return $http(
                {
                    url: settingsFactory.get('quiz') + '/sort',
                    method: "PUT",
                    data: theQuiz,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theQuiz) {
            return $http.delete(settingsFactory.get('quiz') + '/' + theQuiz.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('quiz') + '/all');
        },
        quiz2topic: function(theCourses) {
            return $http.get(settingsFactory.get('quiz') + '/' + theCourses.id +'/courses');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('quiz') + '/search?' + queryString);
        },
        orders: function(theQuiz) {
            return $http(
                {
                    url: settingsFactory.get('quiz') + '/orders',
                    method: "POST",
                    data: theQuiz,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        questions: function(theQuiz, queryString) {
            return $http.get(settingsFactory.get('quiz') +'/' + theQuiz.id  + '/questions?' + queryString);
        }
    }
}]);