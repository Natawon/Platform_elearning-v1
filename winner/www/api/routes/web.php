<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Http\Request;

Route::get('api/debug/enroll', 'SiteController@debugEnroll')->middleware('usersession');

Route::get('api/session/set', function(Request $request){
    // Session::put('session', 'working');
    session()->put('session', "working!");
    return "Stored : \"".session()->get('session')."\"";
});

Route::get('api/session/get', function(Request $request){
    // return response()->json(["Test Session", Session::get('session')], 200);
    return response()->json(["Get Session", $request->session()->get('session')], 200);
});

Route::get('api/geo-ip', 'SiteController@testGeoIP');

Route::get('api/', function () { return view('welcome'); });
Route::get('api/csrf/token', function () {
    // return csrf_token();
    return response('', 200);
});

Route::group(['prefix' => 'api/swiftcoder'], function () {
    Route::post('jobs/callback-progress', 'SwiftCoderController@callbackProgress');
    Route::get('jobs/start', 'SwiftCoderController@checkJobStart');
    Route::get('jobs/transfer', 'SwiftCoderController@checkJobTransfer');
    Route::get('jobs/debug', 'SwiftCoderController@debug');
});

Route::group(['prefix' => 'api/2c2p'], function () {
    Route::put('reconcile/{date?}', '_2c2pController@reconcile');
});

