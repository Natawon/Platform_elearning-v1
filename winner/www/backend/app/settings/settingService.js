
angular.module('newApp').factory('settingsFactory', [function() {

    var _CONSTANTS = {
        "server_error"       : "Something went wrong. Please try again or contact system administrator.",
        "BASE_SITE_URL"      : "http://winner.open-cdn.com",
        "BASE_SERVICE_URL"   : "http://winner.open-cdn.com/api/",
        "BASE_DATA_FILE_URL" : "http://winner.open-cdn.com/data-file/",
        "BASE_MEDIA_URL"     : "http://th-live-14.open-cdn.com:8080/winner/api-media/",
    };

    // For service laravel
    var SERVICE_URL = {
        ffmpeg: _CONSTANTS.BASE_MEDIA_URL + "ffmpeg",
        sc_videos: _CONSTANTS.BASE_MEDIA_URL + "sc_videos",
        internal: _CONSTANTS.BASE_MEDIA_URL + "internal",
        csrf: _CONSTANTS.BASE_SERVICE_URL + "csrf",
        auth: _CONSTANTS.BASE_SERVICE_URL + "auth",
        admins: _CONSTANTS.BASE_SERVICE_URL + "admins",
        admins_groups: _CONSTANTS.BASE_SERVICE_URL + "admins_groups",
        admins_menu: _CONSTANTS.BASE_SERVICE_URL + "admins_menu",
        configuration: _CONSTANTS.BASE_SERVICE_URL + "configuration",
        qa: _CONSTANTS.BASE_SERVICE_URL + "qa",
        highlights: _CONSTANTS.BASE_SERVICE_URL + "highlights",
        groups: _CONSTANTS.BASE_SERVICE_URL + "groups",
        categories: _CONSTANTS.BASE_SERVICE_URL + "categories",
        instructors: _CONSTANTS.BASE_SERVICE_URL + "instructors",
        courses: _CONSTANTS.BASE_SERVICE_URL + "courses",
        topics: _CONSTANTS.BASE_SERVICE_URL + "topics",
        members: _CONSTANTS.BASE_SERVICE_URL + "members",
        members_pre_approved: _CONSTANTS.BASE_SERVICE_URL + "members_pre_approved",
        documents: _CONSTANTS.BASE_SERVICE_URL + "documents",
        quiz: _CONSTANTS.BASE_SERVICE_URL + "quiz",
        questions: _CONSTANTS.BASE_SERVICE_URL + "questions",
        answer: _CONSTANTS.BASE_SERVICE_URL + "answer",
        videos: _CONSTANTS.BASE_SERVICE_URL + "videos",
        transcodings: _CONSTANTS.BASE_SERVICE_URL + "transcodings",
        usage_statistic: _CONSTANTS.BASE_SERVICE_URL + "usage_statistic",
        stats: _CONSTANTS.BASE_SERVICE_URL + "stats",
        stats_live: _CONSTANTS.BASE_SERVICE_URL + "stats_live",
        super_users: _CONSTANTS.BASE_SERVICE_URL + "super_users",
        sub_groups: _CONSTANTS.BASE_SERVICE_URL + "sub_groups",
        level_groups: _CONSTANTS.BASE_SERVICE_URL + "level_groups",
        classrooms: _CONSTANTS.BASE_SERVICE_URL + "classrooms",
        certificates: _CONSTANTS.BASE_SERVICE_URL + "certificates",
        domains: _CONSTANTS.BASE_SERVICE_URL + "domains",
        license_types: _CONSTANTS.BASE_SERVICE_URL + "license_types",
        slides: _CONSTANTS.BASE_SERVICE_URL + "slides",
        slides_times: _CONSTANTS.BASE_SERVICE_URL + "slides_times",
        images: _CONSTANTS.BASE_SERVICE_URL + "images",
        questionnaire_packs: _CONSTANTS.BASE_SERVICE_URL + "questionnaire_packs",
        questionnaires: _CONSTANTS.BASE_SERVICE_URL + "questionnaires",
        questionnaire_choices: _CONSTANTS.BASE_SERVICE_URL + "questionnaire_choices",
        my_profile: _CONSTANTS.BASE_SERVICE_URL + "my_profile",
        methods: _CONSTANTS.BASE_SERVICE_URL + "methods",
        orders: _CONSTANTS.BASE_SERVICE_URL + "orders",
        payments: _CONSTANTS.BASE_SERVICE_URL + "payments",
        jobs: _CONSTANTS.BASE_SERVICE_URL + "jobs",
        live: _CONSTANTS.BASE_SERVICE_URL + "live",
        cron_jobs: _CONSTANTS.BASE_SERVICE_URL + "cron_jobs",
        discussions: _CONSTANTS.BASE_SERVICE_URL + "discussions",
        subtitles: _CONSTANTS.BASE_SERVICE_URL + "subtitles",
    };

    // For dir images
    var DIR_URL = {
        base_admins_avatar: _CONSTANTS.BASE_DATA_FILE_URL + "admins/",
        base_configuration_logo: _CONSTANTS.BASE_DATA_FILE_URL + "configuration/logo/",
        base_categories_icon: _CONSTANTS.BASE_DATA_FILE_URL + "categories/icon/",
        base_highlights_picture: _CONSTANTS.BASE_DATA_FILE_URL + "highlights/",
        base_courses_thumbnail: _CONSTANTS.BASE_DATA_FILE_URL + "courses/thumbnail/",
        base_documents_file: _CONSTANTS.BASE_DATA_FILE_URL + "file/",
        base_certificates_logo: _CONSTANTS.BASE_DATA_FILE_URL + "certificates/logo/",
        base_certificates_logo_en: _CONSTANTS.BASE_DATA_FILE_URL + "certificates/logo_en/",
        base_certificates_watermark: _CONSTANTS.BASE_DATA_FILE_URL + "certificates/watermark/",
        base_certificates_watermark_en: _CONSTANTS.BASE_DATA_FILE_URL + "certificates/watermark_en/",
        base_groups_thumbnail: _CONSTANTS.BASE_DATA_FILE_URL + "groups/thumbnail/",
        base_slides_picture: _CONSTANTS.BASE_DATA_FILE_URL + "slides/picture/",
        base_slides_pdf: _CONSTANTS.BASE_DATA_FILE_URL + "slides/pdf/",
        base_images_logo: _CONSTANTS.BASE_DATA_FILE_URL + "images/logo/",
        base_images_signature: _CONSTANTS.BASE_DATA_FILE_URL + "images/signature/",
        base_instructors_pdf: _CONSTANTS.BASE_DATA_FILE_URL + "instructors/pdf/",
        base_methods_picture: _CONSTANTS.BASE_DATA_FILE_URL + "methods/picture/",
        base_discussions_file: _CONSTANTS.BASE_DATA_FILE_URL + "discussion/",
    };

    // For upload images
    var UPLOAD_URL = {
        upload_admins_avatar: _CONSTANTS.BASE_DATA_FILE_URL + "/admins_avatar_upload.php",
        upload_configuration_logo: _CONSTANTS.BASE_DATA_FILE_URL + "/configuration_logo_upload.php",
        upload_categories_icon: _CONSTANTS.BASE_DATA_FILE_URL + "/categories_icon_upload.php",
        upload_highlights_picture: _CONSTANTS.BASE_DATA_FILE_URL + "/highlights_picture_upload.php",
        upload_courses_thumbnail: _CONSTANTS.BASE_DATA_FILE_URL + "/courses_thumbnail_upload.php",
        upload_documents_file: _CONSTANTS.BASE_DATA_FILE_URL + "/documents_file_upload.php",
        upload_certificates_logo: _CONSTANTS.BASE_DATA_FILE_URL + "/certificates_logo_upload.php",
        upload_certificates_logo_en: _CONSTANTS.BASE_DATA_FILE_URL + "/certificates_logo_en_upload.php",
        upload_certificates_watermark: _CONSTANTS.BASE_DATA_FILE_URL + "/certificates_watermark_upload.php",
        upload_certificates_watermark_en: _CONSTANTS.BASE_DATA_FILE_URL + "/certificates_watermark_en_upload.php",
        upload_groups_thumbnail: _CONSTANTS.BASE_DATA_FILE_URL + "/groups_thumbnail_upload.php",
        upload_slides_picture: _CONSTANTS.BASE_DATA_FILE_URL + "/slides_picture_upload.php",
        upload_slides_pdf: _CONSTANTS.BASE_DATA_FILE_URL + "/slides_pdf_upload.php",
        upload_images_logo: _CONSTANTS.BASE_DATA_FILE_URL + "/images_logo_upload.php",
        upload_images_signature: _CONSTANTS.BASE_DATA_FILE_URL + "/images_signature_upload.php",
        upload_instructors_pdf: _CONSTANTS.BASE_DATA_FILE_URL + "/instructors_pdf_upload.php",
        upload_methods_picture: _CONSTANTS.BASE_DATA_FILE_URL + "/methods_picture_upload.php",
        chunk_video: _CONSTANTS.BASE_MEDIA_URL + "chunk-upload/server/php/index.php",
        get_video: _CONSTANTS.BASE_MEDIA_URL + "chunk-upload/server/php/get_video.php",
        upload_discussions_file: _CONSTANTS.BASE_DATA_FILE_URL + "/discussions_file_upload.php",
    };

    return {
		get: function(name) {
			return SERVICE_URL[name];
		},
        getUpload: function(name) {
            return UPLOAD_URL[name];
        },
        getURL: function(name) {
            return DIR_URL[name];
        },
        getConstant: function (name) {
            return _CONSTANTS[name];
        }
    }
}]);
