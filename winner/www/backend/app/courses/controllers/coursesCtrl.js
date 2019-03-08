'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('coursesCtrl', ['$scope', '$sce', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'coursesFactory', 'topicsFactory', 'groupsFactory', 'level_groupsFactory', 'sub_groupsFactory', 'adminsFactory', 'categoriesFactory', 'instructorsFactory', 'membersFactory', 'members_pre_approvedFactory', 'certificatesFactory', 'imagesFactory', 'pluginsService', 'functionsFactory', 'settingsFactory',
    function ($scope, $sce, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, coursesFactory, topicsFactory, groupsFactory, level_groupsFactory, sub_groupsFactory, adminsFactory, categoriesFactory, instructorsFactory, membersFactory, members_pre_approvedFactory, certificatesFactory, imagesFactory, pluginsService, functionsFactory, settingsFactory) {

        $scope._isArray = angular.isArray;

        $scope.videos = {};
        $scope.courses = {};
        $scope.courses_all = [];
        $scope.courses_data = {
            // "getting_certificate": '<p>&nbsp; &nbsp; &nbsp; เข้าเรียนไม่น้อยกว่า 80% ของเวลาเรียนทั้งหมด&nbsp;<br />&nbsp; &nbsp; &nbsp; และสอบผ่านไม่น้อยกว่า 70% ของคะแนนโดยรวมทั้งหมด</p>',
            "getting_certificate": '<p class="t-indent-22">เข้าเรียนไม่น้อยกว่า 80% ของเวลาเรียนทั้งหมด และสอบผ่านไม่น้อยกว่า 70% ของคะแนนโดยรวมทั้งหมด</p>',
            "course2related": [],
            "related": [],
            "certificates_used_type": 'default',
            "free": 1,
            "state": 'vod',
            "is_discussion": 1,
            "is_filter": 1,
        };
        $scope.selected_course = {};
        $scope.selected_certificates = {};
        $scope.selected_group_upload = {};
        $scope.groups = [];
        $scope.level_groups = [];
        $scope.targets = [];
        $scope.categories = [];
        $scope.instructors = [];
        $scope.certificates = [];
        $scope.items = [1,2,3,4];
        $scope.search = "";
        $scope.sub_groups = [];

        $scope.mode = "Create";

        $scope.uploadMembersOptions = {
            "exampleDescription": 'ไฟล์ที่จะใช้อัพโหลดจะต้องเป็นไฟล์ที่มีนามสกุล .csv (UTF-8) เท่านั้น ซึ่งผู้ใช้งานสามารถสร้างช้อมูลได้จากโปรแกรมข้อมูลตารางทั่วไป เช่น MS Excel (Windows) หรือ Numbers (OSX) แล้วจึง Export ออกมาเพื่อทำการอัพโหลด<br><br> <strong>หมายเหตุ : </strong><ul class="list-decimal"><li>เครื่องหมาย * ในไฟล์ตัวอย่าง คือฟิล์ดที่จำเป็นต้องใส่ โดยมีรายละเอียดดังนี้<ul><li>เครื่องหมาย * หมายถึง ฟิล์ด Pre-Approved ซึ่งจะต้องใส่ให้ตรงกับฟิล์ด Pre-Approved ที่ระบบกำหนดมาให้ (เพียง 1 ฟิล์ด)</li><li>เครื่องหมาย ** หมายถึง ฟิล์ดที่จำเป็นต้องใส่ เช่น รหัสกลุ่มย่อยหลัก, รหัสกลุ่มย่อย</li></ul></li><li>หากท่านใช้ Microsoft Excel ควรจะใช้เวอร์ชั่นที่รองรับการ Export .csv แบบ UTF-8 หรือถ้าเป็นเวอร์ชั่นที่ไม่รองรับ ท่านจำเป็นต้องตั้งค่าภาษาตามขั้นตอนดังนี้ <ul><li>ไปที่ <b>Start Menu</b> -> คลิก <b>Region</b> -> เลือก <b>Administrative tab</b> -> คลิก <b>Change system locale...</b> -> เลือก <b>Thai (Thailand)</b> -> คลิก <b>OK</b> และ <b>Restart Computer</b></li></ul> </li></ul>',
            "uploadToGroup": null
        };

        $scope.certificates_options = {
            "order_direction": "DESC"
        };

        $scope.images_data = {};
        $scope.images_logos = [];
        $scope.images_signatures = [];

        $scope.images_logos_options = {
            "type": 1,
            "order_direction": "DESC"
        };

        $scope.images_signatures_options = {
            "type": 2,
            "order_direction": "DESC"
        };

        $scope.imagesField = "";

        $scope.base_images_logo = settingsFactory.getURL('base_images_logo');
        $scope.base_images_signature = settingsFactory.getURL('base_images_signature');

        $scope.base_courses_thumbnail = settingsFactory.getURL('base_courses_thumbnail');

        $scope.base_site_url = settingsFactory.getConstant('BASE_SITE_URL');

        $scope.groupsExampleFileUpload = [];

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        $scope.level = [
            { "label": 'Beginner', "name": "ระดับต้น (Beginner)" },
            { "label": 'Intermediate', "name": "ระดับกลาง (Intermediate)" },
            { "label": 'Advance', "name": "ระดับสูง (Advance)" }
        ];

        $scope.activity_types = [
            { "id": 15, "name": "Education class/seminar/workshop" },
            { "id": 16, "name": "Marketing event" },
            { "id": 17, "name": "List comp event" },
            { "id": 18, "name": "Research sharing/seminar/symposium" },
            { "id": 19, "name": "Speech" },
            { "id": 157, "name": "List comp event(oppday)" },
            { "id": 161, "name": "Professional examination" },
            { "id": 162, "name": "Professional education" },
            { "id": 200, "name": "Internal event/meeting" },
            { "id": 201, "name": "Others" }
        ];
        $scope.activity_focuses = [
            { "id": 20, "name": "Fundamental investment (beginner)" },
            { "id": 21, "name": "Fundamental investment (intermediate)" },
            { "id": 22, "name": "Fundamental investment (advance)" },
            { "id": 23, "name": "Technical investment (beginner)" },
            { "id": 24, "name": "Technical investment (intermediate)" },
            { "id": 25, "name": "Technical investment (advance)" },
            { "id": 26, "name": "Financial performance" },
            { "id": 27, "name": "Financial planning" },
            { "id": 28, "name": "Sustainability" },
            { "id": 29, "name": "General economic" },
            { "id": 30, "name": "Investment research" },
            { "id": 31, "name": "Academic research" },
            { "id": 32, "name": "International conference" },
            { "id": 33, "name": "Many aspects" },
            { "id": 203, "name": "Listed Comp - Accounting and Finance" },
            { "id": 204, "name": "Listed Comp - Investor Relations" },
            { "id": 205, "name": "Listed Comp - Company Secretary Development" },
            { "id": 206, "name": "Listed Comp - Corporate Governance" },
            { "id": 207, "name": "Listed Comp - Executive Development" },
            { "id": 208, "name": "Listed Comp - Regulation and Compliance" },
            { "id": 209, "name": "Listed Comp - Others" }
        ];
        $scope.activity_details = [
            { "id": 154, "name": "Simulation" },
            { "id": 155, "name": "Contest" },
            { "id": 156, "name": "General" }
        ];

        $scope.courses_data.activity_type = 15;
        $scope.courses_data.activity_focus = 20;
        $scope.courses_data.activity_detail = 154;

        $scope.languageOptions = {
            "certLang": "th"
        };

        $scope.certificateLanguages = [
            { "value": "th", "title": "ตัวอย่างภาษาไทย (TH)" },
            { "value": "en", "title": "ตัวอย่างภาษาอังกฤษ (EN)" },
        ];

        $scope._DefaultOptions = {
            "canCustomizeCourse": false
        };

        $scope.hint = {
            'level_public': 'เปิดเมื่อต้องการแสดงให้สมาชิกในกลุ่มที่ตั้งค่าไว้เห็นหลักสูตรนี้ทั้งหมด หรือ ปิดเมื่อต้องการใช้งานเฉพาะ Upload member',
            'public': 'เปิดเมื่อต้องการให้เข้าถึงหลักสูตร หรือ ปิดเมื่อไม่ต้องการให้เข้าถึงหลักสูตร'
        };

        $scope.filters_members_pre_approved = {};
        $scope.filters_members = {};

        $scope.state = [
            { "value": 'live', "title": "Live" },
            { "value": 'vod', "title": "VOD" }
        ];

        $timeout(function() {
            $('#start_datetime, #end_datetime , #latest_end_datetime').datetimepicker({
                prevText: '<i class="fa fa-angle-left"></i>',
                nextText: '<i class="fa fa-angle-right"></i>',
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                controlType: 'select',
                oneLine: true,
                timeInput: true,
                stepMinute: 5,

            });
        }, 2000);
        ///

        var set_pagination = function (pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function (resp) {
            $scope.courses = resp.data;
            for (var i = 0; i < $scope.courses.length; i++) {
                var newCoursesModifyDatetime = new Date($scope.courses[i].modify_datetime).toISOString();
                $scope.courses[i].modify_datetime = $filter('date')(newCoursesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);
        };

        var courses_query = function (page, per_page, search) {
            var query_string = "&page=" + page + "&per_page=" + per_page + "&order_by=" + $scope.sorting_order + "&order_direction=" + $scope.sorting_direction+"&search="+search;
            var query = coursesFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleNotSkip = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.not_skip == 1) { theCourses.not_skip = 0; } else { theCourses.not_skip = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleDownloadCertificate = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.download_certificate == 1) { theCourses.download_certificate = 0; } else { theCourses.download_certificate = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleNotSeek = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.not_seek == 1) { theCourses.not_seek = 0; } else { theCourses.not_seek = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleLevelPublic = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.level_public == 1) { theCourses.level_public = 0; } else { theCourses.level_public = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleStatus = function (theCourses, forceUpdate) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.status == 1) { theCourses.status = 0; } else { theCourses.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                if (forceUpdate === true) { theCourses._mode = 'list'; }
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theCourses) {
            if (theCourses.status == 1) { theCourses.status = 0; } else { theCourses.status = 1; }
            coursesFactory.updateStatus({'id': theCourses.id, 'status': theCourses.status})
                .success(function(data) {
                    if (data.is_error == false) {
                        notification("success",data.message);
                    } else {
                        notification("error",data.message);
                    }
                })
                .error(function(data) {
                    if (data.message !== undefined) {
                        // notification("error", data.message);
                        notification("error", settingsFactory.getConstant('server_error'));
                    } else {
                        notification("error", settingsFactory.getConstant('server_error'));
                    }
                });
        };

        $scope.toggleFree = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.free == 1) { theCourses.free = 0; } else { theCourses.free = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleLatest = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.latest == 1) { theCourses.latest = 0; } else { theCourses.latest = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleRandomQuiz = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.random_quiz == 1) { theCourses.random_quiz = 0; } else { theCourses.random_quiz = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleRecommended = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.recommended == 1) { theCourses.recommended = 0; } else { theCourses.recommended = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleCertificatesShowLogo = function(theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.certificates_show_logo == 1) { theCourses.certificates_show_logo = 0; } else { theCourses.certificates_show_logo = 1; }
            // if ($scope.mode == "Edit") {
            //     coursesFactory.update(theCourses)
            //         .success(function(data) {
            //             if(data.is_error == false){
            //                 notification("success",data.message);
            //             }
            //             if(data.is_error == true){
            //                 notification("error",data.message);
            //             }
            //         })
            //         .error(function() {
            //             notification("error", settingsFactory.getConstant('server_error'));
            //         });
            // }
        };

        $scope.toggleCertificatesShowSignature = function(theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.certificates_show_signature == 1) { theCourses.certificates_show_signature = 0; } else { theCourses.certificates_show_signature = 1; }
            // if ($scope.mode == "Edit") {
            //     coursesFactory.update(theCourses)
            //         .success(function(data) {
            //             if(data.is_error == false){
            //                 notification("success",data.message);
            //             }
            //             if(data.is_error == true){
            //                 notification("error",data.message);
            //             }
            //         })
            //         .error(function() {
            //             notification("error", settingsFactory.getConstant('server_error'));
            //         });
            // }
        };

        $scope.toggleCertificatesShowFooterText = function(theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.certificates_show_footer_text == 1) { theCourses.certificates_show_footer_text = 0; } else { theCourses.certificates_show_footer_text = 1; }
            // if ($scope.mode == "Edit") {
            //     coursesFactory.update(theCourses)
            //         .success(function(data) {
            //             if(data.is_error == false){
            //                 notification("success",data.message);
            //             }
            //             if(data.is_error == true){
            //                 notification("error",data.message);
            //             }
            //         })
            //         .error(function() {
            //             notification("error", settingsFactory.getConstant('server_error'));
            //         });
            // }
        };

        $scope.toggleIsDiscussion = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.is_discussion == 1) { theCourses.is_discussion = 0; } else { theCourses.is_discussion = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.toggleIsFilter = function (theCourses) {
            theCourses.admin_id = $scope.admin.id;
            if (theCourses.is_filter == 1) { theCourses.is_filter = 0; } else { theCourses.is_filter = 1; }
            if ($scope.mode == "Edit") {
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        }

        $scope.GenerateStructure = function (theCourses) {
            $scope.courses_data.structure = "";
            for (var i = 0; i < theCourses.topics.length; i++) {
                $scope.courses_data.structure += '<div class="col-md-12 structure-line">';
                $scope.courses_data.structure += '<h4 class="col-md-4">' + theCourses.topics[i].title + '</h4>';
                $scope.courses_data.structure += '<div class="col-md-8">';
                for (var x = 0; x < theCourses.topics[i].parent.length; x++) {
                    $scope.courses_data.structure += '<h4 class="topics col-md-12"><i class="fa fa-circle"></i>'+ '&nbsp;' + theCourses.topics[i].parent[x].title + '</h4>';
                }
                $scope.courses_data.structure += '</div>';
                $scope.courses_data.structure += '<div class="col-md-12 dotted"></div>';
                $scope.courses_data.structure += '</div>';
            }
        };

        $scope.getCertificates = function() {
            certificatesFactory.all($httpParamSerializer($scope.certificates_options)).success(function (data) {
                $scope.certificates = data;

                // $timeout(function() {
                //     $scope.$watch('', function (new_value, old_value,x ,y) {
                //         // console.log($scope.certificates);
                //         console.log(x);
                //         console.log(y);
                //         console.log(new_value);
                //         console.log(old_value);
                //         console.log($scope.courses_data.certificates_id);

                //         console.log(!angular.equals(new_value, old_value));
                //         return false

                //         if ($scope.certificates !== undefined && new_value !== undefined) {
                //             $scope.selected_certificates = _.find($scope.certificates, ['id', new_value]);

                //             $scope.previewCertificationByCourse($scope.courses_data);

                //             // if ($scope.selected_certificates.id != $scope.courses_data.certificates_id) {

                //             // }

                //             // if ($scope.selected_certificates !== undefined) {
                //             //     if ($scope.selected_certificates.number_of_logo >= 1) {
                //             //         $scope.courses_data.certificates_show_logo = 1;
                //             //     } else {
                //             //         $scope.courses_data.certificates_show_logo = 0;
                //             //     }

                //             //     if ($scope.selected_certificates.number_of_signature >= 1) {
                //             //         $scope.courses_data.certificates_show_signature = 1;
                //             //     } else {
                //             //         $scope.courses_data.certificates_show_signature = 0;
                //             //     }

                //             //     if ($scope.selected_certificates.footer_text != "" || $scope.selected_certificates.footer_text_en != "") {
                //             //         $scope.courses_data.certificates_show_footer_text = 1;
                //             //     } else {
                //             //         $scope.courses_data.certificates_show_footer_text = 0;
                //             //     }
                //             // }
                //         }
                //     });
                // }, 500);
            });
        };

        // $scope.getCertificates();

        $timeout(function() {
            $scope.$watch('courses_data.certificates_id', function (new_value, old_value) {
                // console.log($scope.certificates);
                // console.log("old_value: "+old_value);
                // console.log("new_value: "+new_value);
                if ($scope.certificates !== undefined) {
                    $timeout(function() {
                        $scope.selected_certificates = _.find($scope.certificates, ['id', new_value]);
                        // console.log($scope.selected_certificates);

                        if ($scope.selected_certificates && ($scope.selected_certificates.is_control_logo == 1 || $scope.selected_certificates.is_upload_logo == 1 || $scope.selected_certificates.is_control_signature == 1 || $scope.selected_certificates.is_upload_signature == 1 || $scope.selected_certificates.is_control_footer == 1 || $scope.selected_certificates.is_edit_footer == 1)) {
                            $scope._DefaultOptions.canCustomizeCourse = true;
                        } else {
                            $scope._DefaultOptions.canCustomizeCourse = false;
                            $scope.courses_data.certificates_used_type = 'default';
                        }
                    }, 100);

                    // if ($scope.selected_certificates.id != $scope.courses_data.certificates_id) {

                    // }

                    // if ($scope.selected_certificates !== undefined) {
                    //     if ($scope.selected_certificates.number_of_logo >= 1) {
                    //         $scope.courses_data.certificates_show_logo = 1;
                    //     } else {
                    //         $scope.courses_data.certificates_show_logo = 0;
                    //     }

                    //     if ($scope.selected_certificates.number_of_signature >= 1) {
                    //         $scope.courses_data.certificates_show_signature = 1;
                    //     } else {
                    //         $scope.courses_data.certificates_show_signature = 0;
                    //     }

                    //     if ($scope.selected_certificates.footer_text != "" || $scope.selected_certificates.footer_text_en != "") {
                    //         $scope.courses_data.certificates_show_footer_text = 1;
                    //     } else {
                    //         $scope.courses_data.certificates_show_footer_text = 0;
                    //     }
                    // }
                }
            });
        }, 500);

        $scope.$watch('current_page', function (new_page, old_page) {
            if (new_page != old_page) {
                courses_query(new_page, $scope.per_page, $scope.search);
            }
        });

        $scope.$watch('courses_data.download_certificate', function (new_value, old_value) {
            if (new_value != old_value) {
                if (new_value == 1) {
                    $scope.getCertificates();
                    pluginsService.inputSelect();
                }
            }
        });

        $scope.$watch('courses_data.course2sub_group', function (new_value, old_value) {
            $scope.level_groups = _.filter($scope.level_groups, function(o) {
                return _.find($scope.courses_data.course2sub_group, function(ele) { return ele == o.sub_groups_id; }) !== undefined;
            });

            if (new_value !== undefined) {
                if (angular.isArray(old_value)) {
                    old_value = _.sortBy(old_value, [function(o) { return o; }])
                }
                new_value = _.sortBy(new_value, [function(o) { return o; }])
                if (!angular.equals(new_value, old_value) && angular.isArray(new_value) && new_value.length > 0) {
                    level_groupsFactory.allBySubGroups($httpParamSerializer({"sub_groups[]": new_value})).success(function(data) {
                        for (var i = 0; i < data.owner.length; i++) {
                            if (_.find($scope.level_groups, ['id', data.owner[i].id]) === undefined) {
                                $scope.level_groups.push({id: data.owner[i].id, sub_groups_id: data.owner[i].sub_groups_id, title: data.owner[i].title});
                            }
                        }
                        for (var i = 0; i < data.access.length; i++) {
                            if (_.find($scope.level_groups, ['id', data.access[i].id]) === undefined) {
                                $scope.level_groups.push({id: data.access[i].id, sub_groups_id: data.access[i].sub_groups_id, title: data.access[i].title});
                            }
                        }
                    }).error(function(data) {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
                }
            }
        });

        $scope.$watch('images_data.picture', function (new_files, old_files) {
            if (new_files != old_files && $scope.images_data.picture !== undefined && $scope.images_data.picture !== null && $scope.images_data.picture !== '') {
                $scope.images_data.groups_id = $scope.selected_certificates.groups_id;
                imagesFactory.create($scope.images_data)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);
                        } else {
                            notification("error", data.message);
                        }

                        $scope.reloadImages($scope.images_data);
                        $scope.images_data = {};

                    })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                        $scope.reloadImages($scope.images_data);
                        $scope.images_data = {};
                    });
            }
        });

        // $scope.$watch('courses_data.certificates_id', function () {
        //     $scope.changeCourses();
        // });

        $scope.$watch('uploadMembersOptions.uploadToGroup', function (new_value, old_value) {
            if (old_value != new_value) {
                if (new_value !== null && new_value !== undefined) {
                    groupsFactory.get({id: new_value}).success(function (data) {
                        $scope.selected_group_upload = data;
                    });
                }

                $timeout(function() {
                    angular.element('select.uploadToGroup').trigger('change');
                }, 10);
            }
        });

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        $scope.sortOrder = function(theAdminsGroups) {
            coursesFactory.sort(theAdminsGroups)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    courses_query($scope.current_page, $scope.per_page, $scope.search);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                    $scope.enableSortable();
                });
        };

        $scope.sortableOptions = {
            stop: function(e, ui) {
                var $sorted = ui.item;

                var $prev = $sorted.prev();
                var $next = $sorted.next();

                var dataSort = {
                    id: $sorted.data('id')
                };

                if ($prev.length > 0) {
                    dataSort.type = 'moveAfter';
                    dataSort.positionEntityId = $prev.data('id');
                } else if ($next.length > 0) {
                    dataSort.type = 'moveBefore';
                    dataSort.positionEntityId = $next.data('id');
                } else {
                    notification("error"," Something wrong!");
                }

                coursesFactory.sort(dataSort).success(function() {
                    notification("success", "The courses has been sortable.");
                    courses_query($scope.current_page, $scope.per_page, $scope.search);
                });
            }
        };

        $scope.coursesRelatedSortableOptions = {
            handle: ".dd-handle",
            stop: function(e, ui) {
                var $sorted = ui.item;

                $scope.courses_data.course2related = $scope.courses_data.related.map(function(elem, index) {
                    return elem.id;
                });
            }
        };

        $scope.sort_by = function (newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction == 'desc') ? 'asc' : 'desc';
            }
            $scope.sorting_order = newSortingOrder;
            courses_query($scope.page, $scope.per_page, $scope.search);
            $('th i').each(function () {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.' + newSortingOrder + ' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        courses_query($scope.page, $scope.per_page, $scope.search);

        var courses_members_pre_approved_query = function() {
            var filters = "&" + $httpParamSerializer($scope.filters_members_pre_approved)
            coursesFactory.getMembersPreApproved({id:$routeParams.id}, filters).success(function(resp) {
                $scope.courses_data.members_pre_approved = resp.data;
            });
        };

        var courses_members_query = function() {
            var filters = "&" + $httpParamSerializer($scope.filters_members)
            coursesFactory.getMembers({id:$routeParams.id}, filters).success(function(resp) {
                $scope.courses_data.members = resp.data;
            });
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
        })

        sub_groupsFactory.all().success(function(data) {
            $scope.sub_groups = data;
        });

        // level_groupsFactory.all().success(function(data) {
        //     for (var i = 0; i < data.owner.length; i++) {
        //         $scope.level_groups.push({id: data.owner[i].id, title: data.owner[i].title});
        //     }
        //     for (var i = 0; i < data.access.length; i++) {
        //         $scope.level_groups.push({id: data.access[i].id, title: data.access[i].title});
        //     }
        // });

        categoriesFactory.all().success(function (data) {
            $scope.categories = data;
        })

        instructorsFactory.all().success(function (data) {
            $scope.instructors = data;
        })

        $scope.getCourse = function() {
            if (!angular.isUndefined($routeParams.id)) {
                coursesFactory.get({id: $routeParams.id}).success(function (data) {
                    $scope.courses_data = data;
                    $scope.mode = "Edit";

                    $scope.courses_data.dir_name = "courses/C" + $scope.courses_data.id;

                    $scope.courses_data.course2sub_group = new Array();
                    if (!angular.isUndefined($scope.courses_data.sub_groups) && $scope.courses_data.sub_groups.length != 0) {
                        for (var i = 0; i < $scope.courses_data.sub_groups.length; i++) {
                            $scope.courses_data.course2sub_group.push($scope.courses_data.sub_groups[i].id);
                        }
                    }

                    $scope.courses_data.course2level_group = new Array();
                    if (!angular.isUndefined($scope.courses_data.level_groups) && $scope.courses_data.level_groups.length != 0) {
                        for (var i = 0; i < $scope.courses_data.level_groups.length; i++) {
                            $scope.courses_data.course2level_group.push($scope.courses_data.level_groups[i].id);
                        }
                    }

                    $scope.courses_data.course2group = new Array();
                    if (!angular.isUndefined($scope.courses_data.groups) && $scope.courses_data.groups.length != 0) {
                        for (var i = 0; i < $scope.courses_data.groups.length; i++) {
                            $scope.courses_data.course2group.push($scope.courses_data.groups[i].id);
                        }
                    }

                    $scope.courses_data.course2category = new Array();
                    if (!angular.isUndefined($scope.courses_data.categories) && $scope.courses_data.categories.length != 0) {
                        // console.log($scope.courses_data.categories);
                        for (var i = 0; i < $scope.courses_data.categories.length; i++) {
                            $scope.courses_data.course2category.push($scope.courses_data.categories[i].id);
                            // console.log($scope.courses_data.course2category);
                        }
                    }

                    $scope.courses_data.course2instructor = new Array();
                    if (!angular.isUndefined($scope.courses_data.instructors) && $scope.courses_data.instructors.length != 0) {
                        for (var i = 0; i < $scope.courses_data.instructors.length; i++) {
                            $scope.courses_data.course2instructor.push($scope.courses_data.instructors[i].id);
                        }
                    }

                    $scope.courses_data.course2related = new Array();
                    if (!angular.isUndefined($scope.courses_data.related) && $scope.courses_data.related.length != 0) {
                        for (var i = 0; i < $scope.courses_data.related.length; i++) {
                            $scope.courses_data.course2related.push($scope.courses_data.related[i].id);
                        }
                    }

                    $scope.courses_data.course2member = new Array();
                    if (!angular.isUndefined($scope.courses_data.members) && $scope.courses_data.members.length != 0) {
                        for (var i = 0; i < $scope.courses_data.members.length; i++) {
                            $scope.courses_data.course2member.push($scope.courses_data.members[i].id);
                        }
                    }

                    coursesFactory.allInGroups($scope.courses_data).success(function (data) {
                        $scope.courses_all = data;
                        // console.log($scope.courses_all);
                    });

                    coursesFactory.overview($scope.courses_data).success(function (data) {
                        $scope.overview = data;
                    });

                    $scope.certificates_options.courses_id = $scope.courses_data.id;
                    $scope.getCertificates();

                    if ($scope.admin.super_users == 1) {
                        $scope.groupsExampleFileUpload.push($scope.admin.groups);
                    } else {
                        $scope.groupsExampleFileUpload = _.intersectionBy($scope.courses_data.groups, $scope.admin.admins_groups.groups, 'id');
                    }

                    $timeout(function() {
                        _.map($scope.groupsExampleFileUpload, function(group) {
                            switch (group.field_approval) {
                                case 'email'         : group.field_approval_text = "อีเมล์"; break;
                                case 'full_name'     : group.field_approval_text = "ชื่อ - นามสกุล"; break;
                                case 'id_card'       : group.field_approval_text = "เลขบัตรประจำตัวประชาชน"; break;
                                case 'license_id'    : group.field_approval_text = "เลขที่ใบอนุญาต"; break;
                                case 'occupation_id' : group.field_approval_text = group.meaning_of_occupation_id; break;
                            }
                        });

                        if ($scope.groupsExampleFileUpload.length == 1) {
                            $scope.uploadMembersOptions.uploadToGroup = $scope.groupsExampleFileUpload[0].id;
                        }
                    }, 500);

                    // console.log($scope.groupsExampleFileUpload);

                    $timeout(function() {
                        courses_members_pre_approved_query();
                        courses_members_query();
                    }, 500);
                });
            } else {
                coursesFactory.allInGroups().success(function (data) {
                    $scope.courses_all = data;
                    // console.log($scope.courses_all);
                });
                // coursesFactory.all().success(function (data) {
                //     $scope.courses_all = data;
                // });
            }
        };

        $scope.getCourse();

        $scope.addRelatedCourse = function(selected_course) {
            if (selected_course !== null) {
                var indexOfSelected;

                $scope.courses_data.related.unshift(selected_course);
                $scope.courses_data.course2related.unshift(selected_course.id);

                indexOfSelected = _.findIndex($scope.courses_all, selected_course);
                $scope.courses_all.splice(indexOfSelected, 1);

                $timeout(function() {
                    angular.element('select[name=course_id]').val('').trigger('change');
                }, 10);
            }
        };

        $scope.removeRelatedCourse = function(theCourses) {
            _.remove($scope.courses_data.related, function(n) {
                return n.id == theCourses.id;
            });

            _.remove($scope.courses_data.course2related, function(n) {
                return n == theCourses.id;
            });

            $scope.courses_all.push(theCourses);
            $scope.courses_all = _.sortBy($scope.courses_all, ['order']);

            $timeout(function() {
                angular.element('select[name=course_id]').val('').trigger('change');
            }, 100);
        };

        $scope.changeFilter = function() {
            courses_query($scope.page, $scope.per_page, $scope.search);
        };

        $scope.getImagesLogo = function() {
            imagesFactory.all($httpParamSerializer($scope.images_logos_options)).success(function (data) {
                $scope.images_logos = data;
            });
        };
        $scope.getImagesLogo();

        $scope.getImagesSignature = function() {
            imagesFactory.all($httpParamSerializer($scope.images_signatures_options)).success(function (data) {
                $scope.images_signatures = data;
            });
        };
        $scope.getImagesSignature();

        $scope.reloadImages = function(images_data) {
            switch (images_data.type) {
                case 1: $scope.getImagesLogo(); break;
                case 2: $scope.getImagesSignature(); break;
                default:
                    $scope.getImagesLogo();
                    $scope.getImagesSignature();
                break;
            }
        };

        $scope.setImagesField = function(field) {
            $scope.imagesField = field;
        };

        $scope.setLogo = function(selectedPicture) {
            $scope.courses_data[$scope.imagesField] = selectedPicture;
            angular.element('#modalCertLogo').modal('hide');
        };

        $scope.setSignature = function(selectedPicture) {
            $scope.courses_data[$scope.imagesField] = selectedPicture;
            angular.element('#modalCertSignature').modal('hide');
        };

        $scope.submitCourses = function (theCourses, nextAction) {
            var originalCourse2related = {};
            var tempCourse2related = {};

            functionsFactory.clearError(angular.element('.courses-frm'));

            originalCourse2related = theCourses.course2related;

            // console.log(theCourses.related);
            theCourses.course2related.map(function(elem, index) {
                tempCourse2related[elem] = {"order": (index+1)};
            });

            theCourses.course2related = tempCourse2related;

            // return false;

            theCourses.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                // console.log(theCourses);
                coursesFactory.update(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('courses'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                        }
                        theCourses.course2related = originalCourse2related;
                    })
                    .error(function (data) {
                        // console.log(data);
                        functionsFactory.handleError(data, angular.element('.courses-frm'));
                        // if (data.message !== undefined) {
                        //     notification("error", data.message);
                        // } else {
                        //     notification("error", settingsFactory.getConstant('server_error'));
                        // }
                        theCourses.course2related = originalCourse2related;
                    });
            } else {
                coursesFactory.create(theCourses)
                    .success(function (data) {
                        if (data.is_error == false) {
                            notification("success", data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('courses/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('courses'); break;
                            }
                        }
                        if (data.is_error == true) {
                            notification("error", data.message);
                            theCourses.course2related = originalCourse2related;
                        }
                    })
                    .error(function (data) {
                        // console.log(data);
                        functionsFactory.handleError(data, angular.element('.courses-frm'));
                        // if (data.message !== undefined) {
                        //     notification("error", data.message);
                        // } else {
                        //     notification("error", settingsFactory.getConstant('server_error'));
                        // }
                        theCourses.course2related = originalCourse2related;
                    });
            }
        }

        $scope.deleteCourses = function (theCourses) {
            var id = theCourses.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if (alert == true) {
                coursesFactory.delete(theCourses).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }
                })
                    .error(function () {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.deleteImages = function(theImages) {
            var id = theImages.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                imagesFactory.delete(theImages).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);

                        if ($scope.courses_data[$scope.imagesField] == theImages.picture) {
                            $scope.courses_data[$scope.imagesField] =  null;
                        }

                        $scope.reloadImages(theImages);
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                        $scope.reloadImages(theImages);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.reloadImages(theImages);
                });
            }
        };

        $scope.detachMembers = function (theMembers) {
            var theCourses = $scope.courses_data;
            var dataDetach = {
                'members': []
            };
            dataDetach.members.push(theMembers);
            var alert = confirm("Are you sure to detach #" + theMembers.id + " ?");
            if (alert == true) {
                coursesFactory.detachMembers(theCourses, dataDetach).success(function (data) {
                    if (data.is_error == false) {
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if (data.is_error == true) {
                        notification("error", data.message);
                    }

                    $scope.getCourse();
                })
                .error(function () {
                    notification("error", settingsFactory.getConstant('server_error'));
                    $scope.getCourse();
                });
            }
        }

        $scope.ReviewVODOverview = function (theTopics) {
            topicsFactory.get({id: theTopics.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url_cut,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $scope.Review = function (theCourses) {
            coursesFactory.get({id: theCourses.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.review_streaming_url,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $scope.ReviewStreaming = function (theCourses) {
            coursesFactory.get({id: theCourses.id}).success(function (data) {
                var playerInstance = jwplayer("player");
                playerInstance.setup({
                    file: data.streaming_url,
                    aspectratio: "16:9",
                    width: "100%",
                    autostart: "true"
                });
                $('#modal-basic').modal('show');
            });
        }

        $('#modal-basic').on('hidden.bs.modal', function () {
            $timeout(function() {
                var playerInstance = jwplayer("player");
                playerInstance.stop();
            }, 500);
        });

        // Delete Members Pre-Approved
        $scope.deleteMembersPreApproved = function(theMembersPreApproved) {
            var id = theMembersPreApproved.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                members_pre_approvedFactory.delete(theMembersPreApproved).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $scope.getCourse();
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                });
            }
        }

        // Approve Member
        $scope.approveMember = function($event, theMembers) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.approve(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    $scope.getCourse();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                    $scope.getCourse();
                });
        };

        // Reject Member
        $scope.rejectMember = function($event, theMembers) {
            angular.element($event.currentTarget).button('loading');
            membersFactory.reject(theMembers)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }
                    $scope.getCourse();
                })
                .error(function() {
                    notification("error"," No Access-Control-Allow-Origin");
                    $scope.getCourse();
                });
        };

        // Get Result Upload
        $scope.checkResultsUpload = function(results, type) {
            var rejectedMembers = [];
            var updatedMembers = [];
            var uploadedMembers = [];

            if (!angular.isUndefined(results.rejected_members)) {
                rejectedMembers = results.rejected_members;
            }

            if (!angular.isUndefined(results.updated_members)) {
                updatedMembers = results.updated_members;
            }

            if (!angular.isUndefined(results.uploaded_members)) {
                uploadedMembers = results.uploaded_members;
            }

            $timeout(function() {
                if (type === "pre-approved") {
                    // console.log(uploadedMembers);
                    // console.log(rejectedMembers);
                    $scope.preApprovedMembersUploaded = uploadedMembers;
                    $scope.preApprovedMembersUpdated = updatedMembers;
                    $scope.preApprovedMembersRejected = rejectedMembers;
                } else {
                    $scope.membersUploaded = uploadedMembers;
                    $scope.membersUpdated = updatedMembers;
                    $scope.membersRejected = rejectedMembers;
                }
            }, 100);
        };

        // Upload Pre-Approved Members
        $scope.uploadPreApprovedMembers = function(theCourses) {
            var fileMembers;
            var $btnPreApprovedMembers = angular.element('#btn-upload-pre-approved-members');

            $btnPreApprovedMembers.button('loading');

            if ($scope.uploadMembersOptions.uploadToGroup === undefined || $scope.uploadMembersOptions.uploadToGroup === null) {
                notification("error", "กรุณาเลือกลุ่ม (Group) ที่จะอัพโหลด");
                $btnPreApprovedMembers.button('reset');
                return false;
            }

            fileMembers = angular.element('#preApprovedFile')[0].files[0];
            coursesFactory.uploadPreApprovedMembers(theCourses, fileMembers, $scope.uploadMembersOptions.uploadToGroup)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    $scope.getCourse();
                    $scope.checkResultsUpload(data, 'pre-approved');
                    $btnPreApprovedMembers.button('reset');
                    angular.element('#removePreApprovedFile').trigger('click');
                })
                .error(function(data) {
                    if (!angular.isUndefined(data.file)) {
                        notification("error", data.file);
                    } else {
                        notification("error", data.message);
                        angular.element('#removePreApprovedFile').trigger('click');
                    }

                    $scope.getCourse();
                    $scope.checkResultsUpload(data, 'pre-approved');
                    $btnPreApprovedMembers.button('reset');

                });
        };

        // Upload Members
        $scope.uploadMembers = function(theCourses) {
            var fileMembers;
            var $btnMembers = angular.element('#btn-upload-members');

            $btnMembers.button('loading');

            if ($scope.uploadMembersOptions.uploadToGroup === undefined || $scope.uploadMembersOptions.uploadToGroup === null) {
                notification("error", "กรุณาเลือกลุ่ม (Group) ที่จะอัพโหลด");
                $btnMembers.button('reset');
                return false;
            }

            fileMembers = angular.element('#membersFile')[0].files[0];
            coursesFactory.uploadMembers(theCourses, fileMembers, $scope.uploadMembersOptions.uploadToGroup)
                .success(function(data) {
                    if(data.is_error == false){
                        notification("success", data.message);
                        // $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error", data.message);
                    }

                    $scope.getCourse();
                    $scope.checkResultsUpload(data);
                    $btnMembers.button('reset');
                    angular.element('#removeMembersFile').trigger('click');
                })
                .error(function(data) {
                    if (!angular.isUndefined(data.file)) {
                        notification("error", data.file);
                    } else {
                        notification("error", data.message);
                        angular.element('#removeMembersFile').trigger('click');
                    }

                    $scope.getCourse();
                    $scope.checkResultsUpload(data);
                    $btnMembers.button('reset');

                });
        };

        $scope.previewCertificationByCourse = function(theCourses) {
            $timeout(function() {
                if (theCourses.certificates_id !== undefined && theCourses.certificates_id !== null) {
                    angular.element('#box-preview-certificates').css('opacity', '0');
                    functionsFactory.notification("info", "Loading preview...");

                    theCourses.lang = $scope.languageOptions.certLang;

                    certificatesFactory.createPreviewByCourse(theCourses).success(function(data) {
                        theCourses.old_file = data.filePreview;
                        angular.element('#box-preview-certificates').attr('src', data.urlPreview);
                        $timeout(function() {
                            angular.element('#box-preview-certificates').css('opacity', '1');
                        }, 1000);
                    }).error(function(data) {
                        $timeout(function() {
                            functionsFactory.notification("error", "The data used in the preview is incomplete.");
                        }, 1000);
                        theCourses.old_file = null;
                        // console.log('error');
                        // console.log(data);
                    });
                }
            }, 500);
        };

        $scope.downloadExampleFileUpload = function(groupKey, model) {
            window.location.href = settingsFactory.get(model) + '/' + groupKey + '/example/file';
        };

        // Reload on certificate id changed
        angular.element('body').on('change', '#certificates_id', function(event) {
            $scope.previewCertificationByCourse($scope.courses_data);
        });

        $timeout(function() {
            pluginsService.popoverWithOptions();
        }, 1000);

        $scope.changeFilterMembersPreApproved = function() {
            courses_members_pre_approved_query();
        };

        $scope.changeFilterMembers = function() {
            courses_members_query();
        };

        //notification
        var notification = function (status, alert) {
            if (status == "success") {
                var n = noty({
                    text: '<div class="alert alert-success"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else {
                var n = noty({
                    text: '<div class="alert alert-danger"><p><strong> ' + alert + ' </strong></p></div>',
                    layout: 'topRight',
                    theme: 'made',
                    maxVisible: 10,
                    animation: {
                        open: 'animated bounceInRight',
                        close: 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            }
        }

    }]);
