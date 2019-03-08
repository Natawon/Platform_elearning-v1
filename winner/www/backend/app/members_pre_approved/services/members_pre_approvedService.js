
angular.module('newApp').factory('members_pre_approvedFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('members_pre_approved') + '?' + queryString);
        },
        get: function(theMembersPreApproved) {
            return $http.get(settingsFactory.get('members_pre_approved') + '/' + theMembersPreApproved.id);
        },
        update: function(theMembersPreApproved) {
            return $http(
                {
                    url: settingsFactory.get('members_pre_approved') + '/' + theMembersPreApproved.id,
                    method: "PUT",
                    data: theMembersPreApproved,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theMembersPreApproved) {
            return $http(
                {
                    url: settingsFactory.get('members_pre_approved'),
                    method: "POST",
                    data: theMembersPreApproved,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete: function(theMembersPreApproved) {
            return $http.delete(settingsFactory.get('members_pre_approved') + '/' + theMembersPreApproved.id);
        },
        approve: function(theMembersPreApproved) {
            return $http(
                {
                    url: settingsFactory.get('members_pre_approved') + '/' + theMembersPreApproved.id + '/approve',
                    method: "PUT",
                    data: theMembersPreApproved,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        reject: function(theMembersPreApproved) {
            return $http(
                {
                    url: settingsFactory.get('members_pre_approved') + '/' + theMembersPreApproved.id + '/reject',
                    method: "PUT",
                    data: theMembersPreApproved,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
    }
}]);