Route::group(['prefix' => 'api/site'], function () {

    Route::get('csrf/token', function () {
        // return response()->json(["csrf_token" => csrf_token()], 200);
        $csrf_token = csrf_token();
        return response()->json(["csrf_token" => $csrf_token, "encrypted_csrf_token" => Crypt::encrypt($csrf_token)], 200);
    });

    Route::get('mailTest', 'SiteController@mailTest');
    // Route::get('memberTest', 'SiteController@memberTest');

    Route::get('groups404', 'SiteController@groups404');
    Route::get('groups/{id}/id', 'SiteController@groups_id');
    Route::get('groups/{key}', 'SiteController@groups');
    Route::get('sub_groups/{id}', 'SiteController@sub_groups');

    Route::post('user/login', 'SiteController@login');
    Route::post('user/logout', 'SiteController@logout');
    Route::post('user/register', 'SiteController@register');
    Route::put('user/change-password', 'SiteController@changePasswordOnly')->middleware('usersession');
    Route::post('user/change_password', 'SiteController@change_password');
    Route::post('user/use_old_password', 'SiteController@use_old_password');
    Route::post('user/use_sub_group', 'SiteController@use_sub_group');
    Route::post('user/forgot', 'SiteController@forgot');
    Route::get('user/tax-invoice', 'SiteController@getSessionTaxInvoice');
    Route::post('user/tax-invoice', 'SiteController@updateTaxInvoice')->middleware('usersession');
    Route::post('user/edit_profile', 'SiteController@edit_profile')->middleware('usersession');
    Route::put('user/action/login', 'SiteController@forgetSessionActionLogin')->middleware('usersession');


    Route::get('groups/{groups_id}/courses/search/{keyword?}', 'SiteController@searchCourses');
    Route::get('groups/{groups_id}/courses/filter', 'SiteController@filterCoursesList')->middleware('usersession');
    Route::get('groups/{groups_id}/courses_list', 'SiteController@courses_list');
    Route::get('groups/{groups_id}/categories/{categories_id}/courses', 'SiteController@categories2courses');
    Route::get('groups/{groups_id}/courses_recommended', 'SiteController@courses_recommended');

    Route::get('groups/{groups_id}/live_courses_list', 'SiteController@live_courses_list');
    // Route::get('members/{token}', 'SiteController@members');

    Route::get('my2enroll', 'SiteController@my2enroll')->middleware('usersession');
    Route::get('my2enroll_test', 'SiteController@my2enroll_test');
    Route::get('avatars_list', 'SiteController@avatars_list')->middleware('usersession');
    Route::get('avatars/{id}', 'SiteController@avatars')->middleware('usersession');
    Route::put('members/avatar', 'SiteController@changeAvatar')->middleware('usersession');
    Route::get('configuration', 'SiteController@configuration');
    Route::get('qa', 'SiteController@qa');
    Route::get('groups/{groups_key}/highlights', 'SiteController@highlights');
    Route::get('groups/{groups_id}/categories', 'SiteController@categories');
    Route::get('groups/{groups_key}/courses/{courses_id}', 'SiteController@courses');
    Route::get('groups/{groups_key}/courses_test/{courses_id}', 'SiteController@courses_test');

    // Live
    Route::post('member2live', 'SiteController@member2live');
    

    // Route::get('groups/{groups_key}/courses/{courses_id}/th', 'TestHonnController@courses');

    Route::post('enroll/views', 'SiteController@enrollViews')->middleware('usersession');
    Route::get('enroll/{enroll_id}/quiz/{quiz_id}', 'SiteController@quiz')->middleware('usersession');
    Route::get('enroll/{id}', 'SiteController@enroll')->middleware('usersession');
    Route::get('enroll/courses/{courses_id}', 'SiteController@enrollByCourse')->middleware('usersession');
    Route::get('exam2score/{id}', 'SiteController@exam2score')->middleware('usersession');
    Route::post('enroll2learning', 'SiteController@enroll2learning');
    Route::post('enroll2quiz', 'SiteController@enroll2quiz')->middleware('usersession');
    Route::post('questions2survey', 'SiteController@questions2survey')->middleware('usersession');
    Route::post('questions2answer', 'SiteController@questions2answer')->middleware('usersession');
    Route::post('questions2answer_single', 'SiteController@questions2answer_single')->middleware('usersession');

    Route::get('enroll2topic/{id}/skip/{topics_id}', 'SiteController@enroll2topic_skip')->middleware('usersession');
    Route::get('enroll2topic_live/{id}/skip/{topics_id}', 'SiteController@enroll2topic_live_skip')->middleware('usersession');
    Route::post('enroll2topic_stage', 'SiteController@enroll2topic_stage')->middleware('usersession');
    Route::post('enroll2topic_duration', 'SiteController@enroll2topic_duration')->middleware('usersession');
    Route::post('enroll2topic_live_duration', 'SiteController@enroll2topic_live_duration')->middleware('usersession');
    Route::post('enroll2topic_status', 'SiteController@enroll2topic_status')->middleware('usersession');
    Route::get('enroll2topic/{id}', 'SiteController@enroll2topic')->middleware('usersession');

    Route::get('enroll2summary/{id}', 'SiteController@enroll2summary')->middleware('usersession');
    Route::post('enroll/{id}/certificate', 'SiteController@checkCertificate')->middleware('usersession');
    Route::get('enroll/{id}/certificate/{lang?}', 'SiteController@downloadCertificate')->middleware('usersession');

    Route::get('license_types/{license_types_id}', 'SiteController@getLicenseTypes');
    Route::get('license_types', 'SiteController@getLicenseTypesList');

    Route::post('filter_courses', 'SiteController@filterCourses')->middleware('usersession');

    Route::post('orders', 'SiteController@createOrders');
    Route::get('my2orders', 'SiteController@my2orders')->middleware('usersession');

    // discussion
    Route::post('discussion/send', 'SiteController@discussion_send')->middleware('usersession');
    Route::post('discussion/reply', 'SiteController@discussion_reply')->middleware('usersession');
    Route::get('discussion/groups/{groups_key}/courses/{id}', 'SiteController@discussion_list')->middleware('usersession');
    Route::get('discussion/groups/{groups_key}/courses/{id}/instructors', 'SiteController@discussion_instructors_list');
    Route::put('discussion/{id}/view', 'SiteController@discussion_update_view')->middleware('usersession');
    Route::put('discussion/{id}/like', 'SiteController@discussion_update_like')->middleware('usersession');
    Route::put('discussion/{id}/dislike', 'SiteController@discussion_update_dislike')->middleware('usersession');
    Route::get('discussion/{id}', 'SiteController@discussion_detail')->middleware('usersession');

    // Instructors
    Route::put('instructors/discussion/{id}/read', 'SiteController@discussion_instructors_read');
    Route::post('instructors/discussion/reply', 'SiteController@discussion_instructors_reply')->middleware('userinstructorssession');
    Route::get('instructors/discussion/groups/{groups_key}/courses/{id}', 'SiteController@discussion_instructors_list')->middleware('userinstructorssession');
    Route::put('instructors/discussion/{id}/view', 'SiteController@discussion_update_view')->middleware('userinstructorssession');
    Route::get('instructors/discussion/{id}', 'SiteController@discussion_instructors_detail')->middleware('userinstructorssession');
    Route::post('instructors/groups/{groups_key}/courses/{courses_id}/login', 'SiteController@instructors_login');
    Route::post('instructors/logout', 'SiteController@instructors_logout');
    Route::get('instructors/session', 'SiteController@getSessionUserInstructor');


    /* payment service */
    // Route::post('payments', 'SitePaymentsController@createPayments');
    Route::post('payments/2c2p/result', 'SitePaymentsController@result2c2p');
    // Route::get('payments/result/{token}', 'SitePaymentsController@result');
    // Route::post('payments/require', 'SitePaymentsController@req');
    // Route::get('payments/token/{token}', 'SitePaymentsController@token');
    // Route::put('payments/orders/{orders_id}/update', 'SitePaymentsController@orders_update');
    // Route::get('payments/methods', 'SitePaymentsController@methods');
    // Route::post('payments/orders', 'SitePaymentsController@orders');

    Route::get('courses/{id}/getSlideActive', 'SiteController@getSlideActive')->middleware('usersession');
    Route::get('topics/{id}/stream', 'SiteController@getStream')->middleware('usersession');

    // Videos
    Route::get('videos/{id}/subtitles/file', 'SiteController@getSubtitlesFile')->where('id', '[0-9]+');

    // Bandwidths
    Route::get('bandwidths/current', 'BandwidthsController@currentBandwidth');
    Route::post('bandwidths/current', 'BandwidthsController@createCurrent');
});

