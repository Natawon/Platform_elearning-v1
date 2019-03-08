var _myprofile = function() {
    var getCoursesList = function(page, search) {
        return $.ajax({
            method: 'GET',
            // url: 'https://elearning.set.or.th/api/site/my2enroll_test?page=' + page + '&search=' + search,
            url: URL_API+'/site/my2enroll?page=' + page + '&search=' + search,
            contentType: 'application/json',
            dataType: 'json',
            // data: JSON.stringify(data),
            success: function(result){

            }
        });
    }

    var getCourse = function(groups_key, courses_id) {
        return $.ajax({
            method: 'GET',
            // url: 'https://elearning.set.or.th/api/site/my2enroll_test?page=' + page + '&search=' + search,
            url: URL_API+'/site/groups/' + groups_key + '/courses/' + courses_id,
            contentType: 'application/json',
            dataType: 'json',
            // data: JSON.stringify(data),
            success: function(result){

            }
        });
    }

    return {

        getCoursesList: function(data, search) {
            return getCoursesList(data, search);
        },
        getCourse: function(id, courses_id) {
            return getCourse(id, courses_id);
        }
    };

}();