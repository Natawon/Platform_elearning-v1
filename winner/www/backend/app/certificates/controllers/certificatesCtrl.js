'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('certificatesCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', '$httpParamSerializer', 'certificatesFactory', 'groupsFactory', 'admins_groupsFactory', 'imagesFactory', 'pluginsService', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, $httpParamSerializer, certificatesFactory, groupsFactory, admins_groupsFactory, imagesFactory, pluginsService, functionsFactory, settingsFactory) {

        $scope.certificates = {};
        $scope.certificates_data = {
            "body_text_1": "วุฒิบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า",
            "body_text_1_en": "This certificate is provided to show that.",
            "body_text_2": "ผ่านการเรียน E-Learning ครบถ้วนตามหลักสูตร",
            "body_text_2_en": "Through e-Learning course.",
            "footer_text": "สอบถามข้อมูลเพิ่มเติมกรุณาติดต่อ Contact Center โทรศัพท์ 02-000-0000 หรือ e-mail: info@domain.com เว็บไซต์ https://www.mydomain.com",
            "footer_text_en": "For more information, please contact 02-000-0000 or E-Mail: info@domain.com. or Website: https://www.mydomain.com",
            "number_of_logo": 1,
            "logo_align": "L",
            "number_of_signature": 1,
            "signature_align": "C",
            "background_color": "#ffffff",
            "text_color": "#000000",
            "is_border": 1,
            "border_color": "#ffa400",
            "border_style": "radius",
        };
        $scope.selected_groups = {};
        $scope.courses = [];

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

        $scope.mode = "Create";

        $scope.defaultNumberOfLogos = [
            { "value": 0, "title": "ไม่มี" },
            { "value": 1, "title": "1" },
            { "value": 2, "title": "2" },
            { "value": 3, "title": "3" },
        ];

        $scope.defaultNumberOfSignatures = [
            { "value": 0, "title": "ไม่มี" },
            { "value": 1, "title": "1" },
            { "value": 2, "title": "2" },
            { "value": 3, "title": "3" },
        ];

        $scope.defaultColors = [
            { "title": "-- เลือกสี --", "css_class": "t0", "hex_color": "#ffa400" },
            { "title": "t1", "css_class": "t1", "hex_color": "#ffe76d" },
            { "title": "t2", "css_class": "t2", "hex_color": "#999999" },
            { "title": "t3", "css_class": "t3", "hex_color": "#FF9700" },
            { "title": "t4", "css_class": "t4", "hex_color": "#7CACD2" },
            { "title": "t5", "css_class": "t5", "hex_color": "#8ecdbc" }
        ];

        $scope.languageOptions = {
            "certLang": "th"
        };

        $scope.certificateLanguages = [
            { "value": "th", "title": "ตัวอย่างภาษาไทย (TH)" },
            { "value": "en", "title": "ตัวอย่างภาษาอังกฤษ (EN)" },
        ];

        $scope._DefaultOptions = {
            "colorPicker": [
                    ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
                    ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"]
                ]
        }

        $scope.filters = {
            // "search": "",
        };

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'order';
        $scope.sorting_direction = 'asc';
        $scope.keyword = "";

        var set_pagination = function(pagination_data) {
            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;
        };

        var success_callback = function(resp) {
            $scope.certificates = resp.data;
            for(var i=0; i<$scope.certificates.length; i++) {
                var newCertificatesModifyDatetime = new Date($scope.certificates[i].modify_datetime).toISOString();
                $scope.certificates[i].modify_datetime = $filter('date')(newCertificatesModifyDatetime, 'dd MMM yyyy HH:mm:ss');
            }
            set_pagination(resp);

            $('#btnFiltersClear, #btnFiltersSubmit').button('reset');
        };

        var certificates_query = function(page, per_page) {
            var filters = $httpParamSerializer($scope.filters);
            filters = filters !== undefined ? "&"+filters : "";

            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+filters;
            var query = certificatesFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(theCertificates, forceUpdate) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.status == 1) { theCertificates.status = 0; } else { theCertificates.status = 1; }
            if ($scope.mode == "Edit" || forceUpdate === true) {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.updateStatus = function(theCertificates) {
            if (theCertificates.status == 1) { theCertificates.status = 0; } else { theCertificates.status = 1; }
            certificatesFactory.updateStatus({'id': theCertificates.id, 'status': theCertificates.status})
                .success(function(data) {
                    if (data.is_error == false) {
                        notification("success",data.message);
                    } else {
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        };

        $scope.toggleIsControlLogo = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_control_logo == 1) { theCertificates.is_control_logo = 0; } else { theCertificates.is_control_logo = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsUploadLogo = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_upload_logo == 1) { theCertificates.is_upload_logo = 0; } else { theCertificates.is_upload_logo = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsControlSignature = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_control_signature == 1) { theCertificates.is_control_signature = 0; } else { theCertificates.is_control_signature = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsUploadSignature = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_upload_signature == 1) { theCertificates.is_upload_signature = 0; } else { theCertificates.is_upload_signature = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsControlFooter = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_control_footer == 1) { theCertificates.is_control_footer = 0; } else { theCertificates.is_control_footer = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsEditFooter = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_edit_footer == 1) { theCertificates.is_edit_footer = 0; } else { theCertificates.is_edit_footer = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.toggleIsBorder = function(theCertificates) {
            theCertificates.admin_id = $scope.admin.id;
            if (theCertificates.is_border == 1) { theCertificates.is_border = 0; } else { theCertificates.is_border = 1; }
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function() {
                        notification("error", settingsFactory.getConstant('server_error'));
                    });
            }
        };

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                certificates_query(new_page, $scope.per_page);
            }
        });

        // $scope.$watch('certificates_data.groups_id', function(new_value, old_value) {
        //     if (new_value != old_value) {
        //         $timeout(function() {
        //             angular.element('select[name=groups_id]').trigger('change');
        //         }, 10);
        //     }
        // });

        // $scope.$watch('certificates_data.logo_align', function(new_value, old_value) {
        //     if (new_value != old_value) {
        //         $timeout(function() {
        //             angular.element('select[name=logo_align]').trigger('change');
        //         }, 10);
        //     }
        // });

        $scope.$watch('certificates_data.groups_id', function () {
            $scope.changeCourses();
        });

        $scope.$watch('certificates_data.number_of_logo', function (new_value, old_value) {
            if (new_value != old_value) {
                switch ($scope.certificates_data.number_of_logo) {
                    case 1:
                        $scope.certificates_data.logo_align = 'L';
                    break
                    case 2:
                        $scope.certificates_data.logo_align = 'C';
                    break
                    case 3:
                        $scope.certificates_data.logo_align = 'C';
                    break
                    default:
                        $scope.certificates_data.logo_align = null;
                    break;
                }
            }
        });

        $scope.$watch('certificates_data.number_of_signature', function (new_value, old_value) {
            if (new_value != old_value) {
                switch ($scope.certificates_data.number_of_signature) {
                    case 1:
                        $scope.certificates_data.signature_align = 'C';
                    break
                    case 2:
                        $scope.certificates_data.signature_align = 'C';
                    break
                    case 3:
                        $scope.certificates_data.signature_align = 'C';
                    break
                    default:
                        $scope.certificates_data.signature_align = null;
                    break;
                }
            }
        });

        $scope.$watch('images_data.picture', function (new_files, old_files) {
            if (new_files != old_files && $scope.images_data.picture !== undefined && $scope.images_data.picture !== null && $scope.images_data.picture !== '') {
                $scope.images_data.groups_id = $scope.certificates_data.groups_id;
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

        $scope.enableSortable = function() {
            angular.element('.ui-sortable').sortable( "enable" );
        };

        $scope.disableSortable = function() {
            angular.element('.ui-sortable').sortable( "disable" );
        };

        groupsFactory.all().success(function (data) {
            $scope.groups = data;
            $scope.groups_list = data;

            $timeout(function() {
                $scope.$watch('certificates_data.groups_id', function (new_value, old_value) {
                    if ($scope.groups !== undefined) {
                        $scope.selected_groups = _.find($scope.groups, ['id', new_value]);
                    }
                });
            }, 500);

            if ($scope.groups_list.length == 1) {
                $scope.filters.groups_id = $scope.groups_list[0].id;
            }
        });

        $scope.sortOrder = function(theCertificates) {
            certificatesFactory.sort(theCertificates)
                .success(function(data) {
                    notification("success",data.message);
                    // $route.reload();
                    certificates_query($scope.current_page, $scope.per_page);
                    $scope.enableSortable();
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
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

                certificatesFactory.sort(dataSort).success(function() {
                    notification("success", "The certificates has been sortable.");
                    certificates_query($scope.current_page, $scope.per_page);
                });
            }
        };

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            certificates_query($scope.page, $scope.per_page);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        certificates_query($scope.page, $scope.per_page);

        $scope.changeFilter = function(isLoadBtnSubmit) {
            if (isLoadBtnSubmit !== false) {
                $('#btnFiltersSubmit').button('loading');
            }

            certificates_query($scope.page, $scope.per_page);
        };

        $scope.clearFilters = function () {
            $('#btnFiltersClear').button('loading');
            // angular.element('.frm-filter')[0].reset();
            $scope.filters = {};
            $timeout(function() {
                angular.element('select#filter_groups_id').trigger('change');
            }, 10);
            // $scope.changeFilter(false);
        };

        if (!angular.isUndefined($routeParams.id)) {
            certificatesFactory.get({id:$routeParams.id}).success(function(data) {
                $scope.certificates_data = data;
                $scope.mode = "Edit";
            })
        } else {
            if ($scope.admin.groups_id != null) {
                $scope.certificates_data.groups_id = $scope.admin.groups_id;
            } else {
                admins_groupsFactory.get({id:$scope.admin.admins_groups_id}).success(function(data) {
                    if (data.groups.length == 1) {
                        $scope.certificates_data.groups_id = data.groups[0].id;
                    }
                });
            }
        }

        $timeout(function() {
            pluginsService.colorPicker();
        }, 0);

        $scope.changeCourses = function () {
            $scope.courses = [];
            if (!angular.isUndefined($scope.certificates_data.groups_id) && $scope.certificates_data.groups_id !== null) {
                groupsFactory.courses({id:$scope.certificates_data.groups_id}, '').success(function(data) {
                    $scope.courses = data;
                });
            }
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
            $scope.certificates_data[$scope.imagesField] = selectedPicture;
            angular.element('#modalCertLogo').modal('hide');
        };

        $scope.setSignature = function(selectedPicture) {
            $scope.certificates_data[$scope.imagesField] = selectedPicture;
            angular.element('#modalCertSignature').modal('hide');
        };

        $scope.submitCertificates = function(theCertificates, nextAction) {

            if ($scope.selected_groups !== undefined && $scope.selected_groups.multi_lang_certificate == 1) {

            } else {
                theCertificates.body_text_1_en = null;
                theCertificates.body_text_2_en = null;
                theCertificates.footer_text_en = null;
                theCertificates.logo_1_en = null;
                theCertificates.logo_2_en = null;
                theCertificates.logo_3_en = null;
                theCertificates.signature_1_en = null;
                theCertificates.name_of_signature_1_en = null;
                theCertificates.position_of_signature_1_en = null;
                theCertificates.remark_of_signature_1_en = null;
                theCertificates.signature_2_en = null;
                theCertificates.name_of_signature_2_en = null;
                theCertificates.position_of_signature_2_en = null;
                theCertificates.remark_of_signature_2_en = null;
                theCertificates.signature_3_en = null;
                theCertificates.name_of_signature_3_en = null;
                theCertificates.position_of_signature_3_en = null;
                theCertificates.remark_of_signature_3_en = null;
            }

            if ([1,2,3].indexOf(theCertificates.number_of_logo) >= 0) {
                if (theCertificates.number_of_logo == 1) {
                    theCertificates.logo_2 = null;
                    theCertificates.logo_2_en = null;
                    theCertificates.logo_3 = null;
                    theCertificates.logo_3_en = null;
                } else if (theCertificates.number_of_logo == 2) {
                    theCertificates.logo_3 = null;
                    theCertificates.logo_3_en = null;
                }
            } else {
                theCertificates.logo_1 = null;
                theCertificates.logo_1_en = null;
                theCertificates.logo_2 = null;
                theCertificates.logo_2_en = null;
                theCertificates.logo_3 = null;
                theCertificates.logo_3_en = null;
            }

            if ([1,2,3].indexOf(theCertificates.number_of_signature) >= 0) {
                if (theCertificates.number_of_signature == 1) {
                    theCertificates.signature_2 = null;
                    theCertificates.signature_2_en = null;
                    theCertificates.signature_3 = null;
                    theCertificates.signature_3_en = null;
                } else if (theCertificates.number_of_signature == 2) {
                    theCertificates.signature_3 = null;
                    theCertificates.signature_3_en = null;
                }
            } else {
                theCertificates.signature_1 = null;
                theCertificates.signature_1_en = null;
                theCertificates.signature_2 = null;
                theCertificates.signature_2_en = null;
                theCertificates.signature_3 = null;
                theCertificates.signature_3_en = null;
            }

            // console.log(theCertificates);
            // return false;
            functionsFactory.clearError(angular.element('.certificates-frm'));
            theCertificates.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                certificatesFactory.update(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('certificates'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.certificates-frm'));
                    });
            }else{
                certificatesFactory.create(theCertificates)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('certificates/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('certificates'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.certificates-frm'));
                    });
            }
        }

        $scope.deleteImages = function(theImages) {
            var id = theImages.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                imagesFactory.delete(theImages).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);

                        if ($scope.certificates_data[$scope.imagesField] == theImages.picture) {
                            $scope.certificates_data[$scope.imagesField] =  null;
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
        }

        $scope.deleteCertificates = function(theCertificates) {
            var id = theCertificates.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                certificatesFactory.delete(theCertificates).success(function(data) {
                    if(data.is_error == false){
                        notification("success",data.message);
                        $route.reload();
                    }
                    if(data.is_error == true){
                        notification("error",data.message);
                    }
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
            }
        };

        $scope.previewCertification = function(theCertificates) {
            // functionsFactory.notification("error", "This function isn't available.");
            console.log(theCertificates);
            angular.element('#box-preview-certificates').css('opacity', '0');
            functionsFactory.notification("info", "Loading preview...");

            theCertificates.lang = $scope.languageOptions.certLang;
            certificatesFactory.createPreview(theCertificates).success(function(data) {
                // theCertificates.old_file = data.filePreview;
                angular.element('#box-preview-certificates').attr('src', data.urlPreview);
                $timeout(function() {
                    angular.element('#box-preview-certificates').css('opacity', '1');
                }, 1000);

                $timeout(function() {
                    certificatesFactory.deletePreview(data.filePreview);
                }, 1500);
            }).error(function(data) {
                $timeout(function() {
                    functionsFactory.notification("error", "The data used in the preview is incomplete.");
                }, 1000);
                theCertificates.old_file = null;
                // console.log('error');
                // console.log(data);
            });
        };

        //notification
        var notification = function (status,alert) {
            if(status == "success") {
                var n = noty({
                    text        : '<div class="alert alert-success"><p><strong> '+ alert +' </strong></p></div>',
                    layout      : 'topRight',
                    theme       : 'made',
                    maxVisible  : 10,
                    animation   : {
                        open  : 'animated bounceInRight',
                        close : 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            } else {
                var n = noty({
                    text        : '<div class="alert alert-danger"><p><strong> '+ alert +' </strong></p></div>',
                    layout      : 'topRight',
                    theme       : 'made',
                    maxVisible  : 10,
                    animation   : {
                        open  : 'animated bounceInRight',
                        close : 'animated bounceOutRight'
                    },
                    timeout: 3000
                });
            }
        }

    }]);
