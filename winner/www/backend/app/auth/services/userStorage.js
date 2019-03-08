/**
 * Created by m on 31/3/2558.
 */

'use strict';

angular.module('newApp')
    .factory('userStorage', ['$window', function ($window) {
        return {
            setUser: function(user) {
                $window.localStorage && $window.localStorage.setItem('userData', JSON.stringify(user));
                return this;
            },
            getUser: function() {
                return $window.localStorage && JSON.parse($window.localStorage.getItem('userData'));
            },
            deleteUser: function() {
                return $window.localStorage && $window.localStorage.removeItem('userData');
            }
        };

    }]);

