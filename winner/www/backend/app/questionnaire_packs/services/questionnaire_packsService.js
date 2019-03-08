
angular.module('newApp').factory('questionnaire_packsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('questionnaire_packs') + '?' + queryString);
        },
        get: function(theQuestionnairePacks) {
            return $http.get(settingsFactory.get('questionnaire_packs') + '/' + theQuestionnairePacks.id);
        },
        update: function(theQuestionnairePacks) {
            return $http(
                {
                    url: settingsFactory.get('questionnaire_packs') + '/' + theQuestionnairePacks.id,
                    method: "PUT",
                    data: theQuestionnairePacks,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theQuestionnairePacks) {
            return $http(
                {
                    url: settingsFactory.get('questionnaire_packs') + '/' + theQuestionnairePacks.id + '/status',
                    method: "PUT",
                    data: theQuestionnairePacks,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theQuestionnairePacks) {
            return $http(
                {
                    url: settingsFactory.get('questionnaire_packs'),
                    method: "POST",
                    data: theQuestionnairePacks,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theQuestionnairePacks) {
            return $http(
                {
                    url: settingsFactory.get('questionnaire_packs') + '/sort',
                    method: "PUT",
                    data: theQuestionnairePacks,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theQuestionnairePacks) {
            return $http.delete(settingsFactory.get('questionnaire_packs') + '/' + theQuestionnairePacks.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('questionnaire_packs') + '/all');
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('questionnaire_packs') + '/search?' + queryString);
        },
        orders: function(theQuestionnairePacks) {
            return $http(
                {
                    url: settingsFactory.get('questionnaire_packs') + '/orders',
                    method: "POST",
                    data: theQuestionnairePacks,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        questionnaires: function(theQuestionnairePacks, queryString) {
            return $http.get(settingsFactory.get('questionnaire_packs') +'/' + theQuestionnairePacks.id  + '/questionnaires?' + queryString);
        }
    }
}]);