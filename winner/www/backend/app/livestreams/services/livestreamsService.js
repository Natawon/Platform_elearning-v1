angular.module('newApp').factory('livestreamFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {
    return {
        slides2groups: function(theLive, theSlidesGroups, queryString) {
            return $http.get(settingsFactory.get('courses') + '/' + theLive.id + '/slides_groups/'+ theSlidesGroups +'/slides?' + queryString);
        },
        create_slides_times: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times'),
                    method: "POST",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        update_slides_times: function(theSlidesTimes) {
            return $http(
                {
                    url: settingsFactory.get('slides_times') + '/' + theSlidesTimes.id,
                    method: "PUT",
                    data: theSlidesTimes,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        update_on_demand: function(theLive) {
            return $http(
                {
                    url: settingsFactory.get('courses') + '/' + theLive.id + '/update_on_demand',
                    method: "PUT",
                    data: theLive,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        slides_groups: function(theLive, queryString) {
            return $http.get(settingsFactory.get('courses') + '/' + theLive.id + '/slides_groups?' + queryString);
        },
        delete_slides_times: function(theSlidesTimes) {
            return $http.delete(settingsFactory.get('slides_times') + '/' + theSlidesTimes.id);
        },
        incomingStream: function(theLive) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/incomingStream');
        },
        incomingStreamDuration: function(theLive, queryString) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/incomingStreamDuration' + '?' + queryString);
        },
        incomeDuration: function(theLive) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/incomeDuration');
        },
        startRecord: function(theLive, queryString) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/startRecord' + '?' + queryString);
        },
        stopRecord: function(theLive, queryString) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/stopRecord' + '?' + queryString);
        },
        toggleStreamingStatus: function(theLive) {
            return $http(
                {
                    url: settingsFactory.get('live') + '/' + theLive.id + '/toggleStreamingStatus',
                    method: "PUT",
                    data: theLive,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        toggleStreamingPause: function(theLive) {
            return $http(
                {
                    url: settingsFactory.get('live') + '/' + theLive.id + '/toggleStreamingPause',
                    method: "PUT",
                    data: theLive,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        checkRecordFile: function(theLive) {
            return $http.get(settingsFactory.get('internal') + '/check-record-file.php?filename=' + theLive.streaming_record_filename + '&dir_name=' + theLive.streaming_record_part);
        },
        copyRecordedFile: function(theLive) {
            return $http(
                {
                    url: settingsFactory.get('sc_videos') + '/copy-record-file.php',
                    method: "POST",
                    data: theLive,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        },
        checkOriginalFile: function(queryString) {
            return $http.get(settingsFactory.get('sc_videos') + '/check-original-file.php?' + queryString);
        },
        getBroadcastSignal: function(theLive) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.id + '/getBroadcastSignal');
        },
        getLiveResults: function(theLive) {
            return $http.get(settingsFactory.get('live') + '/' + theLive.topic_id + '/getLiveResults');
        },
        updateLiveResults: function(theLive) {
            return $http(
                {
                    url: settingsFactory.get('live') + '/' + theLive.topic_id + '/updateLiveResults',
                    method: "PUT",
                    data: theLive,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }
            );
        }
    }
}]);