// Set OpenUrl
Route::group(['prefix' => 'api/set'], function () {
    Route::get('user/session', 'SetController@getSessionUser');
    Route::get('user/temp/session', 'SetController@getTempSession');
    Route::delete('user/temp/session', 'SetController@forgetTempSession');

    // Route::get('generate-keys', 'SetController@generateKeys');
    // Route::post('single-sign-on', 'SetController@singleSignOn');
    // Route::get('single-sign-on/test', 'SetController@singleSignOnTest');
    // Route::post('single-sign-on/test', 'SetController@singleSignOnTest');

    // Route::get('test-encrypt', 'SetController@testEncrypt');
    // Route::post('test-decrypt', 'SetController@testDecrypt');
    Route::post('dummy-data', 'SetController@dummyData');
    Route::get('debug', 'SetController@debug');
    Route::get('debug-https', 'SetController@debugHttps');

    // E1
    Route::post('login', 'SetController@singleSignOnTest');

    // E1
    Route::post('{group_key}/login', 'SetController@singleSignOnTest');

    // E2
    Route::get('{group_key}/courses', 'SetController@groupsCoursesLists');
    Route::post('{group_key}/courses', 'SetController@groupsCoursesLists');

    // E3
    Route::post('{group_key}/courses/{course_id}', 'SetController@groupsCoursesInfo');
    Route::post('groups/{group_key}/courses/{course_id}', 'SetController@groupsCoursesInfo');

    // E4
    Route::post('{group_key}/courses/{course_id}/download/certificate/{lang?}', 'SetController@downloadCertificate');

    // S2
    Route::post('logout', 'SetController@logout');
});

// Route::post('api/set/openurl/groups/{groups_id}/courses', 'SetController@groupsCoursesLists');

// Route::get('api/certificates/{filename}/preview', 'CertificatesController@preview')->name('certificates-preview');


/* admin service */
Route::post('api/auth/login', 'AuthController@login');
Route::get('api/auth/logout', 'AuthController@logout');
Route::delete('api/auth/temp/session', 'AuthController@forgetTempSession');

