/**
 * Created by m on 31/3/2558.
 */
'use strict';

angular.module('newApp')
    .factory('authenticationService', ['$http', '$sanitize', '$timeout', 'sessionService', 'userStorage', 'settingsFactory', 'functionsFactory', function ($http, $sanitize, $timeout, sessionService, userStorage, settingsFactory, functionsFactory) {
        var cacheSession = function() {
            sessionService.set('authenticated', true);
        };

        var uncacheSession = function() {
            sessionService.unset('authenticated');
        };

        var loginError = function(response) {
            //flashService.show(response.flash);
        };

        var saveUser = function(data) {
            //console.log(data);
            userStorage.setUser(data);
        };

        var deleteUser = function() {
            userStorage.deleteUser();
        };

        var getUser = function() {
            return userStorage.getUser();
        };

        var getCsrf = function() {
            $http.get(settingsFactory.get('csrf') + '/token').success(function(data) {
                // console.log(data);
            });
        };

        var forgetTempSession = function() {
            return $http.delete(settingsFactory.get('auth') + '/temp/session');
        };

        var sanitizeCredentials = function(credentials) {
            return {
                username: $sanitize(credentials.username),
                password: $sanitize(credentials.password),
                remember: credentials.remember,
                forceLogin: credentials.forceLogin,
                // csrf_token: CSRF_TOKEN
            };
        };

        var changePassword = function(data) {
            data.change_password = $sanitize(data.change_password)
            return $http.post(settingsFactory.get('admins') + '/change-password', data);
        };

        var updateUser = function() {
            return $http.put(settingsFactory.get('auth')).success(function(data) {
                userStorage.setUser(data);
            });
            // $http({
            //     url: settingsFactory.get('auth'),
            //     method: "PUT",
            //     // data: data,
            //     headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            // }).success(function(data) {
            //     userStorage.setUser(data);
            // });
        };

        return {
            login: function(credentials) {
                return $http.post(settingsFactory.get('auth') + '/login', sanitizeCredentials(credentials));
                // login.success(cacheSession);
                // login.success(saveUser);
                // login.error(loginError);
                // return login;
            },
            logout: function() {
                var logout = $http.get(settingsFactory.get('auth') + '/logout');
                logout.success(uncacheSession);
                logout.success(deleteUser);
                return logout;
            },
            isLoggedIn: function() {
                return sessionService.get('authenticated');
            },
            getUser: function() {
                return getUser();
            },
            cacheSession: function() {
                return cacheSession();
            },
            saveUser: function(user) {
                return saveUser(user);
            },
            getCsrf: function() {
                return getCsrf();
            },
            changePassword: function(data) {
                return changePassword(data);
            },
            forgetTempSession: function() {
                return forgetTempSession();
            },
            updateUser: function() {
                return updateUser();
            }
        };
}]);