
angular.module('newApp').factory('sub_groupsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('sub_groups') + '?' + queryString);
        },
        get: function(theSubGroups) {
            return $http.get(settingsFactory.get('sub_groups') + '/' + theSubGroups.id);
        },
        update: function(theSubGroups) {
            return $http(
                {
                    url: settingsFactory.get('sub_groups') + '/' + theSubGroups.id,
                    method: "PUT",
                    data: theSubGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theSubGroups) {
            return $http(
                {
                    url: settingsFactory.get('sub_groups') + '/' + theSubGroups.id + '/status',
                    method: "PUT",
                    data: theSubGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theSubGroups) {
            return $http(
                {
                    url: settingsFactory.get('sub_groups'),
                    method: "POST",
                    data: theSubGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theSubGroups) {
            return $http(
                {
                    url: settingsFactory.get('sub_groups') + '/sort',
                    method: "PUT",
                    data: theSubGroups,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theSubGroups) {
            return $http.delete(settingsFactory.get('sub_groups') + '/' + theSubGroups.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('sub_groups') + '/search?' + queryString);
        },
        level_groups: function(theSubGroups) {
            return $http.get(settingsFactory.get('sub_groups') + '/' + theSubGroups.id + '/level_groups');
        },
        all: function() {
            return $http.get(settingsFactory.get('sub_groups') + '/all');
        },
        orders: function(theSubGroups) {
            return $http(
                {
                    url: settingsFactory.get('sub_groups') + '/orders',
                    method: "POST",
                    data: theSubGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