Route::group(['middleware' => ['auth:admin']], function () {

    // Manual API
    // Route::post('api/regist/enroll', 'SetController@enroll');
    // Route::get('api/regist/enroll', 'SetController@enrollList');

    // Auth
    Route::put('api/auth', 'AuthController@updateUser');

    //Admins
    Route::post('api/admins/change-password', 'AdminsController@changePassword');
    Route::get('api/admins/targets', 'AdminsController@targets');
    Route::put('api/admins/{id}/status', 'AdminsController@updateStatus');
    Route::resource('api/admins', 'AdminsController');

    //Admins Groups
    Route::get('api/admins_groups/super_user_all', 'AdminsGroupsController@super_user_all');
    Route::get('api/admins_groups/all', 'AdminsGroupsController@all');
    Route::put('api/admins_groups/sort', 'AdminsGroupsController@sort');
    Route::put('api/admins_groups/{id}/status', 'AdminsGroupsController@updateStatus');
    Route::resource('api/admins_groups', 'AdminsGroupsController');

    //Admins Menu
    Route::get('api/admins_menu/all', 'AdminsMenuController@all');
    Route::get('api/admins_menu/all/parent', 'AdminsMenuController@allParent');

    //Configuration
    Route::resource('api/configuration', 'ConfigurationController');

    //Highlights
    Route::post('api/highlights/orders', 'HighlightsController@orders');
    Route::put('api/highlights/sort', 'HighlightsController@sort');
    Route::put('api/highlights/{id}/status', 'HighlightsController@updateStatus');
    Route::resource('api/highlights', 'HighlightsController');

    //Categories
    Route::get('api/categories/all_categories', 'CategoriesController@all_categories');
    Route::post('api/categories/orders', 'CategoriesController@orders');
    Route::put('api/categories/sort', 'CategoriesController@sort');
    Route::get('api/categories/all', 'CategoriesController@all');
    Route::put('api/categories/{id}/status', 'CategoriesController@updateStatus');
    Route::resource('api/categories', 'CategoriesController');

    //Instructors
    Route::post('api/instructors/orders', 'InstructorsController@orders');
    Route::put('api/instructors/sort', 'InstructorsController@sort');
    Route::get('api/instructors/all', 'InstructorsController@all');
    Route::put('api/instructors/{id}/status', 'InstructorsController@updateStatus');
    Route::resource('api/instructors', 'InstructorsController');

    //Groups
    Route::get('api/groups/{groups_id}/sub_groups', 'GroupsController@sub_groups');
    Route::get('api/groups/all_groups', 'GroupsController@all_groups');
    Route::get('api/groups/all', 'GroupsController@all');
    Route::get('api/groups/{groups_id}/courses', 'GroupsController@courses');
    Route::post('api/groups/orders', 'GroupsController@orders');
    Route::put('api/groups/sort', 'GroupsController@sort');
    Route::put('api/groups/{id}/status', 'GroupsController@updateStatus');
    Route::resource('api/groups', 'GroupsController');

    //Courses
    Route::delete('api/courses/{courses_id}/members', 'CoursesController@detachMembers');
    Route::post('api/courses/{courses_id}/members-pre-approved/import', 'CoursesController@importPreApprovedMembers');
    Route::post('api/courses/{courses_id}/members/import', 'CoursesController@importMembers');
    Route::get('api/courses/{courses_id}/members', 'CoursesController@getMembers');
    Route::get('api/courses/{courses_id}/members-pre-approved', 'CoursesController@getMembersPreApproved');
    Route::get('api/courses/{courses_id}/overview', 'CoursesController@overview');
    Route::post('api/courses/orders', 'CoursesController@orders');
    Route::put('api/courses/sort', 'CoursesController@sort');
    Route::get('api/courses/{courses_id}/topics', 'CoursesController@topics');
    Route::get('api/courses/level_public', 'CoursesController@level_public');
    Route::get('api/courses/all', 'CoursesController@all');
    Route::get('api/courses/all/except/{id}', 'CoursesController@allExcept');
    Route::get('api/courses/all/in-groups/{id?}', 'CoursesController@allInGroups');
    Route::put('api/courses/{id}/status', 'CoursesController@updateStatus');
    Route::resource('api/courses', 'CoursesController');

    Route::get('api/courses/{courses_id}/slides', 'CoursesController@slidesGroups');
    Route::put('api/courses/{courses_id}/sync-slide', 'CoursesController@updateSyncSlide');
    Route::get('api/courses/{courses_id}/slides/{slides_order}/previous', 'CoursesController@previousSlides')->where('courses_id', '[0-9]+')->where('slides_order', '[0-9]+');
    Route::get('api/courses/{courses_id}/slides/{slides_order}/next', 'CoursesController@nextSlides')->where('courses_id', '[0-9]+')->where('slides_order', '[0-9]+');
    Route::get('api/courses/{courses_id}/slidesActive', 'CoursesController@slidesActive');
    Route::get('api/courses/{courses_id}/firstSlide', 'CoursesController@firstSlide');

    Route::get('api/live/resetLiveControl', 'LiveStreamsController@resetLiveControl');

    // Route::get('api/live/create_smil', 'LiveStreamsController@create_smil');
    Route::get('api/live/{id}/getBroadcastSignal', 'LiveStreamsController@getBroadcastSignal')->where('id', '[0-9]+');

    // Live control
    Route::get('api/live/{id}/getLiveResults', 'LiveStreamsController@getLiveResults')->where('id', '[0-9]+');
    Route::put('api/live/{id}/updateLiveResults', 'LiveStreamsController@updateLiveResults')->where('id', '[0-9]+');

    Route::put('api/live/{id}/toggleStreamingPause', 'LiveStreamsController@toggleStreamingPause')->where('id', '[0-9]+');
    Route::put('api/live/{id}/toggleStreamingStatus', 'LiveStreamsController@toggleStreamingStatus')->where('id', '[0-9]+');
    Route::put('api/live/{id}/update_on_demand', 'LiveStreamsController@update_on_demand')->where('id', '[0-9]+');
    Route::get('api/live/{id}/incomeDuration', 'LiveStreamsController@getIncomeDuration')->where('id', '[0-9]+');
    Route::get('api/live/{id}/incomingStream', 'LiveStreamsController@getIncomingStream')->where('id', '[0-9]+');
    Route::get('api/live/{id}/incomingStreamDuration', 'LiveStreamsController@getIncomingStreamDuration')->where('id', '[0-9]+');
    Route::get('api/live/{id}/startRecord', 'LiveStreamsController@startRecord')->where('id', '[0-9]+');
    Route::get('api/live/{id}/stopRecord', 'LiveStreamsController@stopRecord')->where('id', '[0-9]+');
    Route::get('api/live_event/{id}/status', 'LiveStreamsController@live_event_status')->where('id', '[0-9]+');

    //QA
    Route::post('api/qa/orders', 'QAController@orders');
    Route::put('api/qa/sort', 'QAController@sort');
    Route::get('api/qa/all', 'QAController@all');
    Route::put('api/qa/{id}/status', 'QAController@updateStatus');
    Route::resource('api/qa', 'QAController');

    //Topics
    Route::put('api/topics/{id}/live/url', 'TopicsController@generateLiveUrl');
    Route::get('api/topics/{topics_id}/getSlides', 'TopicsController@getSlides');
    Route::post('api/topics/orders', 'TopicsController@orders');
    Route::put('api/topics/sort', 'TopicsController@sort');
    Route::get('api/topics/all', 'TopicsController@all');
    Route::get('api/topics/{topics_id}/topics2parents', 'TopicsController@topics2parents');
    Route::get('api/topics/{topics_id}/topicsHasParents', 'TopicsController@topicsHasParents');
    Route::get('api/topics/{topics_id}/parents', 'TopicsController@parents');
    Route::get('/api/topics/{courses_id}/children', 'TopicsController@children');
    Route::put('api/topics/{id}/status', 'TopicsController@updateStatus');
    Route::resource('api/topics', 'TopicsController');

    //Slides
    Route::post('/api/slides/orders', 'SlidesController@orders');
    Route::put('/api/slides/sort', 'SlidesController@sort');
    Route::get('/api/slides/all', 'SlidesController@all');
    Route::get('/api/slides/{slides_id}/getByTopics', 'SlidesController@getByTopics');
    Route::put('/api/slides/{slides_id}/slide_active', 'SlidesController@updateSlideActive');
    Route::put('api/slides/{id}/status', 'SlidesController@updateStatus');
    Route::resource('/api/slides', 'SlidesController');
    Route::post('/api/slides/convert', 'SlidesController@slidesConvertCreate');
    Route::get('/api/courses/{courses_id}/slidesForSync', 'CoursesController@slidesForSync');

    //Slides Times
    Route::post('/api/slides_times/orders', 'SlidesTimesController@orders');
    Route::put('/api/slides_times/sort', 'SlidesTimesController@sort');
    Route::get('/api/slides_times/all', 'SlidesTimesController@all');
    Route::resource('/api/slides_times', 'SlidesTimesController');

    //Documents
    Route::get('api/courses/{courses_id}/discussions', 'CoursesController@discussions');
    Route::get('api/courses/{courses_id}/documents', 'CoursesController@documents');
    Route::post('api/documents/orders', 'DocumentsController@orders');
    Route::put('api/documents/sort', 'DocumentsController@sort');
    Route::get('api/documents/all', 'DocumentsController@all');
    Route::put('api/documents/{id}/status', 'DocumentsController@updateStatus');
    Route::resource('api/documents', 'DocumentsController');

    //Quiz
    Route::get('api/courses/{courses_id}/quiz', 'CoursesController@quiz');
    Route::post('api/quiz/orders', 'QuizController@orders');
    Route::put('api/quiz/sort', 'QuizController@sort');
    Route::get('api/quiz/{courses_id}/courses', 'QuizController@quiz2courses');
    Route::get('api/quiz/all', 'QuizController@all');
    Route::put('api/quiz/{id}/status', 'QuizController@updateStatus');
    Route::resource('api/quiz', 'QuizController');

    //Questions
    Route::get('api/quiz/{quiz_id}/questions', 'QuizController@questions');
    Route::post('api/questions/orders', 'QuestionsController@orders');
    Route::put('api/questions/sort', 'QuestionsController@sort');
    Route::get('api/questions/all', 'QuestionsController@all');
    Route::put('api/questions/{id}/status', 'QuestionsController@updateStatus');
    Route::resource('api/questions', 'QuestionsController');

    //Answer
    Route::get('api/questions/{questions_id}/answer', 'QuestionsController@answer');
    Route::post('api/answer/orders', 'AnswerController@orders');
    Route::put('api/answer/sort', 'AnswerController@sort');
    Route::get('api/answer/all', 'AnswerController@all');
    Route::resource('api/answer', 'AnswerController');

    //Members
    Route::get('api/members/{groups_key}/example/file', 'MembersController@downloadExampleFileUpload');
    Route::put('api/members/{members_id}/approve', 'MembersController@approve');
    Route::put('api/members/{members_id}/reject', 'MembersController@reject');
    Route::get('api/members/export', 'MembersController@export');
    Route::put('api/members/{id}/status', 'MembersController@updateStatus');
    Route::get('api/members/all', 'MembersController@all');
    Route::resource('api/members', 'MembersController');

    //Members Pre-Approved
    Route::get('api/members_pre_approved/{groups_key}/example/file', 'MembersPreApprovedController@downloadExampleFileUpload');
    Route::put('api/members_pre_approved/{members_id}/approve', 'MembersPreApprovedController@approve');
    Route::put('api/members_pre_approved/{members_id}/reject', 'MembersPreApprovedController@reject');
    Route::get('api/members_pre_approved/export', 'MembersPreApprovedController@export');
    Route::resource('api/members_pre_approved', 'MembersPreApprovedController');

    //Videos
    Route::get('api/videos/{videos_id}/subtitles', 'VideosController@subtitles');
    Route::post('api/videos/createVideo', 'VideosController@createVideo');
    Route::put('api/videos/sort', 'VideosController@sort');
    Route::resource('api/videos', 'VideosController');

    //Transcodings
    Route::post('api/transcodings/video', 'TranscodingsController@createByBitrates');
    Route::put('api/transcodings/sort', 'TranscodingsController@sort');
    Route::resource('api/transcodings', 'TranscodingsController');

    //Usage Statistic
    Route::get('api/usage_statistic/export', 'UsageStatisticController@export');
    Route::get('api/courses/{courses_id}/usage_statistic', 'CoursesController@usage_statistic');
    Route::resource('api/usage_statistic', 'UsageStatisticController');

    //Super Users
    Route::put('api/super_users/{id}/status', 'SuperUsersController@updateStatus');
    Route::resource('api/super_users', 'SuperUsersController');

    //SubGroups
    Route::get('api/sub_groups/{sub_groups_id}/domains', 'SubGroupsController@domains');
    Route::get('api/sub_groups/{id}/level_groups', 'SubGroupsController@level_groups');
    Route::post('api/sub_groups/orders', 'SubGroupsController@orders');
    Route::put('api/sub_groups/sort', 'SubGroupsController@sort');
    Route::get('api/sub_groups/all', 'SubGroupsController@all');
    Route::put('api/sub_groups/{id}/status', 'SubGroupsController@updateStatus');
    Route::resource('api/sub_groups', 'SubGroupsController');

    //Class Rooms
    Route::delete('api/classrooms/{classrooms_id}/members', 'ClassRoomsController@detachMembers');
    Route::post('api/classrooms/{classrooms_id}/members-pre-approved/import', 'ClassRoomsController@importPreApprovedMembers');
    Route::post('api/classrooms/{classrooms_id}/members/import', 'ClassRoomsController@importMembers');
    Route::get('api/classrooms/{classrooms_id}/members', 'ClassRoomsController@getMembers');
    Route::get('api/classrooms/{classrooms_id}/members-pre-approved', 'ClassRoomsController@getMembersPreApproved');
    Route::post('api/classrooms/orders', 'ClassRoomsController@orders');
    Route::put('api/classrooms/sort', 'ClassRoomsController@sort');
    Route::get('api/classrooms/all', 'ClassRoomsController@all');
    Route::put('api/classrooms/{id}/status', 'ClassRoomsController@updateStatus');
    Route::resource('api/classrooms', 'ClassRoomsController');

    //Stats
    Route::get('api/stats/export/questions', 'StatsController@export_questions');
    Route::get('api/stats/export/course', 'StatsController@export_course');
    Route::get('api/stats/export/quiz', 'StatsController@export_quiz');
    Route::get('api/stats/export/enroll', 'StatsController@export_enroll');
    Route::get('api/stats/info', 'StatsController@info');
    Route::get('api/stats/enroll', 'StatsController@enroll');
    Route::get('api/stats/log', 'StatsController@log');
    Route::get('api/stats/device', 'StatsController@device');
    Route::get('api/stats/all', 'StatsController@all');
    Route::get('api/stats/state', 'StatsController@state');
    Route::get('api/stats/city', 'StatsController@city');
    Route::get('api/stats/country', 'StatsController@country');
    Route::get('api/stats/info', 'StatsController@info');
    Route::get('api/stats/stats', 'StatsController@stats');
    Route::get('api/stats/quiz', 'StatsController@quiz');
    Route::get('api/stats/courses', 'StatsController@courses');
    Route::get('api/stats/learning', 'StatsController@learning');
    Route::get('api/stats/{id}/course', 'StatsController@course');

    //Stats Live
    Route::get('api/stats_live/export/questions', 'StatsLiveController@export_questions');
    Route::get('api/stats_live/export/course', 'StatsLiveController@export_course');
    Route::get('api/stats_live/export/quiz', 'StatsLiveController@export_quiz');
    Route::get('api/stats_live/export/enroll', 'StatsLiveController@export_enroll');
    Route::get('api/stats_live/info', 'StatsLiveController@info');
    Route::get('api/stats_live/enroll', 'StatsLiveController@enroll');
    Route::get('api/stats_live/log', 'StatsLiveController@log');
    Route::get('api/stats_live/device', 'StatsLiveController@device');
    Route::get('api/stats_live/all', 'StatsLiveController@all');
    Route::get('api/stats_live/state', 'StatsLiveController@state');
    Route::get('api/stats_live/city', 'StatsLiveController@city');
    Route::get('api/stats_live/country', 'StatsLiveController@country');
    Route::get('api/stats_live/info', 'StatsLiveController@info');
    Route::get('api/stats_live/stats_live', 'StatsLiveController@stats_live');
    Route::get('api/stats_live/quiz', 'StatsLiveController@quiz');
    Route::get('api/stats_live/courses', 'StatsLiveController@courses');
    Route::get('api/stats_live/learning', 'StatsLiveController@learning');
    Route::get('api/stats_live/{id}/course', 'StatsLiveController@course');

    //Super Users Groups
    Route::delete('api/level_groups/{level_groups_id}/members', 'LevelGroupsController@detachMembers');
    Route::put('api/level_groups/{level_groups_id}/pre-approved', 'LevelGroupsController@checkPreApproved');
    Route::post('api/level_groups/{level_groups_id}/members-pre-approved/import', 'LevelGroupsController@importPreApprovedMembers');
    Route::post('api/level_groups/{level_groups_id}/members/import', 'LevelGroupsController@importMembers');
    Route::get('api/level_groups/{level_groups_id}/members', 'LevelGroupsController@getMembers');
    Route::get('api/level_groups/{level_groups_id}/members-pre-approved', 'LevelGroupsController@getMembersPreApproved');
    Route::get('api/level_groups/{level_groups_id}/members-not-approved', 'LevelGroupsController@getMembersNotApproved');
    Route::get('api/level_groups/waiting_groups', 'LevelGroupsController@waiting_groups');
    Route::get('api/level_groups/access_groups', 'LevelGroupsController@access_groups');
    Route::get('api/level_groups/sub_groups', 'LevelGroupsController@sub_groups');
    Route::get('api/level_groups/all', 'LevelGroupsController@all');
    Route::get('api/level_groups/all/sub_groups', 'LevelGroupsController@allBySubGroups');
    Route::post('api/level_groups/orders', 'LevelGroupsController@orders');
    Route::put('api/level_groups/sort', 'LevelGroupsController@sort');
    Route::resource('api/level_groups', 'LevelGroupsController');

    //Certificates
    Route::get('api/certificates/{filename}/preview/image', 'CertificatesController@getPreviewImage')->name('certificates-preview-image');
    Route::delete('api/certificates/{filename}/preview', 'CertificatesController@deletePreview');
    Route::get('api/certificates/{filename}/preview', 'CertificatesController@preview')->name('certificates-preview');
    Route::post('api/certificates/preview/courses', 'CertificatesController@createPreviewByCourse');
    Route::post('api/certificates/preview', 'CertificatesController@createPreview');
    Route::post('api/certificates/orders', 'CertificatesController@orders');
    Route::put('api/certificates/sort', 'CertificatesController@sort');
    Route::get('api/certificates/all', 'CertificatesController@all');
    Route::put('api/certificates/{id}/status', 'CertificatesController@updateStatus');
    Route::resource('api/certificates', 'CertificatesController');

    //Domains
    Route::put('api/domains/sort', 'DomainsController@sort');
    Route::resource('api/domains', 'DomainsController');

    //License Types
    Route::post('api/license_types/orders', 'LicenseTypesController@orders');
    Route::put('api/license_types/sort', 'LicenseTypesController@sort');
    Route::get('api/license_types/all', 'LicenseTypesController@all');
    Route::resource('api/license_types', 'LicenseTypesController');

    //Images
    Route::get('api/images/all_images', 'ImagesController@all_images');
    Route::post('api/images/orders', 'ImagesController@orders');
    Route::put('api/images/sort', 'ImagesController@sort');
    Route::get('api/images/all', 'ImagesController@all');
    Route::resource('api/images', 'ImagesController');

    //QuestionnairePacks
    Route::get('api/groups/{groups_id}/questionnaire_packs', 'GroupsController@questionnaire_packs');
    Route::get('api/groups/{groups_id}/questionnaire_packs', 'GroupsController@questionnaire_packs');
    Route::post('api/questionnaire_packs/orders', 'QuestionnairePacksController@orders');
    Route::put('api/questionnaire_packs/sort', 'QuestionnairePacksController@sort');
    Route::get('api/questionnaire_packs/{courses_id}/courses', 'QuestionnairePacksController@questionnaire_packs2courses');
    Route::get('api/questionnaire_packs/all', 'QuestionnairePacksController@all');
    Route::put('api/questionnaire_packs/{id}/status', 'QuestionnairePacksController@updateStatus');
    Route::resource('api/questionnaire_packs', 'QuestionnairePacksController');

    //Questionnaires
    Route::get('api/questionnaire_packs/{questionnaire_packs_id}/questionnaires', 'QuestionnairePacksController@questionnaires');
    Route::post('api/questionnaires/orders', 'QuestionnairesController@orders');
    Route::put('api/questionnaires/sort', 'QuestionnairesController@sort');
    Route::get('api/questionnaires/all', 'QuestionnairesController@all');
    Route::put('api/questionnaires/{id}/status', 'QuestionnairesController@updateStatus');
    Route::resource('api/questionnaires', 'QuestionnairesController');

    //QuestionnaireChoices
    Route::get('api/questionnaires/{questions_id}/questionnaire_choices', 'QuestionnairesController@questionnaire_choices');
    Route::post('api/questionnaire_choices/orders', 'QuestionnaireChoicesController@orders');
    Route::put('api/questionnaire_choices/sort', 'QuestionnaireChoicesController@sort');
    Route::get('api/questionnaire_choices/all', 'QuestionnaireChoicesController@all');
    Route::resource('api/questionnaire_choices', 'QuestionnaireChoicesController');

    // My Profile
    Route::get('api/my_profile/self', 'MyProfileController@showSelf');
    Route::put('api/my_profile/self', 'MyProfileController@updateSelf');
    Route::post('api/my_profile/self/change-access', 'MyProfileController@changeAccess');
    // Route::resource('api/my_profile', 'MyProfileController');

    // Methods
    Route::get('/api/methods/all', 'MethodsController@all');
    Route::put('api/methods/sort', 'MethodsController@sort');
    Route::put('api/methods/{id}/status', 'MethodsController@updateStatus');
    Route::resource('api/methods', 'MethodsController');

    // Orders
    Route::get('/api/orders/all', 'OrdersController@all');
    Route::put('api/orders/sort', 'OrdersController@sort');
    Route::resource('api/orders', 'OrdersController');

    // Payments
    Route::put('api/payments/{id}/is_canceled', 'PaymentsController@updateIsCanceled');
    Route::get('api/payments/{id}/reconcile/file', 'PaymentsController@downloadReconcileFile');
    Route::put('api/payments/sort', 'PaymentsController@sort');
    Route::resource('api/payments', 'PaymentsController');

    // Jobs
    Route::put('api/jobs/sort', 'JobsController@sort');
    Route::resource('api/jobs', 'JobsController');

    // CronJobs
    Route::get('api/cron_jobs/{code}/monitor', 'CronJobsController@monitor');

    // Discussions
    Route::put('api/discussions/{id}/read', 'DiscussionsController@read');
    Route::put('api/discussions/{id}/is_reject', 'DiscussionsController@updateIsReject');
    Route::put('api/discussions/{id}/is_public', 'DiscussionsController@updateIsPublic');
    Route::put('api/discussions/{id}/is_sent_instructor', 'DiscussionsController@updateIsSentInstructor');
    Route::get('api/courses/{courses_id}/discussions', 'CoursesController@discussions');
    Route::post('api/discussions/send', 'DiscussionsController@send');
    Route::get('api/discussions/all/{courses_id}/except/{except_id}', 'DiscussionsController@allExcept');
    Route::post('api/discussions/orders', 'DiscussionsController@orders');
    Route::put('api/discussions/sort', 'DiscussionsController@sort');
    Route::get('api/discussions/all', 'DiscussionsController@all');
    Route::put('api/discussions/{id}/status', 'DiscussionsController@updateStatus');
    Route::resource('api/discussions', 'DiscussionsController');

    // Subtitles
    Route::post('api/subtitles/videos/{courses_id}/upload', 'SubtitlesController@uploadFile');
    Route::get('api/subtitles/videos/{id}/file/download', 'SubtitlesController@downloadFile')->where('id', '[0-9]+');
    Route::get('api/subtitles/videos/{id}/file', 'SubtitlesController@getFile')->where('id', '[0-9]+');
    Route::get('api/subtitles/videos/{video_id}', 'SubtitlesController@getByVideo')->where('video_id', '[0-9]+');
    Route::post('api/subtitles/videos/{video_id}', 'SubtitlesController@createByVideo')->where('video_id', '[0-9]+');
    Route::resource('api/subtitles', 'SubtitlesController');

    Route::get('api/quiz_report', 'QuizReportController@quiz_report');

});

