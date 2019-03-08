'use strict';

/**
 * @ngdoc overview
 * @name newappApp
 * @description
 * # newappApp
 *
 * Main module of the application.
 */
var MakeApp = angular
    .module('newApp', [
        'ngAnimate',
        'ngCookies',
        'ngResource',
        'ngRoute',
        'ngSanitize',
        'ngTouch',
        'ui.bootstrap',
        'angularFileUpload',
        'checklist-model',
        'summernote',
        'ui.sortable',
        'frapontillo.bootstrap-duallistbox',
        'blueimp.fileupload',
        'ngclipboard'
    ]);

MakeApp.config(['$routeProvider', '$httpProvider', function ($routeProvider, $httpProvider) {
    $routeProvider
        .when('/', {
            templateUrl: 'app/dashboard/views/dashboard.html',
            controller: 'dashboardCtrl',
            menu: '/dashboard'
        })
        .when('/configuration', {
            templateUrl: 'app/configuration/views/edit.html',
            controller: 'configurationCtrl',
            menu: '/configuration'
        })
        .when('/groups', {
            templateUrl: 'app/groups/views/list.html',
            controller: 'groupsCtrl',
            menu: '/groups'
        })
        .when('/groups/create', {
            templateUrl: 'app/groups/views/create-edit.html',
            controller: 'groupsCtrl',
            menu: '/groups'
        })
        .when('/groups/:id/edit', {
            templateUrl: 'app/groups/views/create-edit.html',
            controller: 'groupsCtrl',
            menu: '/groups'
        })
        .when('/groups/:selected_groups/courses', {
            templateUrl: 'app/courses/views/list.html',
            controller: 'coursesCtrl',
            menu: '/groups'
        })
        .when('/categories', {
            templateUrl: 'app/categories/views/list.html',
            controller: 'categoriesCtrl',
            menu: '/categories'
        })
        .when('/categories/create', {
            templateUrl: 'app/categories/views/create-edit.html',
            controller: 'categoriesCtrl',
            menu: '/categories'
        })
        .when('/categories/:id/edit', {
            templateUrl: 'app/categories/views/create-edit.html',
            controller: 'categoriesCtrl',
            menu: '/categories'
        })
        .when('/about', {
            templateUrl: 'app/configuration/views/about.html',
            controller: 'configurationCtrl',
            menu: '/about'
        })
        .when('/qa', {
            templateUrl: 'app/qa/views/list.html',
            controller: 'qaCtrl',
            menu: '/qa'
        })
        .when('/qa/create', {
            templateUrl: 'app/qa/views/create-edit.html',
            controller: 'qaCtrl',
            menu: '/qa'
        })
        .when('/qa/:id/edit', {
            templateUrl: 'app/qa/views/create-edit.html',
            controller: 'qaCtrl',
            menu: '/qa'
        })
        .when('/admins', {
            templateUrl: 'app/admins/views/list.html',
            controller: 'adminsCtrl',
            menu: '/admins'
        })
        .when('/admins/create', {
            templateUrl: 'app/admins/views/create-edit.html',
            controller: 'adminsCtrl',
            menu: '/admins'
        })
        .when('/admins/:id/edit', {
            templateUrl: 'app/admins/views/create-edit.html',
            controller: 'adminsCtrl',
            menu: '/admins'
        })
        .when('/admins_groups', {
            templateUrl: 'app/admins_groups/views/list.html',
            controller: 'admins_groupsCtrl',
            menu: '/admins_groups'
        })
        .when('/admins_groups/create', {
            templateUrl: 'app/admins_groups/views/create-edit.html',
            controller: 'admins_groupsCtrl',
            menu: '/admins_groups'
        })
        .when('/admins_groups/:id/edit', {
            templateUrl: 'app/admins_groups/views/create-edit.html',
            controller: 'admins_groupsCtrl',
            menu: '/admins_groups'
        })
        .when('/super_users', {
            templateUrl: 'app/super_users/views/list.html',
            controller: 'super_usersCtrl',
            menu: '/super_users'
        })
        .when('/super_users/create', {
            templateUrl: 'app/super_users/views/create-edit.html',
            controller: 'super_usersCtrl',
            menu: '/super_users'
        })
        .when('/super_users/:id/edit', {
            templateUrl: 'app/super_users/views/create-edit.html',
            controller: 'super_usersCtrl',
            menu: '/super_users'
        })
        .when('/my_profile/edit', {
            templateUrl: 'app/my_profile/views/create-edit.html',
            controller: 'my_profileCtrl',
            menu: '/my_profile'
        })
        .when('/documents', {
            templateUrl: 'app/documents/views/list.html',
            controller: 'documentsCtrl',
            menu: '/documents'
        })
        .when('/documents/create', {
            templateUrl: 'app/documents/views/create-edit.html',
            controller: 'documentsCtrl',
            menu: '/documents'
        })
        .when('/documents/:id/edit', {
            templateUrl: 'app/documents/views/create-edit.html',
            controller: 'documentsCtrl',
            menu: '/documents'
        })
        .when('/courses/:selected_courses/documents', {
            templateUrl: 'app/documents/views/list.html',
            controller: 'documentsCtrl',
            menu: '/documents'
        })
        .when('/courses', {
            templateUrl: 'app/courses/views/list.html',
            controller: 'coursesCtrl',
            menu: '/courses'
        })
        .when('/courses/create', {
            templateUrl: 'app/courses/views/create-edit.html',
            controller: 'coursesCtrl',
            menu: '/courses'
        })
        .when('/courses/:id/edit', {
            templateUrl: 'app/courses/views/create-edit.html',
            controller: 'coursesCtrl',
            menu: '/courses'
        })
        .when('/topics', {
            templateUrl: 'app/topics/views/list.html',
            controller: 'topicsCtrl',
            menu: '/topics'
        })
        .when('/topics/create', {
            templateUrl: 'app/topics/views/create-edit.html',
            controller: 'topicsCtrl',
            menu: '/topics'
        })
        .when('/topics/:id/edit', {
            templateUrl: 'app/topics/views/create-edit.html',
            controller: 'topicsCtrl',
            menu: '/topics'
        })
        .when('/livestreams/:id/control', {
            templateUrl: 'app/livestreams/views/livestreams.html?v=1.0.4',
            controller: 'livestreamsCtrl',
            menu: '/topics'
        })
        .when('/videos/:selected_videos/subtitles/create', {
            templateUrl: 'app/subtitles/views/create-edit.html',
            controller: 'subtitlesCtrl',
            menu: '/topics'
        })
        .when('/courses/:selected_courses/topics', {
            templateUrl: 'app/topics/views/list.html',
            controller: 'topicsCtrl',
            menu: '/topics'
        })
        .when('/quiz', {
            templateUrl: 'app/quiz/views/list.html',
            controller: 'quizCtrl',
            menu: '/quiz'
        })
        .when('/quiz/create', {
            templateUrl: 'app/quiz/views/create-edit.html',
            controller: 'quizCtrl',
            menu: '/quiz'
        })
        .when('/quiz/:id/edit', {
            templateUrl: 'app/quiz/views/create-edit.html',
            controller: 'quizCtrl',
            menu: '/quiz'
        })
        .when('/courses/:selected_courses/quiz', {
            templateUrl: 'app/quiz/views/list.html',
            controller: 'quizCtrl',
            menu: '/quiz'
        })
        .when('/questions', {
            templateUrl: 'app/questions/views/list.html',
            controller: 'questionsCtrl',
            menu: '/quiz'
        })
        .when('/questions/create', {
            templateUrl: 'app/questions/views/create-edit.html',
            controller: 'questionsCtrl',
            menu: '/quiz'
        })
        .when('/questions/create/:quizID', {
            templateUrl: 'app/questions/views/create-edit.html',
            controller: 'questionsCtrl',
            menu: '/quiz'
        })
        .when('/questions/:id/edit', {
            templateUrl: 'app/questions/views/create-edit.html',
            controller: 'questionsCtrl',
            menu: '/quiz'
        })
        .when('/quiz/:selected_quiz/questions', {
            templateUrl: 'app/questions/views/list.html',
            controller: 'questionsCtrl',
            menu: '/quiz'
        })
        .when('/instructors', {
            templateUrl: 'app/instructors/views/list.html',
            controller: 'instructorsCtrl',
            menu: '/instructors'
        })
        .when('/instructors/create', {
            templateUrl: 'app/instructors/views/create-edit.html',
            controller: 'instructorsCtrl',
            menu: '/instructors'
        })
        .when('/instructors/:id/edit', {
            templateUrl: 'app/instructors/views/create-edit.html',
            controller: 'instructorsCtrl',
            menu: '/instructors'
        })
        .when('/slides', {
            templateUrl: 'app/slides/views/list.html',
            controller: 'slidesCtrl',
            menu: '/slides'
        })
        .when('/slides/create', {
            templateUrl: 'app/slides/views/create-edit.html',
            controller: 'slidesCtrl',
            menu: '/slides'
        })
        .when('/slides/:id/edit', {
            templateUrl: 'app/slides/views/create-edit.html',
            controller: 'slidesCtrl',
            menu: '/slides'
        })
        .when('/slides/:id/sync', {
            templateUrl: 'app/slides/views/sync.html',
            controller: 'slidesCtrl',
            menu: '/slides'
        })
        .when('/slides/:selected_courses/courses', {
            templateUrl: 'app/slides/views/list.html',
            controller: 'slidesCtrl',
            menu: '/courses'
        })
        .when('/classrooms', {
            templateUrl: 'app/classrooms/views/list.html',
            controller: 'classroomsCtrl',
            menu: '/classrooms'
        })
        .when('/classrooms/create', {
            templateUrl: 'app/classrooms/views/create-edit.html',
            controller: 'classroomsCtrl',
            menu: '/classrooms'
        })
        .when('/classrooms/:id/edit', {
            templateUrl: 'app/classrooms/views/create-edit.html',
            controller: 'classroomsCtrl',
            menu: '/classrooms'
        })
        .when('/discussions', {
            templateUrl: 'app/discussions/views/list.html',
            controller: 'discussionsCtrl',
            menu: '/discussions'
        })
        .when('/discussions/create', {
            templateUrl: 'app/discussions/views/create-edit.html',
            controller: 'discussionsCtrl',
            menu: '/discussions'
        })
        .when('/discussions/:id/edit', {
            templateUrl: 'app/discussions/views/create-edit.html',
            controller: 'discussionsCtrl',
            menu: '/discussions'
        })
        .when('/discussions/:id/board', {
            templateUrl: 'app/discussions/views/board.html',
            controller: 'discussionsBoardCtrl',
            menu: '/discussions'
        })
        .when('/courses/:selected_courses/discussions', {
            templateUrl: 'app/discussions/views/list.html',
            controller: 'discussionsCtrl',
            menu: '/discussions'
        })
        .when('/certificates', {
            templateUrl: 'app/certificates/views/list.html',
            controller: 'certificatesCtrl',
            menu: '/certificates'
        })
        .when('/certificates/create', {
            templateUrl: 'app/certificates/views/create-edit.html',
            controller: 'certificatesCtrl',
            menu: '/certificates'
        })
        .when('/certificates/:id/edit', {
            templateUrl: 'app/certificates/views/create-edit.html',
            controller: 'certificatesCtrl',
            menu: '/certificates'
        })
        .when('/questionnaire_packs', {
            templateUrl: 'app/questionnaire_packs/views/list.html',
            controller: 'questionnaire_packsCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaire_packs/create', {
            templateUrl: 'app/questionnaire_packs/views/create-edit.html',
            controller: 'questionnaire_packsCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaire_packs/:id/edit', {
            templateUrl: 'app/questionnaire_packs/views/create-edit.html',
            controller: 'questionnaire_packsCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/groups/:selected_groups/questionnaire_packs', {
            templateUrl: 'app/questionnaire_packs/views/list.html',
            controller: 'questionnaire_packsCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaires', {
            templateUrl: 'app/questionnaires/views/list.html',
            controller: 'questionnairesCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaires/create', {
            templateUrl: 'app/questionnaires/views/create-edit.html',
            controller: 'questionnairesCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaires/create/:questionnaire_packsID', {
            templateUrl: 'app/questionnaires/views/create-edit.html',
            controller: 'questionnairesCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaires/:id/edit', {
            templateUrl: 'app/questionnaires/views/create-edit.html',
            controller: 'questionnairesCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/questionnaire_packs/:selected_questionnaire_packs/questionnaires', {
            templateUrl: 'app/questionnaires/views/list.html',
            controller: 'questionnairesCtrl',
            menu: '/questionnaire_packs'
        })
        .when('/stats', {
            templateUrl: 'app/stats/views/list.html',
            controller: 'statsCtrl',
            menu: '/stats'
        })
        .when('/stats_live', {
            templateUrl: 'app/stats_live/views/list.html',
            controller: 'stats_liveCtrl',
            menu: '/stats_live'
        })
        .when('/usage_statistic', {
            templateUrl: 'app/usage_statistic/views/list.html',
            controller: 'usage_statisticCtrl',
            menu: '/usage_statistic'
        })
        .when('/courses/:selected_courses/usage_statistic', {
            templateUrl: 'app/usage_statistic/views/list.html',
            controller: 'usage_statisticCtrl',
            menu: '/usage_statistic'
        })
        .when('/usage_statistic/:id/view', {
            templateUrl: 'app/usage_statistic/views/view.html',
            controller: 'usage_statisticCtrl',
            menu: '/usage_statistic'
        })
        .when('/orders/:search/items', {
            templateUrl: 'app/items/views/list.html',
            controller: 'itemsCtrl',
            menu: '/items'
        })
        .when('/orders', {
            templateUrl: 'app/orders/views/list.html',
            controller: 'ordersCtrl',
            menu: '/orders'
        })
        .when('/orders/:id/view', {
            templateUrl: 'app/orders/views/view.html',
            controller: 'ordersCtrl',
            menu: '/orders'
        })
        .when('/payments', {
            templateUrl: 'app/payments/views/list.html',
            controller: 'paymentsCtrl',
            menu: '/payments'
        })
        .when('/payments/:id/view', {
            templateUrl: 'app/payments/views/view.html',
            controller: 'paymentsCtrl',
            menu: '/payments'
        })
        .when('/members', {
            templateUrl: 'app/members/views/list.html',
            controller: 'membersCtrl',
            menu: '/members'
        })
        .when('/members/create', {
            templateUrl: 'app/members/views/create-edit.html',
            controller: 'membersCtrl',
            menu: '/members'
        })
        .when('/members/:id/edit', {
            templateUrl: 'app/members/views/create-edit.html',
            controller: 'membersCtrl',
            menu: '/members'
        })
        .when('/members_pre_approved/create', {
            templateUrl: 'app/members_pre_approved/views/create-edit.html',
            controller: 'members_pre_approvedCtrl',
            menu: '/members'
        })
        .when('/members_pre_approved/:id/edit', {
            templateUrl: 'app/members_pre_approved/views/create-edit.html',
            controller: 'members_pre_approvedCtrl',
            menu: '/members'
        })
        .when('/highlights', {
            templateUrl: 'app/highlights/views/list.html',
            controller: 'highlightsCtrl',
            menu: '/highlights'
        })
        .when('/highlights/create', {
            templateUrl: 'app/highlights/views/create-edit.html',
            controller: 'highlightsCtrl',
            menu: '/highlights'
        })
        .when('/highlights/:id/edit', {
            templateUrl: 'app/highlights/views/create-edit.html',
            controller: 'highlightsCtrl',
            menu: '/highlights'
        })
        .when('/sub_groups', {
            templateUrl: 'app/sub_groups/views/list.html',
            controller: 'sub_groupsCtrl',
            menu: '/sub_groups'
        })
        .when('/sub_groups/create', {
            templateUrl: 'app/sub_groups/views/create-edit.html',
            controller: 'sub_groupsCtrl',
            menu: '/sub_groups'
        })
        .when('/sub_groups/:id/edit', {
            templateUrl: 'app/sub_groups/views/create-edit.html',
            controller: 'sub_groupsCtrl',
            menu: '/sub_groups'
        })
        .when('/level_groups', {
            templateUrl: 'app/level_groups/views/list.html',
            controller: 'level_groupsCtrl',
            menu: '/level_groups'
        })
        .when('/level_groups/create', {
            templateUrl: 'app/level_groups/views/create-edit.html',
            controller: 'level_groupsCtrl',
            menu: '/level_groups'
        })
        .when('/level_groups/:id/edit', {
            templateUrl: 'app/level_groups/views/create-edit.html',
            controller: 'level_groupsCtrl',
            menu: '/level_groups'
        })
        .otherwise({
            redirectTo: '/'
        });

    var logsOutUserOn401 = function ($location, $q, sessionService) {
        return {
            'response': function (response) {
                return response;
            },
            'responseError': function (rejection) {
                if (rejection.status === 401) {
                    sessionService.unset('authenticated');
                    window.location.href = "login.html";
                    return $q.reject(rejection);
                }
                return $q.reject(rejection);
            }
        }
    };

    var handle403 = function ($location, $q, $timeout, sessionService) {
        return {
            'response': function (response) {
                return response;
            },
            'responseError': function (rejection) {
                if (rejection.status === 403) {
                    angular.element('.page-content').hide();
                    $timeout(function() {
                        $location.path('/');
                    }, 1000)
                    return $q.reject(rejection);
                }
                return $q.reject(rejection);
            }
        }
    };

    $httpProvider.interceptors.push(logsOutUserOn401);
    $httpProvider.interceptors.push(handle403);
    $httpProvider.defaults.withCredentials = true;

}]);

// Route State Load Spinner(used on page or content load)
MakeApp.directive('ngSpinnerLoader', ['$rootScope',
    function ($rootScope) {
        return {
            link: function (scope, element, attrs) {
                // by defult hide the spinner bar
                element.addClass('hide'); // hide spinner bar by default
                // display the spinner bar whenever the route changes(the content part started loading)
                $rootScope.$on('$routeChangeStart', function () {
                    element.removeClass('hide'); // show spinner bar
                });
                // hide the spinner bar on rounte change success(after the content loaded)
                $rootScope.$on('$routeChangeSuccess', function () {
                    setTimeout(function () {
                        element.addClass('hide'); // hide spinner bar
                    }, 500);
                    $("html, body").animate({
                        scrollTop: 0
                    }, 500);
                });
            }
        };
    }
]);
MakeApp.directive('ckEditor', function () {
    return {
        require: '?ngModel',
        link: function (scope, elm, attr, ngModel) {
            var ck = CKEDITOR.replace(elm[0]);
            if (!ngModel) return;
            ck.on('instanceReady', function () {
                setTimeout(function() {
                    ck.setData(ngModel.$viewValue);
                }, 300);
            });
            function updateModel() {
                scope.$apply(function () {
                    ngModel.$setViewValue(ck.getData());
                });
            }
            ck.on('change', updateModel);
            ck.on('key', updateModel);
            // ck.on('dataReady', updateModel);
            ck.on('pasteState', updateModel);

            ngModel.$render = function (value) {
                ck.setData(ngModel.$viewValue);
            };
        }
    };
});

MakeApp.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

MakeApp.filter("trustUrl", ['$sce', function ($sce) {
    return function (recordingUrl) {
        return $sce.trustAsResourceUrl(recordingUrl);
    };
}]);

MakeApp.filter('stripTags', function() {
    return function(text) {
        return  text ? String(text).replace(/<[^>]+>/gm, '') : '';
    };
});

var redirectToLogin = function () {
    window.location.href = "login.html";
};

MakeApp.run(function ($rootScope, $location, authenticationService) {

    var clearPlayer = function() {
        if ($("#player").length > 0) {
            jwplayer("player").remove();
            // console.log("Successfully player instances removed.");
        }
    };

    var checkIgnoreMenu = function(typeMenu) {
        var ignoreMenu = ["#/members_pre_approved", "#/questions", "#/questionnaires", "#/my_profile", "#/livestreams", "#/videos"];
        var isIgnore;

        switch(typeMenu) {
            case 1: isIgnore = $.inArray(window.location.hash, ignoreMenu) >= 0 ? true : false; break;
            case 2: isIgnore = $.inArray(window.location.hash.substring(0, window.location.hash.indexOf("/", 2)), ignoreMenu) >= 0 ? true : false; break;
            default: isIgnore = false; break;
        }

        return isIgnore;
    };

    var checkHash = function(link) {
        if (window.location.hash.indexOf("/", 2) !== -1) {
            if (checkIgnoreMenu(2)) {
                return false;
            } else {
                return window.location.hash.substring(0, window.location.hash.indexOf("/", 2)) !== "#/"+link;
            }
        } else {
            if (checkIgnoreMenu(1)) {
                return false;
            } else {
                return window.location.hash !== "#/"+link;
            }
        }
    };

    var checkMenuAccess = function() {
        var admin = authenticationService.getUser();
        var isMenuNotAllow = admin.admins_groups.admins_groups_menu.every(function(menu) {
            if (menu.sub_menu !== undefined) {
                return menu.sub_menu.every(function(sub_menu) {
                    return checkHash(sub_menu.link);
                });
            } else {
                return checkHash(menu.link);
            }
        });

        return !isMenuNotAllow;
    };

    if (window.location.pathname.search("login.html") === -1) {
        if (authenticationService.isLoggedIn() !== "true" ||
            typeof authenticationService.getUser() == "undefined" ||
            authenticationService.getUser() == "" ||
            authenticationService.getUser() == null) {
            redirectToLogin();
        } else if (!checkMenuAccess()) {
            window.location.href = "/backend/#/";
            // console.log("Menu Access Denied!. #1");
        }
    }

    $rootScope.$on('$routeChangeStart', function (event, next, current) {
        if (authenticationService.isLoggedIn() !== "true" ||
            typeof authenticationService.getUser() == "undefined" ||
            authenticationService.getUser() == "" ||
            authenticationService.getUser() == null) {
            redirectToLogin();
        } else if (!checkMenuAccess()) {
            $location.path('/');
            // console.log("Menu Access Denied!. #2");
        }

        clearPlayer();
    });
});
