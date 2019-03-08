
angular.module('newApp').factory('groupsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('groups') + '?' + queryString);
        },
        get: function(theGroups) {
            return $http.get(settingsFactory.get('groups') + '/' + theGroups.id);
        },
        update: function(theGroups) {
            return $http(
                {
                    url: settingsFactory.get('groups') + '/' + theGroups.id,
                    method: "PUT",
                    data: theGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theGroups) {
            return $http(
                {
                    url: settingsFactory.get('groups') + '/' + theGroups.id + '/status',
                    method: "PUT",
                    data: theGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theGroups) {
            return $http(
                {
                    url: settingsFactory.get('groups'),
                    method: "POST",
                    data: theGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theGroups) {
            return $http(
                {
                    url: settingsFactory.get('groups') + '/sort',
                    method: "PUT",
                    data: theGroups,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theGroups) {
            return $http.delete(settingsFactory.get('groups') + '/' + theGroups.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('groups') + '/search?' + queryString);
        },
        categories: function(theGroups) {
            return $http.get(settingsFactory.get('groups') + '/' + theGroups.id + '/categories');
        },
        courses: function(theGroups,queryString) {
            return $http.get(settingsFactory.get('groups') + '/' + theGroups.id + '/courses?' + queryString);
        },
        sub_groups: function(theGroups) {
            return $http.get(settingsFactory.get('groups') + '/' + theGroups.id + '/sub_groups');
        },
        all: function() {
            return $http.get(settingsFactory.get('groups') + '/all');
        },
        all_groups: function() {
            return $http.get(settingsFactory.get('groups') + '/all_groups');
        },
        orders: function(theGroups) {
            return $http(
                {
                    url: settingsFactory.get('groups') + '/orders',
                    method: "POST",
                    data: theGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        questionnaire_packs: function(theGroups, queryString) {
            return $http.get(settingsFactory.get('groups') +'/' + theGroups.id  + '/questionnaire_packs?' + queryString);
        },
    }
}]);




