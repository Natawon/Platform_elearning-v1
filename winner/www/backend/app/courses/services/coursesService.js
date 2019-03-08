
angular.module('newApp').factory('coursesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('courses') + '?' + queryString);
        },
        get: function(theCourses) {
            return $http.get(settingsFactory.get('courses') + '/' + theCourses.id);
        },
        update: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/' + theCourses.id,
                    method: "PUT",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/' + theCourses.id + '/status',
                    method: "PUT",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses'),
                    method: "POST",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/sort',
                    method: "PUT",
                    data: theCourses,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theCourses) {
            return $http.delete(settingsFactory.get('courses') + '/' + theCourses.id);
        },
        all: function() {
            return $http.get(settingsFactory.get('courses') + '/all');
        },
        level_public: function() {
            return $http.get(settingsFactory.get('courses') + '/level_public');
        },
        overview: function(theCourses) {
            return $http.get(settingsFactory.get('courses') + '/' + theCourses.id + '/overview');
        },
        allExcept: function(theCourses) {
            return $http.get(settingsFactory.get('courses') + '/all/except/' + theCourses.id);
        },
        allInGroups: function(theCourses) {
            return $http.get(settingsFactory.get('courses') + '/all/in-groups/' + ((theCourses !== undefined) ? theCourses.id : ''));
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('courses') + '/search?' + queryString);
        },
        topics: function(theCourses,queryString) {
            return $http.get(settingsFactory.get('courses') + '/' + theCourses.id + '/topics?' + queryString);
        },
        updateSyncSlide: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/' + theCourses.id + '/sync-slide',
                    method: "PUT",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        slides: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/slides?' + queryString);
        },
        slidesForSync: function(theCourses) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/slidesForSync');
        },
        previousSlides: function(theCourses, theSlides) {
            return $http.get(settingsFactory.get('courses') + '/'+ theCourses.id +'/slides/'+ theSlides +'/previous');
        },
        nextSlide: function(theCourses, theSlides) {
            return $http.get(settingsFactory.get('courses') + '/'+ theCourses.id +'/slides/'+ theSlides +'/next');
        },
        slidesActive: function(theCourses) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/slidesActive');
        },
        firstSlide: function(theCourses) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/firstSlide');
        },
        create_slides_times: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times'),
                    method: "POST",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        update_slides_times: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times') + '/' + theSlidesTimes.id,
                    method: "PUT",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        delete_slides_times: function(theSlidesTimes) {
            return $http.delete(settingsFactory.get('slides_times') + '/' + theSlidesTimes.id);
        },
        documents: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/documents?' + queryString);
        },
        discussions: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/discussions?' + queryString);
        },
        quiz: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/quiz?' + queryString);
        },
        usage_statistic: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') +'/' + theCourses.id  + '/usage_statistic?' + queryString);
        },
        orders: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/orders',
                    method: "POST",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        getUploadVideos: function(queryString) {
            return $http.get(settingsFactory.getUpload("chunk_courses_video") + '?' + queryString);
        },
        getVideo: function(dir_name, file) {
            return $http.get(settingsFactory.getUpload("chunk_courses_video") + '?dir_name=' + dir_name + '&file=' + file);
        },
        uploadMembers: function(theCourses, file, groupId) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('groupId', groupId);
            return $http.post(settingsFactory.get('courses') + '/' + theCourses.id + '/members/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },
        uploadPreApprovedMembers: function(theCourses, file, groupId) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('groupId', groupId);
            return $http.post(settingsFactory.get('courses') + '/' + theCourses.id + '/members-pre-approved/import', fd,
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                });
        },
        getMembers: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') + '/' + theCourses.id + '/members' + '?' + queryString);
        },
        getMembersPreApproved: function(theCourses, queryString) {
            return $http.get(settingsFactory.get('courses') + '/' + theCourses.id + '/members-pre-approved' + '?' + queryString);
        },
        detachMembers: function(theCourses, dataDetach) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/' + theCourses.id + '/members',
                    method: "DELETE",
                    data: dataDetach,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        toggleStreamingStatus: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('live') + '/' + theCourses.id + '/toggleStreamingStatus',
                    method: "PUT",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
    }
}]);