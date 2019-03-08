'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('discussionsBoardCtrl', ['$scope', '$rootScope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', 'discussionsFactory', 'coursesFactory', 'groupsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $rootScope, $sce, $routeParams, $location, $route, $filter, $timeout, discussionsFactory, coursesFactory, groupsFactory, functionsFactory, settingsFactory) {

        $scope.discussions_data = {};
        $scope.discussions_data_send = {};
        $scope.selected_discussion = null;

        $scope.type_discussions_file = "send";
        $scope.base_discussions_file = settingsFactory.getURL('base_discussions_file');

        $scope.getDiscussion = function(createdId) {
            if (!angular.isUndefined($routeParams.id)) {
                discussionsFactory.get({id: $routeParams.id}).success(function (data) {
                    $scope.discussions_data = data;
                    $scope.discussions_data_send.groups_id = $scope.discussions_data.groups_id;
                    $scope.discussions_data_send.courses_id = $scope.discussions_data.courses_id;
                    $scope.discussions_data_send.parent_id = $scope.discussions_data.id;

                    if ($scope.discussions_data.is_public == 1) {
                        $('#checkboxIsPublic').iCheck('check');
                    }

                    if ($scope.discussions_data.is_sent_instructor == 1) {
                        $('#checkboxIsSentInstructor').iCheck('check');
                    }

                    discussionsFactory.read({'id': $scope.discussions_data.id})
                        .success(function(data) {
                            if (data.is_error == false) {
                                console.log(data.message);
                            } else {
                                console.log("Failed to read.");
                            }
                        })
                        .error(function() {
                            console.log("Failed to read.");
                        });

                    if (createdId !== undefined) {
                        $timeout(function() {
                            if ($('#discussionId_'+createdId).length > 0) {
                                angular.element('body, html').animate({
                                    scrollTop: $('#discussionId_'+createdId).offset().top - 100
                                }, "slow");

                                // setTimeout(function() {
                                //     $('#discussionId_'+createdId).fadeOut('fast').delay(100).fadeIn(700);
                                // }, 800);
                            }
                        }, 400);
                    } else {
                        $timeout(function() {
                            if ($('.panel-hl-warning').length > 0) {
                                angular.element('body, html').animate({
                                    scrollTop: $('.panel-hl-warning').offset().top - 100
                                }, "slow");
                            }
                        }, 1000);
                    }

                });
            }
        };

        $scope.getDiscussion();

        $('#checkboxIsPublic').on('ifClicked', function(event){
            console.log(event);
            $scope.updateIsPublic($scope.discussions_data);
        });

        $('#checkboxIsSentInstructor').on('ifClicked', function(event){
            $scope.updateIsSentInstructor($scope.discussions_data);
        });

        $scope.updateIsPublic = function(theDiscussions) {
            if (theDiscussions.is_public == 1) { theDiscussions.is_public = 0; } else { theDiscussions.is_public = 1; }
            discussionsFactory.updateIsPublic({'id': theDiscussions.id, 'is_public': theDiscussions.is_public})
                .success(function(data) {
                    if (data.is_error == false) {
                        functionsFactory.notification("success",data.message);
                    } else {
                        functionsFactory.notification("error",data.message);
                    }
                })
                .error(function() {
                    functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.updateIsSentInstructor = function(theDiscussions) {
            if (theDiscussions.is_sent_instructor == 1) { theDiscussions.is_sent_instructor = 0; } else { theDiscussions.is_sent_instructor = 1; }
            discussionsFactory.updateIsSentInstructor({'id': theDiscussions.id, 'is_sent_instructor': theDiscussions.is_sent_instructor})
                .success(function(data) {
                    if (data.is_error == false) {
                        functionsFactory.notification("success",data.message);
                    } else {
                        functionsFactory.notification("error",data.message);
                    }
                })
                .error(function() {
                    functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.reply = function (theParentDiscussions, type) {
            if (theParentDiscussions !== undefined && type !== undefined) {
                $scope.selected_discussion = theParentDiscussions;

                if (type == 1) {
                    angular.element('#panel-selected_discussion').removeClass('bg-light');
                    angular.element('#panel-selected_discussion').removeClass('panel-border-2-lightgrey');
                    angular.element('#panel-selected_discussion').addClass('panel-border-2-grey')

                    $scope.discussions_data_send.parent_id = theParentDiscussions.id;
                    delete $scope.discussions_data_send.mention_id

                    $scope.discussions_data_send.description = "";
                } else {
                    angular.element('#panel-selected_discussion').removeClass('panel-border-2-grey');
                    angular.element('#panel-selected_discussion').addClass('bg-light').addClass('panel-border-2-lightgrey')

                    $scope.discussions_data_send.parent_id = theParentDiscussions.parent_id;
                    $scope.discussions_data_send.mention_id = theParentDiscussions.id;

                    if (theParentDiscussions.type == 0) {
                        $scope.discussions_data_send.description = "@" + theParentDiscussions.members.first_name + ' ' + theParentDiscussions.members.last_name + " ";
                    } else if (theParentDiscussions.type == 1) {
                        $scope.discussions_data_send.description = "@" + theParentDiscussions.modify_by + " ";
                    } else {
                        $scope.discussions_data_send.description = "@" + theParentDiscussions.instructors.title + " ";
                    }
                }
            } else {
                $scope.cancelReply();
            }

            angular.element('#description').focus();
        };

        $scope.cancelReply = function (theParentDiscussions, type) {
            $scope.selected_discussion = null;
            $scope.discussions_data_send.parent_id = $scope.discussions_data.id;
            delete $scope.discussions_data_send.mention_id
            $scope.discussions_data_send.description = "";
        };

        $scope.updateIsReject = function(theDiscussions) {
            if (theDiscussions.is_reject == 1) { theDiscussions.is_reject = 0; } else { theDiscussions.is_reject = 1; }
            discussionsFactory.updateIsReject({'id': theDiscussions.id, 'is_reject': theDiscussions.is_reject, 'reject_remark': theDiscussions.reject_remark})
                .success(function(data) {
                    if (data.is_error == false) {
                        angular.element('#rejectRemarkModal').modal('hide');
                        functionsFactory.notification("success",data.message);
                        $timeout(function() {
                            window.location.reload();
                        }, 500);
                    } else {
                        functionsFactory.notification("error",data.message);
                    }
                })
                .error(function() {
                    functionsFactory.notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.rejectModal = function(theDiscussions) {
            $scope.reject_discussion_data = theDiscussions;

            angular.element('#rejectRemarkModal').modal('show');
            return false;
        };

        $scope.viewRejectRemarkModal = function(theDiscussions) {
            discussionsFactory.get({id: theDiscussions.id}).success(function (data) {
                $scope.show_reject_discussion_data = data;
                angular.element('#viewRejectRemarkModal').modal('show');
            });
        };

        $scope.submitDiscussions = function (theDiscussions, nextAction) {
            // console.log(theDiscussions);
            // return false;
            functionsFactory.clearError(angular.element('.discussions-frm'));
            discussionsFactory.send(theDiscussions)
                .success(function (data) {
                    if (data.is_error == false) {
                        functionsFactory.notification("success", data.message);
                        // $route.reload();
                        $scope.cancelReply();
                        $scope.getDiscussion(data.createdId);
                        // console.log(data);

                        // switch (nextAction) {
                        //     case 'add_another'      : $route.reload(); break;
                        //     case 'continue_editing' : $location.path('discussions/'+ data.createdId +'/edit').search({}); break;
                        //     default                 : theDiscussions.courses_id ? $location.path('courses/'+ theDiscussions.courses_id +'/discussions').search({}) : $location.path('discussions').search({}); break;
                        // }
                    }
                    if (data.is_error == true) {
                        functionsFactory.notification("error", data.message);
                    }
                })
                .error(function (data) {
                    functionsFactory.handleError(data, angular.element('.discussions-frm'));
                });
        };

    }]);
