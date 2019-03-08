
angular.module('newApp').factory('usage_statisticFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('usage_statistic') + '?' + queryString);
        },
        get: function(theQuiz) {
            return $http.get(settingsFactory.get('usage_statistic') + '/' + theQuiz.id);
        }
    }

}]);