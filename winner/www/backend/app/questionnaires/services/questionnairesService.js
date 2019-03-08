
angular.module('newApp').factory('questionnairesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('questionnaires') + '?' + queryString);
        },
        get: function(theQuestionnaires) {
            return $http.get(settingsFactory.get('questionnaires') + '/' + theQuestionnaires.id);
        },
        update: function(theQuestionnaires) {
            return $http(
                {
                    url: settingsFactory.get('questionnaires') + '/' + theQuestionnaires.id,
                    method: "PUT",
                    data: theQuestionnaires,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theQuestionnaires) {
            return $http(
                {
                    url: settingsFactory.get('questionnaires') + '/' + theQuestionnaires.id + '/status',
                    method: "PUT",
                    data: theQuestionnaires,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theQuestionnaires) {
            return $http(
                {
                    url: settingsFactory.get('questionnaires'),
                    method: "POST",
                    data: theQuestionnaires,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theQuestionnaires) {
            return $http(
                {
                    url: settingsFactory.get('questionnaires') + '/sort',
                    method: "PUT",
                    data: theQuestionnaires,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theQuestionnaires) {
            return $http.delete(settingsFactory.get('questionnaires') + '/' + theQuestionnaires.id);
        },
        delete_questionnaire_choices: function(theAnswer) {
            return $http.delete(settingsFactory.get('questionnaire_choices') + '/' + theAnswer.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('questionnaires') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('questionnaires') + '/search?' + queryString);
        },
        orders: function(theQuestionnaires) {
            return $http(
                {
                    url: settingsFactory.get('questionnaires') + '/orders',
                    method: "POST",
                    data: theQuestionnaires,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }

    }
}]);