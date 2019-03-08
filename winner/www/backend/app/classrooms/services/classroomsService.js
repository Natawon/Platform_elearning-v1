
angular.module('newApp').factory('classroomsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('classrooms') + '?' + queryString);
        },
        get: function(theClassRooms) {
            return $http.get(settingsFactory.get('classrooms') + '/' + theClassRooms.id);
        },
        update: function(theClassRooms) {
            return $http(
                {
                    url: settingsFactory.get('classrooms') + '/' + theClassRooms.id,
                    method: "PUT",
                    data: theClassRooms,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theClassRooms) {
            return $http(
                {
                    url: settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/status',
                    method: "PUT",
                    data: theClassRooms,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theClassRooms) {
            return $http(
                {
                    url: settingsFactory.get('classrooms'),
                    method: "POST",
                    data: theClassRooms,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theClassRooms) {
            return $http(
                {
                    url: settingsFactory.get('classrooms') + '/sort',
                    method: "PUT",
                    data: theClassRooms,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theClassRooms) {
            return $http.delete(settingsFactory.get('classrooms') + '/' + theClassRooms.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('classrooms') + '/search?' + queryString);
        },
        orders: function(theClassRooms) {
            return $http(
                {
                    url: settingsFactory.get('classrooms') + '/orders',
                    method: "POST",
                    data: theClassRooms,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        all: function() {
            return $http.get(settingsFactory.get('classrooms') + '/all');
        },
        uploadMembers: function(theClassRooms, file, groupId) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('groupId', groupId);
            return $http.post(settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },
        uploadPreApprovedMembers: function(theClassRooms, file, groupId) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('groupId', groupId);
            return $http.post(settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members-pre-approved/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
            // return $http(
            //     {
            //         url: settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members-pre-approved/import',
            //         method: "POST",
            //         data: {file: file, groupId: groupId},
            //         headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            //     }
            // );
        },
        getMembers: function(theClassRooms, queryString) {
            return $http.get(settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members' + '?' + queryString);
        },
        getMembersPreApproved: function(theClassRooms, queryString) {
            return $http.get(settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members-pre-approved' + '?' + queryString);
        },
        detachMembers: function(theClassRooms, dataDetach) {
            return $http(
                {
                    url: settingsFactory.get('classrooms') + '/' + theClassRooms.id + '/members',
                    method: "DELETE",
                    data: dataDetach,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);




