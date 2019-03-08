// ===== GENERAL FUNCTION ===== //
var fns = function() {

	var urlParam = function(param) {
		var result = "";
		var tmp = [];
		var params;
		// .replace ( "?", "" )
		// this is better, there might be a question mark inside
		params = location.search.substr(1).split("&");
		params.forEach(function(item) {
			// tmp = item.split("=", 2);
			tmp = explode("=", item, 2);
			if (tmp[0] === param && tmp[1] !== undefined) {
				result = decodeURIComponent(tmp[1]);
			}
		});
		return result;
	};

	var handleError = function(data, placeholder, wrapperAnimate) {
		var error;
		var prop;
		var $wrapperInput;

		clearError(placeholder);

		if (typeof data.responseJSON === "object") {
			error = data.responseJSON;
		} else if (isJsonParse(data.responseText)) {
			error = $.parseJSON(data.responseText);
		} else if (typeof data.responseText === "object") {
			error = data.responseText
		} else {
			return false;
		}

		if (wrapperAnimate === undefined) {
			wrapperAnimate = "html, body";
		}

		placeholder = (placeholder !== undefined) ? placeholder : "" ;

		for (prop in error) {

			if (prop === "message") {
                notification('error', error[prop]);
            }

			if (typeof placeholder === 'object') {
				placeholder.find('#'+prop).closest('.form-group').addClass('has-error');
				$wrapperInput = placeholder.find('#'+prop).parent();
			} else {
				$(placeholder+" #"+prop).closest('.form-group').addClass('has-error');
				$wrapperInput = $(placeholder+" #"+prop).parent();
			}

			if ($wrapperInput.hasClass('input-group')) {
				if (error[prop].length > 1) {
	                for (subProp in error[prop]) {
	                    $wrapperInput.parent().append('<small class="help-block">- '+error[prop][subProp]+'</small>');
	                }
	            } else {
	            	$wrapperInput.parent().append('<small class="help-block">- '+error[prop]+'</small>');
	            }
			} else if ($wrapperInput.hasClass('ace-file-input') && $wrapperInput.parent().find('.ace-file-input').length == 1) {
				$wrapperInput.after('<small class="help-block">- '+error[prop]+'</small>');
			} else if ($wrapperInput.is('label') && $wrapperInput.parent().hasClass('radio')) {
				$wrapperInput.parent().parent().append('<small class="help-block">- '+error[prop]+'</small>');
			} else if (error[prop].length > 1) {
                for (subProp in error[prop]) {
                    $wrapperInput.append('<small class="help-block">- '+error[prop][subProp]+'</small>');
                }
            } else {
				$wrapperInput.append('<small class="help-block">- '+error[prop]+'</small>');
			}
			// $("label[for='"+prop+"']").addClass('fc-red');
	    }

	    if (typeof placeholder === 'string') {
	    	placeholder = $(placeholder);
	    }

	    if (placeholder.find(".help-block:first").length) {
	    	$(wrapperAnimate).animate({
				scrollTop: (placeholder.find(".help-block:visible:first").offset().top - 100)
			}, 800);
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
    };

	var handleNotyError = function(subject, data) {
		var error = {};

		if (typeof data !== 'undefined') {
			if (typeof data.responseJSON === 'object') {
				error = data.responseJSON;
			} else {
				if (isJsonParse(data.responseText)) {
					error = $.parseJSON(data.responseText);
				}
			}
		}

		subject = getNotyText(subject);

		if (typeof error.error !== 'undefined') {
			error = error.error
			noty({text: subject+' <br> <span style="word-wrap: break-word;" class="smaller-90">'+error.text+'</span>', type: 'error', timeout: 4000});
		} else if (typeof error.message !== 'undefined') {
			noty({text: subject+' <br> <span style="word-wrap: break-word;" class="smaller-90">'+error.message+'</span>', type: 'error', timeout: 4000});
		} else {
			noty({text: subject, type: 'error', timeout: 2000});
		}

	};

	var boxAlert = function(text, type) {
		text = getNotyText(text);
		switch (type) {
			case 'info':
				return "<div class='alert alert-info' role='alert'>"+text+"</div>";
				break;
			case 'danger':
				return "<div class='alert alert-danger' role='alert'>"+text+"</div>";
				break;
			default:
				return "<div class='alert alert-danger' role='alert'>"+text+"</div>";
				break;
		}
	};

	var isJsonParse = function(str) {
		try {
	        JSON.parse(str);
	    } catch (e) {
	        return false;
	    }
	    return true;
	};

	var arrayIntersect = function(arrays) {
		var result;
		var conditionIndexOf;
		result = arrays.shift().reduce(function(response, currentValue) {
			conditionIndexOf = arrays.every(function(element) {
				return element.indexOf(currentValue) !== -1;
			})

			if (response.indexOf(currentValue) === -1 && conditionIndexOf) {
				response.push(currentValue);
			}

			return response;
		}, []);

		return result;
	};

	var checkRole = function(role) {
		var me = $.parseJSON(localStorage.getItem("me"));
		var objRole;
		var arrCheck = [];

		if(isJsonParse(role)) {
			// console.log(role);
			objRole = $.parseJSON(role);

			if (typeof me.role !== "string") {
				arrCheck.push(objRole);
				arrCheck.push(me.role);
				if (arrayIntersect(arrCheck).length > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				if (objRole.indexOf(me.role.toLowerCase()) !== -1) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	};

	var pathSelf = function() {
		return window.location.pathname+window.location.search;
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

	var tooltipPlacement = function(context, source) {
		var $source = $(source);
		var $parent = $source.closest('table');
		var off1 = $parent.offset();
		var w1 = $parent.width();

		var off2 = $source.offset();
		//var w2 = $source.width();

		if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) {
			return 'right';
		}

		return 'left';
	};

	var blink = function(selector) {
		$(selector).fadeIn(400);
		$('html, body').animate({
			scrollTop: ($(window).scrollTop()+50)
		}, 'slow');
	};

	var strip = function(html) {
		var tmp = document.createElement("DIV");
		tmp.innerHTML = html;
		return tmp.textContent || tmp.innerText || "";
	};

	var iconLoading = function(size) {
		var width, height;
		size = typeof size === 'undefined' ? 'sm' : size ;

		switch(size) {
			case 'xs':
				width  = '16';
				height = '16';
			break;

			case 'sm':
				width  = '28';
				height = '28';
			break;

			default:
				width  = '28';
				height = '28';
			break;
		}

		return "<img class='loading' src='../assets/img/preloader.gif' style='margin: 0px 10px;' width='"+width+"' height='"+height+"'>";
	};

	var removeIconLoading = function($selector) {
		$selector = typeof selector !== 'undefined' ? $selector : $('body');
		$selector.find('.loading').fadeOut(200);
	};

	var getPaginateBy = function($ui) {
		var params = {};
		params.paginate_by = $ui.val();
		return $.param(params);
	};

	var getNotyText = function(code) {
		var message;
		switch(code) {
			case 'createSuccess':
				message = 'Successfully created.';
				break;
			case 'createFail':
				message = 'Create failed.';
				break;

			case 'addSuccess':
				message = 'Successfully added.';
				break;
			case 'addFail':
				message = 'Add failed.';
				break;

			case 'updateSuccess':
				message = 'Successfully updated.';
				break;
			case 'updateFail':
				message = 'Update failed.';
				break;
			case 'noUpdate':
				message = 'No updated.';
				break;
			case 'noDetailUpdate':
				message = 'No detail updated.';
				break;
			case 'updateStatusSuccess':
				message = 'Successfully status updated.';
				break;
			case 'updateStatusFail':
				message = 'Update status failed.';
				break;

			case 'deleteSuccess':
				message = 'Successfully deleted.';
				break;
			case 'deleteFail':
				message = 'Delete failed.';
				break;

			case 'changeSuccess':
				message = 'Successfully changed.';
				break;
			case 'changeFail':
				message = 'Change failed.';
				break;

			case 'assignSuccess':
				message = 'Successfully assigned.';
				break;
			case 'assignFail':
				message = 'Assign failed.';
				break;
			case 'noAssign':
				message = 'No assignment.';
				break;

			case 'copySuccess':
				message = 'Successfully copied.';
				break;
			case 'copyFail':
				message = 'Copy failed.';
				break;

			case 'importSuccess':
				message = 'Successfully imported.';
				break;
			case 'importFail':
				message = 'Import failed.';
				break;

			case 'rejectSuccess':
				message = 'Successfully rejected.';
				break;
			case 'rejectFail':
				message = 'Reject failed.';
				break;

			case 'approveSuccess':
				message = 'Successfully approved.';
				break;
			case 'approveFail':
				message = 'Approve failed.';
				break;

			case 'calculateSuccess':
				message = 'Successfully calculated.';
				break;
			case 'calculateFail':
				message = 'Calculate failed.';
				break;

			case 'noResult':
				message = 'No result.';
				break;
			case 'loadSheetFail':
				message = 'Failed to load sheet.';
				break;
			case 'loadListFail':
				message = 'Failed to load list.';
				break;

			default:
				message = '';
			break;
		}

		return message;
	};

	var scrollIntoView = function($ui) {
		$('html, body').animate({ scrollTop: ($ui.offset().top-20) }, 'slow');
	};

	var submitForm = function(path, params, method) {
	    method = method || "post";

	    var form = document.createElement("form");
	    form.setAttribute("method", method);
	    form.setAttribute("action", path);

	    for(var key in params) {
	        if(params.hasOwnProperty(key)) {
	            var hiddenField = document.createElement("input");
	            hiddenField.setAttribute("type", "hidden");
	            hiddenField.setAttribute("name", key);
	            hiddenField.setAttribute("value", params[key]);

	            form.appendChild(hiddenField);
	         }
	    }

	    document.body.appendChild(form);
	    form.submit();
	};

	var htmlspecialchars = function(str) {
		return str
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	};

	var handleAlert = function(title, msg, status, isContact) {
		var _title = title !== undefined ? title : 'เกิดข้อผิดพลาด';
		var _msg = msg !== undefined ? msg : '';
		var _status = status !== undefined ? status : 'set';
		var _icon = '<i class="fa fa-exclamation-circle" aria-hidden="true"></i>';

		if (isContact === true) {
			_msg = _msg + '<br>กรุณาติดต่อ Contact Center โทร. 02-000-0000<br>อีเมล์ : info@domain.com';
		}

		$.alert({
			"type": _status,
			"title": _icon + " " + _title,
			"content": _msg
		});
	};

	var detectIOS = function (device) {
	    var iDevices = [
	        'iPad Simulator',
	        'iPhone Simulator',
	        'iPod Simulator',
	        'iPad',
	        'iPhone',
	        'iPod'
	    ];

	    if (!!navigator.platform) {
	        while (iDevices.length) {
	            if (navigator.platform === iDevices.pop()) {
	            	if (device === true) {
	            		return {
	            			"device": navigator.platform,
	            			"ios": true
	            		};
	            	} else {
	                	return true;
	            	}
	            }
	        }
	    }

	    if (device === true) {
	    	return {
    			"device": null,
    			"ios": false
    		};
	    } else {
	    	return false;
	    }
	};

	var currentGroup = function() {
		return $('header').data('group-site').replace("/", "");
	};

	var currentCourse = function() {
		return $('header').data('course');
	};

	var normalDateTimeTHClock = function(datetime) {
		var text;
		moment.locale('th');

		text = moment(datetime).format('D MMMM ') + (parseInt(moment(datetime).format('YYYY')) + 543);
        text += " " + moment(datetime).format('H:mm') + " น.";

        return text;
	};

	var parseNewLineToHtml = function(str) {
		return str.replace(/\n/g, "<br>");
	};

    var cleanTagP = function(str) {
        return str.replace(/<[^>]*>/g, '');
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

	var validateYouTubeUrl = function(url) {
        if (url != undefined || url != '') {
            var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
            var match = url.match(regExp);
            if (match && match[2].length == 11) {
                return { has_match: true, matches: match }
            } else {
                return { has_match: false, matches: match }
            }
        } else {
            return { has_match: false, matches: match }
        }
    };

    var getImage = function(dir, images) {
    	var path;
	    if(images){
	        path = dir+images;
	    }else{
	        path = '/images/Default.jpg';
	    }
	    return path;
	}

	return {
		// Get Param by name
		urlParam: function(param) {
			return urlParam(param);
		},
		// Handle error form api
		handleError: function(data, placeholder, wrapperAnimate) {
			return handleError(data, placeholder, wrapperAnimate);
		},
		// Handle noty error form api
		handleNotyError: function(subject, data) {
			return handleNotyError(subject, data);
		},
		// Show div error with text
		boxAlert: function(text, type) {
			return boxAlert(text, type);
		},
		// Check valid json object
		isJsonParse: function(str) {
			return isJsonParse(str);
		},
		// Computes the intersection of arrays
		arrayIntersect: function(arrays) {
			return arrayIntersect(arrays);
		},
		// Check role access
		checkRole: function(role) {
			return checkRole(role);
		},
		// Get Path Self equivalent to basename ($PHP_SELF)
		pathSelf: function() {
			return pathSelf();
		},
		// A JavaScript equivalent of PHP’s explode. Discuss at: http://phpjs.org/functions/explode/
		explode: function(delimiter, string, limit) {
			return explode(delimiter, string, limit);
		},
		// Tooltip placement for tooltip plugin
		tooltipPlacement: function(context, source) {
			return tooltipPlacement(context, source);
		},
		// Blink state row
		blink: function(selector) {
			return blink(selector);
		},
		// Strip tag html
		strip: function(html) {
			return strip(html);
		},
		// Load icon loading .gif
		iconLoading: function(size) {
			return iconLoading(size);
		},
		// Remove icon loading .gif
		removeIconLoading: function(selector) {
			return removeIconLoading(selector);
		},
		// Get value of paginate_by from element
		getPaginateBy: function($ui) {
			return getPaginateBy($ui);
		},
		// Get Text Notification
		getNotyText: function(code) {
			return getNotyText(code);
		},
		// Animate scroll into view element
		scrollIntoView: function($ui) {
			return scrollIntoView($ui);
		},
		// Animate scroll into view element
		submitForm: function(path, params, method) {
			return submitForm(path, params, method);
		},
		// Encode HTML Specialchars
		htmlspecialchars: function(str) {
			return htmlspecialchars(str);
		},
		// Handle Alert
		handleAlert: function(title, msg, status, isContact) {
			return handleAlert(title, msg, status, isContact);
		},
		// Check IOS Device
		detectIOS: function(device) {
			return detectIOS(device);
		},
		// Get Current Group
		currentGroup: function() {
			return currentGroup();
		},
		// Get Current Course
		currentCourse: function() {
			return currentCourse();
		},
		// 6 กันยายน 2561 11:42 น. (require Moment.js)
		normalDateTimeTHClock: function(datetime) {
			return normalDateTimeTHClock(datetime);
		},
		// Convert \n to <br>
		parseNewLineToHtml: function(str) {
			return parseNewLineToHtml(str);
		},
		// Remove Tag <p>
        cleanTagP: function(str) {
            return cleanTagP(str);
        },
        // String Padding
        str_pad: function(str, pad_length, pad_string, pad_type) {
        	return str_pad(str, pad_length, pad_string, pad_type);
        },
        // Check YouTube URL
        validateYouTubeUrl: function(url) {
        	return validateYouTubeUrl(url);
        },
        // Get Default Image
        getImage: function(dir, images) {
        	return getImage(dir, images);
        }
	} // Return functions

}();

// ===== JQUERY PLUGIN FUNCTION ===== //
// Serialize form to object
$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// Toggle Callback Funciton
$.fn.toggleClick = function() {
    var methods = arguments;    // Store the passed arguments for future reference
    var count = methods.length; // Cache the number of methods

    // Use return this to maintain jQuery chainability
    // For each element you bind to
    return this.each(function(i, item){
        // Create a local counter for that element
        var index = 0;

        // Bind a click handler to that element
        // $(document).delegate($(item), 'click', function(event) {
        $(item).off().on('click', function() {
            // That when called will apply the 'index'th method to that element
            // the index % count means that we constrain our iterator between 0
            // and (count-1)
            return methods[index++ % count].apply(this, arguments);
        });
    });
};



