angular.module('newApp').controller('mainCtrl',
    ['$scope', '$rootScope', '$timeout', 'applicationService', 'quickViewService', 'builderService', 'pluginsService', '$location', '$interval', 'authenticationService', 'settingsFactory', 'FileUploader', 'cron_jobsFactory',
        function ($scope, $rootScope, $timeout, applicationService, quickViewService, builderService, pluginsService, $location, $interval, authenticationService, settingsFactory, FileUploader, cron_jobsFactory) {

            // $interval(function() {
            //     // console.log("debug");
            //     cron_jobsFactory.monitor("CHECK_JOB_START").success(function(data) {
            //         // console.log('success');
            //     }).error(function(data) {
            //         // console.log('fail');
            //     });
            // }, 60000);

            $rootScope.activeMenu = '';

            $rootScope.createSlide = {
                "courses_id": null,
                "topics_id": null
            };

            $scope.base_admins_avatar = settingsFactory.getURL('base_admins_avatar');

            $scope.admin = authenticationService.getUser();

            $(document).ready(function () {
                applicationService.init();
                quickViewService.init();
                builderService.init();
                pluginsService.init();
                Dropzone.autoDiscover = false;
            });

            $scope.$on('$routeChangeStart', function (event, data) {
                // console.log(event);
                // console.log(data);
                if (data.$$route !== undefined) {
                    $rootScope.activeMenu = data.$$route.menu;
                    // console.log(data.$$route.menu);
                }
            });

            $scope.$on('$viewContentLoaded', function () {
                pluginsService.init();
                applicationService.customScroll();
                applicationService.handlePanelAction();
                $timeout(function() {
                    $('.nav.nav-sidebar .nav-active').removeClass('nav-active active');
                    $('.nav.nav-sidebar .active:not(.nav-parent)').closest('.nav-parent').addClass('nav-active active');
                }, 0);

                var menuParents = _.findIndex([
                    '/configuration',
                    '/groups',
                    '/categories',
                    '/about',
                    '/qa',
                    '/admins_groups',
                    '/admins',
                    '/super_users',
                    '/documents',
                    '/courses',
                    '/topics',
                    '/quiz',
                    '/instructors',
                    '/slides',
                    '/classrooms',
                    '/discussions',
                    '/certificates',
                    '/questionnaire_packs',
                    '/stats',
                    '/stats_live',
                    '/usage_statistic',
                    '/orders',
                    '/payments',
                    '/sub_groups',
                    '/level_groups',
                ], function(o) { return $location.$$path.indexOf(o) == 0; });

                // if($location.$$path == '/' || $location.$$path == '/layout-api'){
                if(menuParents === -1){
                    $('.nav.nav-sidebar .nav-parent').removeClass('nav-active active');
                    $('.nav.nav-sidebar .nav-parent .children').removeClass('nav-active active');
                    if ($('body').hasClass('sidebar-collapsed') && !$('body').hasClass('sidebar-hover')) return;
                    if ($('body').hasClass('submenu-hover')) return;
                    $('.nav.nav-sidebar .nav-parent .children').slideUp(200);
                    $('.nav-sidebar .arrow').removeClass('active');
                }
                if($location.$$path == '/'){
                    $('body').addClass('dashboard');
                }
                else{
                    $('body').removeClass('dashboard');
                }

            });

            $scope.isActive = function (viewLocation) {
                // if (viewLocation === $location.path()) {
                //     return true;
                // }

                // var indexView = $location.path().indexOf(viewLocation);
                // if (indexView > -1) {
                //     return true;
                //     // if (indexView > 0) {
                //     //     return true;
                //     // } else {
                //     //     return true;
                //     // }
                // }

                // return false;
                return viewLocation === $location.path();
            };

            $scope.logout = function() {
                authenticationService.logout().success(function(data) {
                    window.location.href = '/backend/login.html';
                });
            };

        }]);
