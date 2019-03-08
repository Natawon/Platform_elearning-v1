'use strict';

/**
 * @ngdoc function
 * @name newappApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the newappApp
 */
angular.module('newApp')
    .controller('ordersCtrl', ['$scope', '$routeParams', '$location', '$route', '$filter', '$timeout', 'ordersFactory', 'methodsFactory', 'membersFactory', 'coursesFactory', 'paymentsFactory', 'functionsFactory', 'settingsFactory',
    function ($scope, $routeParams, $location, $route, $filter, $timeout, ordersFactory, methodsFactory, membersFactory, coursesFactory, paymentsFactory, functionsFactory, settingsFactory) {

        $scope.orders = {};
        $scope.orders_data = {
            'currency': 'THB',
            'type_tax_invoice': 'personal',
            // 'members_id': 10589,
        };

        $scope.selected_members = {};

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

        $scope.type_tax_invoices = [
            { "value": 'personal', "title": "บุคคลธรรมดา" },
            { "value": 'corporate', "title": "นิติบุคคล" },
        ];

        $scope.branch_tax_invoices = [
            { "value": 0, "title": "สำนักงานใหญ่" },
            { "value": 1, "title": "สาขา" },
        ];

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
            $scope.orders = resp.data;
            for(var i=0; i<$scope.orders.length; i++) {

                var newCreateDatetime= new Date($scope.orders[i].create_datetime).toISOString();
                $scope.orders[i].create_datetime = $filter('date')(newCreateDatetime, 'dd MMM yyyy HH:mm:ss');

                $scope.orders[i].no = ((resp.total) - (resp.from + i)) + 1;
            }
            set_pagination(resp);
        };

        var orders_query = function(page, per_page, from_date, to_date, search) {
            if(search){
                from_date = ''; $scope.from_date = '';
                to_date = ''; $scope.to_date = '';
            }
            var query_string = "&page="+ page +"&per_page="+ per_page +"&order_by="+$scope.sorting_order+"&order_direction="+$scope.sorting_direction+"&from_date="+from_date+"&to_date="+to_date+"&search="+search;
            var query = ordersFactory.query(query_string);
            query.success(success_callback);
        };

        $scope.$watch('current_page', function(new_page, old_page) {
            if (new_page != old_page) {
                orders_query(new_page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
            }
        });

        $scope.$watch('orders_data.members_id', function(new_value, old_value) {
            if (new_value != undefined) {
                membersFactory.get({id:new_value})
                    .success(function(data) {
                        $scope.selected_members = data;
                    })
            }
        });

        $scope.$watch('orders_data.inv_branch', function(new_value, old_value) {
            if (old_value != undefined && new_value != undefined) {
                if (new_value == 0) {
                    $scope.orders_data.inv_branch_no = '00000';
                } else {
                    $scope.orders_data.inv_branch_no = '';
                }
            }
        });

        $scope.$watch('orders_data.type_tax_invoice', function(new_value, old_value) {
            if (new_value != undefined) {
                if (new_value == 'personal') {
                    // $scope.orders_data.inv_branch = null;
                    // $scope.orders_data.inv_branch_no = null;
                }
            }
        });

        $scope.sort_by = function(newSortingOrder) {
            if ($scope.sorting_order == newSortingOrder) {
                $scope.sorting_direction = ($scope.sorting_direction=='desc')?'asc':'desc';
            }
            $scope.sorting_order = newSortingOrder;
            orders_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
            $('th i').each(function() {
                $(this).removeClass().addClass('fa fa-sort');
            });
            if ($scope.sorting_direction == 'desc') {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-up');
            } else {
                $('th.'+newSortingOrder+' i').removeClass().addClass('fa fa-sort-down');
            }
        };

        orders_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);

        methodsFactory.all().success(function(data) {
            $scope.methods = data;
            /*var last = $scope.methods.length;
            $scope.orders_data.methods_id = $scope.methods[last-1];*/
        });

        // membersFactory.all().success(function(data) {
        //     $scope.members = data;
        // });
        setTimeout(function () {
            if ($('.members-loading-data').length) {
                $(".members-loading-data").select2({
                    placeholder: "-- Search for a member --",
                    minimumInputLength: 3,
                    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                        url: settingsFactory.get('members') + '/all',
                        dataType: 'json',
                        quietMillis: 250,
                        data: function (term, page) {
                            return {
                                search: term, // search term
                            };
                        },
                        results: function (data, page) { // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to alter the remote JSON data
                            return { results: data };
                        },
                        cache: true
                    },
                    initSelection: function (element, callback) {
                        // the input tag has a value attribute preloaded that points to a preselected repository's id
                        // this function resolves that id attribute to an object that select2 can render
                        // using its formatResult renderer - that way the repository name is shown preselected
                        var id = element.val();
                        if (id !== "") {
                            $.ajax(settingsFactory.get('members') + '/' + id, {
                                dataType: "json"
                            }).done(function (data) {
                                callback(data);
                            });
                        }
                    },
                    formatResult: function (data) {
                        var markup = '<div class="row"><div class="col-md-12">'+data.email+'</div></div>';
                        return markup;
                    }, // omitted for brevity, see the source of this page
                    formatSelection: function (data) {
                        return data.email;
                    },  // omitted for brevity, see the source of this page
                    dropdownCssClass: "form-white", // apply css that makes the dropdown taller
                    escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
                });
            }
        }, 1000);

        coursesFactory.all().success(function(data) {
            $scope.courses = data;
            // $scope.orders_data.courses_id = $scope.courses[0];
        });

        if (!angular.isUndefined($routeParams.id)) {
            ordersFactory.get({id:$routeParams.id})
                .success(function(data) {
                    $scope.orders_data = data;
                    $scope.mode = "Edit";
                })
                .error(function() {
                    notification("error", settingsFactory.getConstant('server_error'));
                });
        }

        angular.element('.courses').addClass('hidden');

        // $scope.changeMembers = function() {
        //     var theMembers = {};
        //     theMembers.id = $scope.orders_data.members_id;
        //     membersFactory.get(theMembers).success(function(data) {
        //         $scope.orders_data.members_email = data.email;
        //         $scope.orders_data.members_mobile = data.mobile;
        //     });
        // }

        $scope.changeCourses = function() {
            var theCourses = {};
            theCourses.id = $scope.orders_data.courses_id;
            coursesFactory.get(theCourses).success(function(data) {
                $scope.orders_data.courses_code = data.code;
                $scope.orders_data.courses_title = data.title;
                $scope.orders_data.courses_price = data.price;
            });
        }

        $scope.changeFilter = function() {
            orders_query($scope.page, $scope.per_page, $scope.from_date, $scope.to_date, $scope.search);
        }

        $scope.changeLast7day = function(last_7day) {
            $scope.search = "";
            $scope.from_date = last_7day;
            $scope.to_date = last_7day;
            orders_query($scope.page, $scope.per_page, last_7day, last_7day, $scope.search);
        }

        $scope.submitOrders = function(theOrders, nextAction) {
            functionsFactory.clearError(angular.element('.orders-frm'));
            theOrders.admin_id = $scope.admin.id;
            if ($scope.mode == "Edit") {
                ordersFactory.update(theOrders)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'continue_editing' : $route.reload(); break;
                                default                 : $location.path('orders'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.orders-frm'));
                    });
            }else{
                angular.element('#btn-save').button('loading');
                angular.element('#btn-cancel').button('loading');
                ordersFactory.create(theOrders)
                    .success(function(data) {
                        if(data.is_error == false){
                            notification("success",data.message);

                            switch (nextAction) {
                                case 'add_another'      : $route.reload(); break;
                                case 'continue_editing' : $location.path('orders/'+ data.createdId +'/edit'); break;
                                default                 : $location.path('orders'); break;
                            }
                        }
                        if(data.is_error == true){
                            notification("error",data.message);
                        }
                    })
                    .error(function(data) {
                        functionsFactory.handleError(data, angular.element('.orders-frm'));
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

        // $scope.updateApproveStatus = function(thePayments) {
        //     if (thePayments.approve_status == 1) { thePayments.approve_status = 0; } else { thePayments.approve_status = 1; }
        //     paymentsFactory.updateApproveStatus({'id': thePayments.id, 'approve_status': thePayments.approve_status})
        //         .success(function(data) {
        //             if (data.is_error == false) {
        //                 notification("success",data.message);
        //                 $route.reload();
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

        // $scope.deleteOrders = function(theOrders) {
        //     var id = theOrders.id;
        //     var alert = confirm("Are you sure to delete #" + id + " ?");
        //     if(alert == true) {
        //         ordersFactory.delete(theOrders)
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

        $scope.deletePayments = function(thePayments) {
            var id = thePayments.id;
            var alert = confirm("Are you sure to delete #" + id + " ?");
            if(alert == true) {
                paymentsFactory.delete(thePayments)
                    .success(function(data) {
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
