'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('paymentsCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', 'paymentsFactory', 'ordersFactory', 'methodsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, paymentsFactory, ordersFactory, methodsFactory, functionsFactory, settingsFactory) {

        $scope.payments = {};
        $scope.payments_data = {
            'currency': 'THB',
            'payment_status': 'successful',
        };

        $scope.search = "";
        $scope.from_date = $filter('date')(new Date().toISOString(), 'MM/dd/yyyy');
        $scope.to_date = $filter('date')(new Date().toISOString(), 'MM/dd/yyyy');

        $scope.mode = "Create";

        $scope.max_size = 5;
        $scope.page = 1;
        $scope.per_page = 10;
        $scope.current_page = 1;
        $scope.sorting_order = 'id';
        $scope.sorting_direction = 'desc';

        $scope.payment_statuses = [
            { "value": 'pending', "title": "รอชำระเงิน" },
            { "value": 'successful', "title": "ชำระเงินเรียบร้อย" },
            { "value": 'rejected', "title": "ถูกปฏิเสธ" },
            { "value": 'canceled_by_user', "title": "ยกเลิกโดยผู้ใช้" },
            { "value": 'failed', "title": "เกิดข้อผิดพลาด" },
        ];

        $scope.selected_orders;
        $scope.selected_methods;

        if (!angular.isUndefined($routeParams.orders_id)) {
            $scope.payments_data.orders_id = parseInt($routeParams.orders_id);
        }

        ///Add on datepicker dateFormat
        $timeout(function() {
            $('#txn_datetime').datetimepicker({
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

        $("#approve_datetime").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: "HH:mm:ss",
            onSelect: function (date) {
                $scope.payments_data.approve_datetime = date;
            }
        });
        ///

        $scope.last_7days = {};
        for (var i = 0; i < 7; i++) {
            var date_now = new Date();
            date_now.setDate(date_now.getDate() - [i]);
            $scope.last_7days[i] = $filter('date')(date_now.toISOString(), 'MM/dd/yyyy');
        }

        var set_pagination = function(pagination_data) {

            $scope.total = pagination_data.total;
            $scope.last_page = pagination_data.last_page;
            $scope.current_page = pagination_data.current_page;
            $scope.per_page = pagination_data.per_page;

        };

        var success_callback = function(resp) {
            $scope.payments = resp.data;
            for(var i=0; i<$scope.payments.length; i++) {
                var newOrdersCreateDatetime = new Date($scope.payments[i].orders.create_datetime).toISOString();
                $scope.payments[i].orders.create_datetime = $filter('date')(newOrdersCreateDatetime, 'dd MMM yyyy HH:mm:ss');
                var newApproveDatetime = new Date($scope.payments[i].approve_datetime).toISOString();
                $scope.payments[i].approve_datetime = $filter('date')(newApproveDatetime, 'dd MMM yyyy HH:mm:ss');
                var newPaymentsCreateDatetime = new Date($scope.payments[i].approve_datetime).toISOString();
                $scope.payments[i].create_datetime = $filter('date')(newPaymentsCreateDatetime, 'dd MMM yyyy HH:mm:ss');
                $scope.payments[i].no = ((resp.total) - (resp.from + i)) + 1;
            }
            set_pagination(resp);
        };

        var payments_query = function(page, per_page, from_date, to_date, search) {
            if(search){
                from_date = ''; $scope.from_date = '';
                to_date = ''; $scope.to_date = '';
            }
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+"&from_date="+from_date+"&to_date="+to_date+"&search="+search;
            var query = paymentsFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.toggleStatus = function(thePayments) {
            thePayments.admin_id = $scope.admin.id;
            if (thePayments.status == 1) { thePayments.status = 0; } else { thePayments.status = 1; }
            paymentsFactory.update(thePayments)
                .success(function(data) {
                    notification("success",data.message);
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        // $scope.toggleApproveStatus = function (thePayments, forceUpdate) {
        //     thePayments.admin_id = $scope.admin.id;
        //     if (thePayments.approve_status == 1) { thePayments.approve_status = 0; } else { thePayments.approve_status = 1; }
        //     if ($scope.mode == "Edit" || forceUpdate === true) {
        //         if (forceUpdate === true) { thePayments._mode = 'list'; }
        //         paymentsFactory.update(thePayments)
        //             .success(function (data) {
        //                 if (data.is_error == false) {
        //                     notification("success", data.message);
        //                 }
        //                 if (data.is_error == true) {
        //                     notification("error", data.message);
        //                 }
        //             })
        //             .error(function () {
        //                 notification("error", settingsFactory.getConstant('server_error'));
        //             });
        //     }
        // };

        // $scope.updateApproveStatus = function(thePayments) {
        //     if (thePayments.approve_status == 1) { thePayments.approve_status = 0; } else { thePayments.approve_status = 1; }
        //     paymentsFactory.updateApproveStatus({'id': thePayments.id, 'approve_status': thePayments.approve_status})
        //         .success(function(data) {
        //             if (data.is_error == false) {
        //                 notification("success",data.message);
        //             } else {
        //                 notification("error",data.message);
        //             }
        //         })
        //         .error(function(data) {
        //             if (data.message !== undefined) {
        //                 // notification("error", data.message);
        //                 notification("error", settingsFactory.getConstant('server_error'));
        //             } else {
        //                 notification("error", settingsFactory.getConstant('server_error'));
        //             }
        //         });
        // };

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                payments_query(new_page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
            }
        });

        $scope.$watch('payments_data.orders_id', function(new_value, old_value) {
            if (new_value != undefined) {
                ordersFactory.get({id:new_value})
                    .success(function(data) {
                        $scope.selected_orders = data;
                    })
            }
        });

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            payments_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        payments_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);

        ordersFactory.all().success(function(data) {
            $scope.orders = data;
        });

        methodsFactory.all().success(function(data) {
            $scope.methods = data;
        });

        if (!angular.isUndefined($routeParams.id)) {
            paymentsFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.payments_data = data;
                    $scope.mode = "Edit";
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        $scope.changeFilter = function() {
            payments_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
        }

        $scope.changeMethods = function() {
            if ($scope.selected_methods.id !== undefined && $scope.selected_methods.id !== null) {
                methodsFactory.get($scope.selected_methods).success(function(data) {
                    $scope.selected_methods = data;
                    $scope.payments_data.methods = data.title;
                    // $scope.payments_data.methods_type = data.type_title;
                });
            }
        }

        $scope.changeLast7day = function(last_7day) {
            $scope.search = "";
            $scope.from_date = last_7day;
            $scope.to_date = last_7day;
            payments_query($scope.page, $scope.per_page, last_7day, last_7day, $scope.search);
        }

        $scope.submitPayments = function(thePayments, nextAction) {
            functionsFactory.clearError(angular.element('.payments-frm'));
            thePayments.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                paymentsFactory.update(thePayments)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('payments'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.payments-frm'));
                    });
            }else{
                paymentsFactory.create(thePayments)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('payments/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('payments'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.payments-frm'));
                    });
            }
        }

        $scope.updateIsCanceled = function(thePayments) {
            if (thePayments.is_canceled == 1) { thePayments.is_canceled = 0; } else { thePayments.is_canceled = 1; }
            paymentsFactory.updateIsCanceled({'id': thePayments.id, 'is_canceled': thePayments.is_canceled})
                .success(function(data) {
                    if (data.is_error == false) {
                        notification("success",data.message);
                        // $route.reload();
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

        // $scope.deletePayments = function(thePayments) {
        //     var id = thePayments.id;
        //     var alert = confirm("Are you sure to delete #" + id + " ?");
        //     if(alert == true) {
        //         paymentsFactory.delete(thePayments)
        //             .success(function(data) {
        //                 if(data.is_error == false){
        //                     notification("success",data.message);
        //                     $route.reload();
        //                 }
        //                 if(data.is_error == true){
        //                     notification("error",data.message);
        //                 }
        //             })
        //             .error(function() {
        //                 notification("error", settingsFactory.getConstant('server_error'));
        //             });
        //     }
        // };

        $scope.downloadReconcileFile = function(thePayments) {
            window.location.href = settingsFactory.get('payments') + '/' + thePayments.id + '/reconcile/file';
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
