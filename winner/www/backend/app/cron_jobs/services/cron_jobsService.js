angular.module('newApp').factory('cron_jobsFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    return {
        monitor: function(code) {
            return $http.get(settingsFactory.get('cron_jobs') + '/'+code+'/monitor');
        }
    }
}]);




