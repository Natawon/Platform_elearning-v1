
angular.module('newApp').factory('functionsFactory', ['$timeout', 'settingsFactory', function($timeout, settingsFactory) {

    var base64_encode = function b64EncodeUnicode(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode('0x' + p1);
        }));
    };

    var base64_decode = function b64DecodeUnicode(str) {
        return decodeURIComponent(atob(str).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    };

    //notification
    var notification = function (status, alert) {
        if (status == "success" || status == "info") {
            var n = noty({
                text: '<div class="alert alert-'+status+'"><p><strong> ' + alert + ' </strong></p></div>',
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
    };

    var syntaxHighlight = function(json) {
        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'json-number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'json-key';
                } else {
                    cls = 'json-string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'json-boolean';
            } else if (/null/.test(match)) {
                cls = 'json-null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    };

    var isJsonParse = function(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    };

    var handleError = function(data, placeholder) {
        // console.log("called function.")
        var error;
        var prop;
        var $wrapperInput;

        if (isJsonParse(data.responseText)) {
            error = $.parseJSON(data.responseText);
            // console.log("1");
        } else if (typeof data.responseText === "object") {
            error = data.responseText
            // console.log("2");
        } else if (typeof data === "object") {
            error = data;
            // console.log("3");
        } else {
            if (data.message !== undefined) {
                notification("error", data.message);
            } else {
                notification("error", settingsFactory.getConstant('server_error'));
            }
            // alert(data);
            // console.log("4");
            return false;
        }

        // console.log(error);

        placeholder = (placeholder !== undefined) ? placeholder : "" ;

        for (prop in error) {
            if (prop === "message") {
                // alert(error[prop]);
                // console.log("5");
                notification('error', error[prop]);
            }

            if (prop === "g-recaptcha-response") {
                if (Object.keys(error).length === 1) {
                    if (error[prop].length === 1) {
                        alert("The captcha is not correct.");
                    } else {
                        alert("The captcha field is required.");
                    }
                    break;
                } else {
                    continue;
                }
            }

            if (typeof placeholder === 'object') {
                // console.log("6");
                placeholder.find('#'+prop).closest('.form-group').addClass('has-error');
                $wrapperInput = placeholder.find('#'+prop).parent();
            } else {
                // console.log("7");
                $(placeholder+" #"+prop).closest('.form-group').addClass('has-error');
                $wrapperInput = $(placeholder+" #"+prop).parent();
            }

            $wrapperInput.closest('.tab-content').prev('.nav-tabs').children('li').eq($wrapperInput.closest('.tab-pane').index()).addClass('has-error');

            if ($wrapperInput.hasClass('input-group')) {
                // console.log("8");
                $wrapperInput.parent().append('<div class="help-block">- '+error[prop]+'</div>');
            } else if ($wrapperInput.hasClass('ace-file-input') && $wrapperInput.parent().find('.ace-file-input').length == 1) {
                // console.log("9");
                $wrapperInput.after('<div class="help-block">- '+error[prop]+'</div>');
            } else if ($wrapperInput.is('label') && $wrapperInput.parent().hasClass('radio')) {
                // console.log("10");
                $wrapperInput.parent().parent().append('<div class="help-block">- '+error[prop]+'</div>');
            } else if (error[prop].length > 1) {
                // console.log("11");
                for (subProp in error[prop]) {
                    $wrapperInput.append('<div class="help-block">- '+error[prop][subProp]+'</div>');
                }
            } else {
                // console.log("12");
                $wrapperInput.append('<div class="help-block">- '+error[prop]+'</div>');
            }
            // $("label[for='"+prop+"']").addClass('fc-red');
        }

        if (typeof placeholder === 'string') {
            // console.log("13");
            placeholder = $(placeholder);
        }

        if (placeholder.find(".has-error:visible:first").length) {
            // console.log("14");
            $timeout(function() {
                $('html, body').animate({
                    scrollTop: (placeholder.find(".has-error:visible:first").offset().top - 100)
                }, 500);
                return true;
            }, 100);
        } else if ($wrapperInput.closest('.tab-content').prev('.nav-tabs').children('li.has-error:visible:first').length) {
            // console.log("15");
            $timeout(function() {
                $('html, body').animate({
                    scrollTop: ($wrapperInput.closest('.tab-content').prev('.nav-tabs').children('li.has-error:visible:first').offset().top - 100)
                }, 500);
                return true;
            }, 100);
        } else {
            // console.log("16");
            return false;
        }
    };

    var clearError = function(placeholder) {
        placeholder = (placeholder !== undefined) ? placeholder : "" ;

        if (typeof placeholder === 'object') {
            placeholder.find('.has-error').removeClass('has-error');
            placeholder.find('.help-block').remove();
        } else {
            $(placeholder).find('.has-error').removeClass('has-error');
            $(placeholder).find('.help-block').remove();
        }

        placeholder.closest('.tab-content').prev('.nav-tabs').children('li.has-error').removeClass('has-error');
    };

    var explode = function(delimiter, string, limit) {
        if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined') {
            return null;
        }
        if (delimiter === '' || delimiter === false || delimiter === null) {
            return false;
        }
        if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string === 'object') {
            return {0: ''};
        }
        if (delimiter === true) {
            delimiter = '1';
        }

        // Here we go...
        delimiter += '';
        string += '';

        var s = string.split(delimiter);

        if (typeof limit === 'undefined') {
            return s;
        }

        // Support for limit
        if (limit === 0) {
            limit = 1;
        }

        // Positive limit
        if (limit > 0) {
            if (limit >= s.length) {
                return s;
            }
            return s.slice(0, limit - 1).concat([s.slice(limit - 1).join(delimiter) ]);
        }

        // Negative limit
        if (-limit >= s.length) {
            return [];
        }

        s.splice(s.length + limit);
        return s;
    };

    var durationToTime = function(duration) {
        var offset = Math.floor(duration);
        var sec_num = parseInt(offset, 10); // don't forget the second param
        var hours   = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);
        if (hours   < 10) {hours   = "0"+hours;}
        if (minutes < 10) {minutes = "0"+minutes;}
        if (seconds < 10) {seconds = "0"+seconds;}
        return hours+':'+minutes+':'+seconds;
    };

    var detectEncoding = function(str) {
        var encodingList = ["ASCII", "UTF-8", "UTF-7", "ISO-8859-1", "EUC-JP", "SJIS", "eucJP-win", "SJIS-win", "JIS", "ISO-2022-JP"];
        var dataDetect = jschardet.detect(str)

        var found = encodingList.find(function(element) {
            return element.toLowerCase() == dataDetect.encoding.toLowerCase();
        });

        return found;
    };

    function str_pad(str, pad_length, pad_string, pad_type) {
        var len = pad_length - str.length;
        if (len < 0) return str;
        for (var i = 0; i < len; i++) {
            if (pad_type == "STR_PAD_LEFT") {
                str = pad_string + str;
            } else {
                str += pad_string;
            }
        }

        return str;
    }

    function isEmpty(obj) {
        for (var i in obj) if (obj.hasOwnProperty(i)) return false;
        return true;
    }


    return {
		base64_encode: function(str) {
			return base64_encode(str);
		},
        base64_decode: function(str) {
            return base64_decode(str);
        },
        notification: function(status, alert) {
            return notification(status, alert);
        },
        syntaxHighlight: function(json) {
            return syntaxHighlight(json);
        },
        handleError: function(data, placeholder) {
            return handleError(data, placeholder);
        },
        clearError: function(placeholder) {
            return clearError(placeholder);
        },
        explode: function(delimiter, string, limit) {
            return explode(delimiter, string, limit);
        },
        durationToTime: function(duration) {
            return durationToTime(duration);
        },
        detectEncoding: function(str) {
            return detectEncoding(str);
        },
        // String Padding
        str_pad: function(str, pad_length, pad_string, pad_type) {
            return str_pad(str, pad_length, pad_string, pad_type);
        },
        isEmpty: function(obj) {
            return isEmpty(obj);
        }
    }
}]);
