
angular.module('newApp').factory('level_groupsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('level_groups') + '?' + queryString);
        },
        get: function(theLevelGroups) {
            return $http.get(settingsFactory.get('level_groups') + '/' + theLevelGroups.id);
        },
        update: function(theLevelGroups) {
            return $http(
                {
                    url: settingsFactory.get('level_groups') + '/' + theLevelGroups.id,
                    method: "PUT",
                    data: theLevelGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theLevelGroups) {
            return $http(
                {
                    url: settingsFactory.get('level_groups'),
                    method: "POST",
                    data: theLevelGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theLevelGroups) {
            return $http(
                {
                    url: settingsFactory.get('level_groups') + '/sort',
                    method: "PUT",
                    data: theLevelGroups,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theLevelGroups) {
            return $http.delete(settingsFactory.get('level_groups') + '/' + theLevelGroups.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/search?' + queryString);
        },
        orders: function(theLevelGroups) {
            return $http(
                {
                    url: settingsFactory.get('level_groups') + '/orders',
                    method: "POST",
                    data: theLevelGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sub_groups: function(queryString) {
        return $http.get(settingsFactory.get('level_groups') + '/sub_groups?' + queryString);
        },
        all: function() {
            return $http.get(settingsFactory.get('level_groups') + '/all');
        },
        allBySubGroups: function(theSubGroups) {
            return $http.get(settingsFactory.get('level_groups') + '/all/sub_groups?' + theSubGroups);
            // return $http(
            //     {
            //         url: settingsFactory.get('level_groups') + '/all/sub_groups',
            //         method: "POST",
            //         data: theSubGroups,
            //         headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            //     }
            // );
        },
        access_groups: function(queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/access_groups' + '?' + queryString);
        },
        waiting_groups: function(queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/waiting_groups' + '?' + queryString);
        },
        uploadMembers: function(theLevelGroups, file) {
            var fd = new FormData();
            fd.append('file', file);
            return $http.post(settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },
        uploadPreApprovedMembers: function(theLevelGroups, file) {
            var fd = new FormData();
            fd.append('file', file);
            return $http.post(settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members-pre-approved/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },
        checkPreApprovedMembers: function(theLevelGroups) {
            return $http(
                {
                    url: settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/pre-approved',
                    method: "PUT",
                    data: theLevelGroups,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getMembers: function(theLevelGroups, queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members' + '?' + queryString);
        },
        getMembersPreApproved: function(theLevelGroups, queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members-pre-approved' + '?' + queryString);
        },
        getMembersNotApproved: function(theLevelGroups, queryString) {
            return $http.get(settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members-not-approved' + '?' + queryString);
        },
        detachMembers: function(theLevelGroups, dataDetach) {
            return $http(
                {
                    url: settingsFactory.get('level_groups') + '/' + theLevelGroups.id + '/members',
                    method: "DELETE",
                    data: dataDetach,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




