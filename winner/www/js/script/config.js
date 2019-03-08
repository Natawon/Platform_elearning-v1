var PROJECT_ROOT       = 'http://winner.open-cdn.com';
var URL_API            = 'http://winner.open-cdn.com/api';
var URL_DATA_FILE      = 'http://winner.open-cdn.com/data-file';
// var URL_SET            = 'https://test.set.or.th/set';

var URL_GROUP_SET = {
    "G-Education": {
        "isRedirect"   : false,
        "redirectPage" : null,
        "login"        : PROJECT_ROOT + "/G-Education/login",
        "forgot"       : PROJECT_ROOT + "/G-Education/forgot-password",
    },
};

// Initail Secure
$.get(URL_API+"/site/csrf/token",function(e){$.ajaxSetup({headers:{"X-CSRF-TOKEN":e.csrf_token}})});
$.ajaxSetup({dataFilter:function(a){var c=")]}',\n";return 0===a.lastIndexOf(c,0)?JSON.parse(a.substr(c.length)):a}});