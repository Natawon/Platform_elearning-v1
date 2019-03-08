<?php

return [
    '_ENV_SITE' => "Production",
    '_errorMessage' => [
        '_400' => 'Bad Request',
        '_401' => 'Unauthorized - The request has not been applied because it lacks valid authentication credentials.',
        '_403' => 'Forbidden - The requested is not allowed.',
        '_404' => 'Not Found - The {{_RESOURCE_}} is not found.',
        '_422' => 'The request has invalid parameters.',
        '_500' => 'Internal Server Error. Please contact administrator of this website.',
    ],
    '_SET_URL' => [
        'GROUPS' => [
            'SETGroup' => [
                // Testing
                'S1' => 'https://test.set.or.th/set/unauthorizedAccess.do',
                'S2' => 'https://test.set.or.th/set/api/signout.do',
                'S3' => 'https://member.set.or.th/set/forgotPassword.do?'

                // Production
                // 'S1' => 'https://www.set.or.th/set/unauthorizedAccess.do',
                // 'S2' => 'https://www.set.or.th/set/api/signout.do',
                // 'S3' => 'https://member.set.or.th/set/forgotPassword.do'
            ],
            'SETListedCompany' => [
                // Testing
                'S1' => 'https://www.scptrain.set.or.th',
                'S2' => 'https://www.scptrain.set.or.th/setdd/index.jsp',
                'S3' => 'https://www.scptrain.set.or.th/setdd/resetPassword.do'

                // Production
                // 'S1' => 'https://www.setportal.set.or.th',
                // 'S2' => 'https://www.setportal.set.or.th/setdd/index.jsp',
                // 'S3' => 'https://www.setportal.set.or.th/setdd/resetPassword.do'
            ],
            'SETBroker' => [
                // Testing
                'S1' => 'http://winner.open-cdn.com/SETBroker/login',
                'S2' => 'http://winner.open-cdn.com/SETBroker',
                'S3' => 'http://winner.open-cdn.com/forgot-password'

                // Production
                // 'S1' => 'https://elearning.set.or.th/SETBroker/login',
                // 'S2' => 'https://elearning.set.or.th/SETBroker',
                // 'S3' => 'https://elearning.set.or.th/forgot-password'
            ],
            'SETStudent' => [
                // Testing
                'S1' => 'http://winner.open-cdn.com/SETStudent/login',
                'S2' => 'http://winner.open-cdn.com/SETStudent',
                'S3' => 'http://winner.open-cdn.com/forgot-password'

                // Production
                // 'S1' => 'https://elearning.set.or.th/SETStudent/login',
                // 'S2' => 'https://elearning.set.or.th/SETStudent',
                // 'S3' => 'https://elearning.set.or.th/forgot-password'
            ],
            'SETEmployee' => [
                // Testing
                'S1' => 'https://test.enterprise.set.or.th/OAuth2/Authorize_OAuth2.aspx?redirectURL=https://test.enterprise.set.or.th/OAuth2/API/ELearning/AuthorizeByAD.aspx&Systemname=eLearning',
                'S2' => 'https://www.intranet.set.or.th',
                'S3' => 'https://test.enterprise.set.or.th/OAuth2/Authorize_OAuth2.aspx?redirectURL=https://test.enterprise.set.or.th/OAuth2/API/ELearning/AuthorizeByAD.aspx&Systemname=eLearning'

                // Production
                // 'S1' => 'https://enterprise.set.or.th/OAuth2/Authorize_OAuth2.aspx?redirectURL=https://enterprise.set.or.th/OAuth2/API/ELearning/AuthorizeByAD.aspx&Systemname=eLearning',
                // 'S2' => 'https://www.intranet.set.or.th',
                // 'S3' => 'https://enterprise.set.or.th/OAuth2/Authorize_OAuth2.aspx?redirectURL=https://enterprise.set.or.th/OAuth2/API/ELearning/AuthorizeByAD.aspx&Systemname=eLearning'
            ],
            'SETTFEX' => [
                // Testing
                'S1' => 'https://test.set.or.th/set/unauthorizedAccess.do',
                'S2' => 'https://test.set.or.th/set/api/signout.do',
                'S3' => 'https://member.set.or.th/set/forgotPassword.do?'

                // Production
                // 'S1' => 'https://www.set.or.th/set/unauthorizedAccess.do',
                // 'S2' => 'https://www.set.or.th/set/api/signout.do',
                // 'S3' => 'https://member.set.or.th/set/forgotPassword.do'
            ]
        ],

        // Testing
        'S1' => 'https://test.set.or.th/set/unauthorizedAccess.do',
        'S2' => 'http://winner.open-cdn.com/',
        'S3' => 'https://test.set.or.th/set/forgotPassword.do',
        'R1' => 'https://test.registration.set.or.th/registration/API/elearning/course',
        'R2' => 'https://test.registration.set.or.th/registration/API/elearning/enroll',

        // Production
        // 'S1' => 'https://www.set.or.th/set/unauthorizedAccess.do',
        // 'S2' => 'https://www.set.or.th/set/api/signout.do',
        // 'S3' => 'https://member.set.or.th/set/forgotPassword.do',
        // 'R1' => 'https://scm.set.or.th/registration/API/elearning/course',
        // 'R2' => 'https://scm.set.or.th/registration/API/elearning/enroll',
    ],
    '_BASE_URL' => 'http://winner.open-cdn.com/',
    '_BASE_API_URL' => 'http://winner.open-cdn.com/api/',
    '_BASE_SITE_API_URL' => 'http://winner.open-cdn.com/api/site/',
    '_BASE_BACKEND_URL' => 'http://winner.open-cdn.com/backend/',
    '_BASE_FILE_URL' => [
        'COURSES_THUMBNAIL' => 'http://winner.open-cdn.com/data-file/courses/thumbnail/',
    ],
    'URL' => [
        'P_404' => 'http://winner.open-cdn.com/404',
    	'HOME' => 'http://winner.open-cdn.com/',
        'LIST' => 'http://winner.open-cdn.com/list',
        'INFO' => 'http://winner.open-cdn.com/courses/{COURSE_ID}/info',
    	'LEARNING' => 'http://winner.open-cdn.com/enroll/{COURSE_ID}/download'
    ],
    'URL_GROUP' => [
        'P_404' => 'http://winner.open-cdn.com/{GROUP_KEY}/404',
        'HOME' => 'http://winner.open-cdn.com/{GROUP_KEY}',
        'LIST' => 'http://winner.open-cdn.com/{GROUP_KEY}/list',
        'INFO' => 'http://winner.open-cdn.com/{GROUP_KEY}/courses/{COURSE_ID}/info',
        'LEARNING' => 'http://winner.open-cdn.com/{GROUP_KEY}/enroll/{COURSE_ID}/download',
        'MY_COURSES' => 'http://winner.open-cdn.com/{GROUP_KEY}/my-profile/courses',
        'MY_COURSES_WITH_CER' => 'http://winner.open-cdn.com/{GROUP_KEY}/my-profile/courses/{COURSE_ID}',
        'MY_ORDERS' => 'http://winner.open-cdn.com/{GROUP_KEY}/my-profile/orders',
        'SESSION_EXISTS' => 'http://winner.open-cdn.com/{GROUP_KEY}/session/exists'
    ],
    'PATH' => [
        'HOME' => '',
        'LIST' => 'list',
        'INFO' => 'courses/{COURSE_ID}/info',
        'LEARNING' => 'enroll/{COURSE_ID}/download'
    ],
    'GROUPS' => [
        'SETGROUP' => 'SETGroup',
        'SETLISTEDCOMPANY' => 'SETListedCompany',
        'SETBROKER' => 'SETBroker',
        'SETSTUDENT' => 'SETStudent',
        'SETEMPLOYEE' => 'SETEmployee',
        'SETTFEX' => 'SETTFEX',
    ],
    'PROJECT_INFO' => [
        "TITLE" => "Company Name",
        "CONTACT_CENTER" => "Contact Center",
        "CONTACT_PHONE" => "02-000-0000",
        "CONTACT_MAIL" => "info@domain.com",
        "WEBSITE" => "https://www.mydomain.com"
    ],
    'EMAIL' => [
        'USERNAME' => 'server@dootvmedia.com',
        'NAME' => 'Winner Estate e-Learning',
        'BCC' => 'dev@dootvmedia.com',
        'BCC_NAME' => 'Dev DooTV Media'
    ],
    'CERTIFICATE_NUMBER_PREFIX' => 'WN'
];
