
angular.module('newApp').factory('certificatesFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        query: function(queryString) {
            return $http.get(settingsFactory.get('certificates') + '?' + queryString);
        },
        get: function(theCertificates) {
            return $http.get(settingsFactory.get('certificates') + '/' + theCertificates.id);
        },
        update: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/' + theCertificates.id,
                    method: "PUT",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        updateStatus: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/' + theCertificates.id + '/status',
                    method: "PUT",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        create: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates'),
                    method: "POST",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        sort: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/sort',
                    method: "PUT",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/json'}
                }
            );
        },
        delete: function(theCertificates) {
            return $http.delete(settingsFactory.get('certificates') + '/' + theCertificates.id);
        },
        search: function(queryString) {
            return $http.get(settingsFactory.get('certificates') + '/search?' + queryString);
        },
        all: function(queryString) {
            return $http.get(settingsFactory.get('certificates') + '/all' + '?' + queryString);
        },
        orders: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/orders',
                    method: "POST",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        createPreview: function(theCertificates) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/preview',
                    method: "POST",
                    data: theCertificates,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        createPreviewByCourse: function(theCourses) {
            return $http(
                {
                    url: settingsFactory.get('certificates') + '/preview/courses',
                    method: "POST",
                    data: theCourses,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        deletePreview: function(filename) {
            return $http.delete(settingsFactory.get('certificates') + '/' + filename + '/preview');
        },
    }
}]);




