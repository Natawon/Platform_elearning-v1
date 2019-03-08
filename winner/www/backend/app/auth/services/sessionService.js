/**
 * Created by m on 31/3/2558.
 */

'use strict';

angular.module('newApp')
    .factory('sessionService', ['$window', function ($window) {
        return {
            get: function(key) {
                //return sessionStorage.getItem(key);
                return $window.localStorage && $window.localStorage.getItem(key);
            },
            set: function(key, val) {
                //return sessionStorage.setItem(key, val);
                return $window.localStorage && $window.localStorage.setItem(key, val);
            },
            unset: function(key) {
                //return sessionStorage.removeItem(key);
                return $window.localStorage && $window.localStorage.removeItem(key);
            }
        };
    }]);

