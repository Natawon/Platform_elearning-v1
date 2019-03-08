
angular.module('newApp').factory('stats_liveFactory', ['$http', 'settingsFactory', function ($http, settingsFactory) {

    var stats_live = {};

    var canvasExam = null;
    var canvasExamContext = null;
    var chartExam = null;

    var canvasCompare = null;
    var canvasCompareContext = null;
    var chartCompare = null;

    var canvasPrePost = null;
    var canvasPrePostContext = null;
    var chartPrePost = null;

    var canvasNotPass = null;
    var canvasNotPassContext = null;
    var chartNotPass = null;

    var canvasPass = null;
    var canvasPassContext = null;
    var chartPass = null;

    var canvasLearning = null;
    var canvasLearningContext = null;
    var chartLearning = null;

    var canvasMobile = null;
    var canvasMobileContext = null;
    var chartMobile = null;

    var canvasDesktop = null;
    var canvasDesktopContext = null;
    var chartDesktop = null;

    var canvasLogEvent = null;
    var canvasLogEventContext = null;
    var chartLogEvent = null;

    var map;
    var mapData;
    /**** MAP ****/
    var latlong = {};
    latlong["AD"] = {
        "latitude": 42.5,
        "longitude": 1.5
    };
    latlong["AE"] = {
        "latitude": 24,
        "longitude": 54
    };
    latlong["AF"] = {
        "latitude": 33,
        "longitude": 65
    };
    latlong["AG"] = {
        "latitude": 17.05,
        "longitude": -61.8
    };
    latlong["AI"] = {
        "latitude": 18.25,
        "longitude": -63.1667
    };
    latlong["AL"] = {
        "latitude": 41,
        "longitude": 20
    };
    latlong["AM"] = {
        "latitude": 40,
        "longitude": 45
    };
    latlong["AN"] = {
        "latitude": 12.25,
        "longitude": -68.75
    };
    latlong["AO"] = {
        "latitude": -12.5,
        "longitude": 18.5
    };
    latlong["AP"] = {
        "latitude": 35,
        "longitude": 105
    };
    latlong["AQ"] = {
        "latitude": -90,
        "longitude": 0
    };
    latlong["AR"] = {
        "latitude": -34,
        "longitude": -64
    };
    latlong["AS"] = {
        "latitude": -14.3333,
        "longitude": -170
    };
    latlong["AT"] = {
        "latitude": 47.3333,
        "longitude": 13.3333
    };
    latlong["AU"] = {
        "latitude": -27,
        "longitude": 133
    };
    latlong["AW"] = {
        "latitude": 12.5,
        "longitude": -69.9667
    };
    latlong["AZ"] = {
        "latitude": 40.5,
        "longitude": 47.5
    };
    latlong["BA"] = {
        "latitude": 44,
        "longitude": 18
    };
    latlong["BB"] = {
        "latitude": 13.1667,
        "longitude": -59.5333
    };
    latlong["BD"] = {
        "latitude": 24,
        "longitude": 90
    };
    latlong["BE"] = {
        "latitude": 50.8333,
        "longitude": 4
    };
    latlong["BF"] = {
        "latitude": 13,
        "longitude": -2
    };
    latlong["BG"] = {
        "latitude": 43,
        "longitude": 25
    };
    latlong["BH"] = {
        "latitude": 26,
        "longitude": 50.55
    };
    latlong["BI"] = {
        "latitude": -3.5,
        "longitude": 30
    };
    latlong["BJ"] = {
        "latitude": 9.5,
        "longitude": 2.25
    };
    latlong["BM"] = {
        "latitude": 32.3333,
        "longitude": -64.75
    };
    latlong["BN"] = {
        "latitude": 4.5,
        "longitude": 114.6667
    };
    latlong["BO"] = {
        "latitude": -17,
        "longitude": -65
    };
    latlong["BR"] = {
        "latitude": -10,
        "longitude": -55
    };
    latlong["BS"] = {
        "latitude": 24.25,
        "longitude": -76
    };
    latlong["BT"] = {
        "latitude": 27.5,
        "longitude": 90.5
    };
    latlong["BV"] = {
        "latitude": -54.4333,
        "longitude": 3.4
    };
    latlong["BW"] = {
        "latitude": -22,
        "longitude": 24
    };
    latlong["BY"] = {
        "latitude": 53,
        "longitude": 28
    };
    latlong["BZ"] = {
        "latitude": 17.25,
        "longitude": -88.75
    };
    latlong["CA"] = {
        "latitude": 54,
        "longitude": -100
    };
    latlong["CC"] = {
        "latitude": -12.5,
        "longitude": 96.8333
    };
    latlong["CD"] = {
        "latitude": 0,
        "longitude": 25
    };
    latlong["CF"] = {
        "latitude": 7,
        "longitude": 21
    };
    latlong["CG"] = {
        "latitude": -1,
        "longitude": 15
    };
    latlong["CH"] = {
        "latitude": 47,
        "longitude": 8
    };
    latlong["CI"] = {
        "latitude": 8,
        "longitude": -5
    };
    latlong["CK"] = {
        "latitude": -21.2333,
        "longitude": -159.7667
    };
    latlong["CL"] = {
        "latitude": -30,
        "longitude": -71
    };
    latlong["CM"] = {
        "latitude": 6,
        "longitude": 12
    };
    latlong["CN"] = {
        "latitude": 35,
        "longitude": 105
    };
    latlong["CO"] = {
        "latitude": 4,
        "longitude": -72
    };
    latlong["CR"] = {
        "latitude": 10,
        "longitude": -84
    };
    latlong["CU"] = {
        "latitude": 21.5,
        "longitude": -80
    };
    latlong["CV"] = {
        "latitude": 16,
        "longitude": -24
    };
    latlong["CX"] = {
        "latitude": -10.5,
        "longitude": 105.6667
    };
    latlong["CY"] = {
        "latitude": 35,
        "longitude": 33
    };
    latlong["CZ"] = {
        "latitude": 49.75,
        "longitude": 15.5
    };
    latlong["DE"] = {
        "latitude": 51,
        "longitude": 9
    };
    latlong["DJ"] = {
        "latitude": 11.5,
        "longitude": 43
    };
    latlong["DK"] = {
        "latitude": 56,
        "longitude": 10
    };
    latlong["DM"] = {
        "latitude": 15.4167,
        "longitude": -61.3333
    };
    latlong["DO"] = {
        "latitude": 19,
        "longitude": -70.6667
    };
    latlong["DZ"] = {
        "latitude": 28,
        "longitude": 3
    };
    latlong["EC"] = {
        "latitude": -2,
        "longitude": -77.5
    };
    latlong["EE"] = {
        "latitude": 59,
        "longitude": 26
    };
    latlong["EG"] = {
        "latitude": 27,
        "longitude": 30
    };
    latlong["EH"] = {
        "latitude": 24.5,
        "longitude": -13
    };
    latlong["ER"] = {
        "latitude": 15,
        "longitude": 39
    };
    latlong["ES"] = {
        "latitude": 40,
        "longitude": -4
    };
    latlong["ET"] = {
        "latitude": 8,
        "longitude": 38
    };
    latlong["EU"] = {
        "latitude": 47,
        "longitude": 8
    };
    latlong["FI"] = {
        "latitude": 62,
        "longitude": 26
    };
    latlong["FJ"] = {
        "latitude": -18,
        "longitude": 175
    };
    latlong["FK"] = {
        "latitude": -51.75,
        "longitude": -59
    };
    latlong["FM"] = {
        "latitude": 6.9167,
        "longitude": 158.25
    };
    latlong["FO"] = {
        "latitude": 62,
        "longitude": -7
    };
    latlong["FR"] = {
        "latitude": 46,
        "longitude": 2
    };
    latlong["GA"] = {
        "latitude": -1,
        "longitude": 11.75
    };
    latlong["GB"] = {
        "latitude": 54,
        "longitude": -2
    };
    latlong["GD"] = {
        "latitude": 12.1167,
        "longitude": -61.6667
    };
    latlong["GE"] = {
        "latitude": 42,
        "longitude": 43.5
    };
    latlong["GF"] = {
        "latitude": 4,
        "longitude": -53
    };
    latlong["GH"] = {
        "latitude": 8,
        "longitude": -2
    };
    latlong["GI"] = {
        "latitude": 36.1833,
        "longitude": -5.3667
    };
    latlong["GL"] = {
        "latitude": 72,
        "longitude": -40
    };
    latlong["GM"] = {
        "latitude": 13.4667,
        "longitude": -16.5667
    };
    latlong["GN"] = {
        "latitude": 11,
        "longitude": -10
    };
    latlong["GP"] = {
        "latitude": 16.25,
        "longitude": -61.5833
    };
    latlong["GQ"] = {
        "latitude": 2,
        "longitude": 10
    };
    latlong["GR"] = {
        "latitude": 39,
        "longitude": 22
    };
    latlong["GS"] = {
        "latitude": -54.5,
        "longitude": -37
    };
    latlong["GT"] = {
        "latitude": 15.5,
        "longitude": -90.25
    };
    latlong["GU"] = {
        "latitude": 13.4667,
        "longitude": 144.7833
    };
    latlong["GW"] = {
        "latitude": 12,
        "longitude": -15
    };
    latlong["GY"] = {
        "latitude": 5,
        "longitude": -59
    };
    latlong["HK"] = {
        "latitude": 22.25,
        "longitude": 114.1667
    };
    latlong["HM"] = {
        "latitude": -53.1,
        "longitude": 72.5167
    };
    latlong["HN"] = {
        "latitude": 15,
        "longitude": -86.5
    };
    latlong["HR"] = {
        "latitude": 45.1667,
        "longitude": 15.5
    };
    latlong["HT"] = {
        "latitude": 19,
        "longitude": -72.4167
    };
    latlong["HU"] = {
        "latitude": 47,
        "longitude": 20
    };
    latlong["ID"] = {
        "latitude": -5,
        "longitude": 120
    };
    latlong["IE"] = {
        "latitude": 53,
        "longitude": -8
    };
    latlong["IL"] = {
        "latitude": 31.5,
        "longitude": 34.75
    };
    latlong["IN"] = {
        "latitude": 20,
        "longitude": 77
    };
    latlong["IO"] = {
        "latitude": -6,
        "longitude": 71.5
    };
    latlong["IQ"] = {
        "latitude": 33,
        "longitude": 44
    };
    latlong["IR"] = {
        "latitude": 32,
        "longitude": 53
    };
    latlong["IS"] = {
        "latitude": 65,
        "longitude": -18
    };
    latlong["IT"] = {
        "latitude": 42.8333,
        "longitude": 12.8333
    };
    latlong["JM"] = {
        "latitude": 18.25,
        "longitude": -77.5
    };
    latlong["JO"] = {
        "latitude": 31,
        "longitude": 36
    };
    latlong["JP"] = {
        "latitude": 36,
        "longitude": 138
    };
    latlong["KE"] = {
        "latitude": 1,
        "longitude": 38
    };
    latlong["KG"] = {
        "latitude": 41,
        "longitude": 75
    };
    latlong["KH"] = {
        "latitude": 13,
        "longitude": 105
    };
    latlong["KI"] = {
        "latitude": 1.4167,
        "longitude": 173
    };
    latlong["KM"] = {
        "latitude": -12.1667,
        "longitude": 44.25
    };
    latlong["KN"] = {
        "latitude": 17.3333,
        "longitude": -62.75
    };
    latlong["KP"] = {
        "latitude": 40,
        "longitude": 127
    };
    latlong["KR"] = {
        "latitude": 37,
        "longitude": 127.5
    };
    latlong["KW"] = {
        "latitude": 29.3375,
        "longitude": 47.6581
    };
    latlong["KY"] = {
        "latitude": 19.5,
        "longitude": -80.5
    };
    latlong["KZ"] = {
        "latitude": 48,
        "longitude": 68
    };
    latlong["LA"] = {
        "latitude": 18,
        "longitude": 105
    };
    latlong["LB"] = {
        "latitude": 33.8333,
        "longitude": 35.8333
    };
    latlong["LC"] = {
        "latitude": 13.8833,
        "longitude": -61.1333
    };
    latlong["LI"] = {
        "latitude": 47.1667,
        "longitude": 9.5333
    };
    latlong["LK"] = {
        "latitude": 7,
        "longitude": 81
    };
    latlong["LR"] = {
        "latitude": 6.5,
        "longitude": -9.5
    };
    latlong["LS"] = {
        "latitude": -29.5,
        "longitude": 28.5
    };
    latlong["LT"] = {
        "latitude": 55,
        "longitude": 24
    };
    latlong["LU"] = {
        "latitude": 49.75,
        "longitude": 6
    };
    latlong["LV"] = {
        "latitude": 57,
        "longitude": 25
    };
    latlong["LY"] = {
        "latitude": 25,
        "longitude": 17
    };
    latlong["MA"] = {
        "latitude": 32,
        "longitude": -5
    };
    latlong["MC"] = {
        "latitude": 43.7333,
        "longitude": 7.4
    };
    latlong["MD"] = {
        "latitude": 47,
        "longitude": 29
    };
    latlong["ME"] = {
        "latitude": 42.5,
        "longitude": 19.4
    };
    latlong["MG"] = {
        "latitude": -20,
        "longitude": 47
    };
    latlong["MH"] = {
        "latitude": 9,
        "longitude": 168
    };
    latlong["MK"] = {
        "latitude": 41.8333,
        "longitude": 22
    };
    latlong["ML"] = {
        "latitude": 17,
        "longitude": -4
    };
    latlong["MM"] = {
        "latitude": 22,
        "longitude": 98
    };
    latlong["MN"] = {
        "latitude": 46,
        "longitude": 105
    };
    latlong["MO"] = {
        "latitude": 22.1667,
        "longitude": 113.55
    };
    latlong["MP"] = {
        "latitude": 15.2,
        "longitude": 145.75
    };
    latlong["MQ"] = {
        "latitude": 14.6667,
        "longitude": -61
    };
    latlong["MR"] = {
        "latitude": 20,
        "longitude": -12
    };
    latlong["MS"] = {
        "latitude": 16.75,
        "longitude": -62.2
    };
    latlong["MT"] = {
        "latitude": 35.8333,
        "longitude": 14.5833
    };
    latlong["MU"] = {
        "latitude": -20.2833,
        "longitude": 57.55
    };
    latlong["MV"] = {
        "latitude": 3.25,
        "longitude": 73
    };
    latlong["MW"] = {
        "latitude": -13.5,
        "longitude": 34
    };
    latlong["MX"] = {
        "latitude": 23,
        "longitude": -102
    };
    latlong["MY"] = {
        "latitude": 2.5,
        "longitude": 112.5
    };
    latlong["MZ"] = {
        "latitude": -18.25,
        "longitude": 35
    };
    latlong["NA"] = {
        "latitude": -22,
        "longitude": 17
    };
    latlong["NC"] = {
        "latitude": -21.5,
        "longitude": 165.5
    };
    latlong["NE"] = {
        "latitude": 16,
        "longitude": 8
    };
    latlong["NF"] = {
        "latitude": -29.0333,
        "longitude": 167.95
    };
    latlong["NG"] = {
        "latitude": 10,
        "longitude": 8
    };
    latlong["NI"] = {
        "latitude": 13,
        "longitude": -85
    };
    latlong["NL"] = {
        "latitude": 52.5,
        "longitude": 5.75
    };
    latlong["NO"] = {
        "latitude": 62,
        "longitude": 10
    };
    latlong["NP"] = {
        "latitude": 28,
        "longitude": 84
    };
    latlong["NR"] = {
        "latitude": -0.5333,
        "longitude": 166.9167
    };
    latlong["NU"] = {
        "latitude": -19.0333,
        "longitude": -169.8667
    };
    latlong["NZ"] = {
        "latitude": -41,
        "longitude": 174
    };
    latlong["OM"] = {
        "latitude": 21,
        "longitude": 57
    };
    latlong["PA"] = {
        "latitude": 9,
        "longitude": -80
    };
    latlong["PE"] = {
        "latitude": -10,
        "longitude": -76
    };
    latlong["PF"] = {
        "latitude": -15,
        "longitude": -140
    };
    latlong["PG"] = {
        "latitude": -6,
        "longitude": 147
    };
    latlong["PH"] = {
        "latitude": 13,
        "longitude": 122
    };
    latlong["PK"] = {
        "latitude": 30,
        "longitude": 70
    };
    latlong["PL"] = {
        "latitude": 52,
        "longitude": 20
    };
    latlong["PM"] = {
        "latitude": 46.8333,
        "longitude": -56.3333
    };
    latlong["PR"] = {
        "latitude": 18.25,
        "longitude": -66.5
    };
    latlong["PS"] = {
        "latitude": 32,
        "longitude": 35.25
    };
    latlong["PT"] = {
        "latitude": 39.5,
        "longitude": -8
    };
    latlong["PW"] = {
        "latitude": 7.5,
        "longitude": 134.5
    };
    latlong["PY"] = {
        "latitude": -23,
        "longitude": -58
    };
    latlong["QA"] = {
        "latitude": 25.5,
        "longitude": 51.25
    };
    latlong["RE"] = {
        "latitude": -21.1,
        "longitude": 55.6
    };
    latlong["RO"] = {
        "latitude": 46,
        "longitude": 25
    };
    latlong["RS"] = {
        "latitude": 44,
        "longitude": 21
    };
    latlong["RU"] = {
        "latitude": 60,
        "longitude": 100
    };
    latlong["RW"] = {
        "latitude": -2,
        "longitude": 30
    };
    latlong["SA"] = {
        "latitude": 25,
        "longitude": 45
    };
    latlong["SB"] = {
        "latitude": -8,
        "longitude": 159
    };
    latlong["SC"] = {
        "latitude": -4.5833,
        "longitude": 55.6667
    };
    latlong["SD"] = {
        "latitude": 15,
        "longitude": 30
    };
    latlong["SE"] = {
        "latitude": 62,
        "longitude": 15
    };
    latlong["SG"] = {
        "latitude": 1.3667,
        "longitude": 103.8
    };
    latlong["SH"] = {
        "latitude": -15.9333,
        "longitude": -5.7
    };
    latlong["SI"] = {
        "latitude": 46,
        "longitude": 15
    };
    latlong["SJ"] = {
        "latitude": 78,
        "longitude": 20
    };
    latlong["SK"] = {
        "latitude": 48.6667,
        "longitude": 19.5
    };
    latlong["SL"] = {
        "latitude": 8.5,
        "longitude": -11.5
    };
    latlong["SM"] = {
        "latitude": 43.7667,
        "longitude": 12.4167
    };
    latlong["SN"] = {
        "latitude": 14,
        "longitude": -14
    };
    latlong["SO"] = {
        "latitude": 10,
        "longitude": 49
    };
    latlong["SR"] = {
        "latitude": 4,
        "longitude": -56
    };
    latlong["ST"] = {
        "latitude": 1,
        "longitude": 7
    };
    latlong["SV"] = {
        "latitude": 13.8333,
        "longitude": -88.9167
    };
    latlong["SY"] = {
        "latitude": 35,
        "longitude": 38
    };
    latlong["SZ"] = {
        "latitude": -26.5,
        "longitude": 31.5
    };
    latlong["TC"] = {
        "latitude": 21.75,
        "longitude": -71.5833
    };
    latlong["TD"] = {
        "latitude": 15,
        "longitude": 19
    };
    latlong["TF"] = {
        "latitude": -43,
        "longitude": 67
    };
    latlong["TG"] = {
        "latitude": 8,
        "longitude": 1.1667
    };
    latlong["TH"] = {
        "latitude": 15,
        "longitude": 100
    };
    latlong["TJ"] = {
        "latitude": 39,
        "longitude": 71
    };
    latlong["TK"] = {
        "latitude": -9,
        "longitude": -172
    };
    latlong["TM"] = {
        "latitude": 40,
        "longitude": 60
    };
    latlong["TN"] = {
        "latitude": 34,
        "longitude": 9
    };
    latlong["TO"] = {
        "latitude": -20,
        "longitude": -175
    };
    latlong["TR"] = {
        "latitude": 39,
        "longitude": 35
    };
    latlong["TT"] = {
        "latitude": 11,
        "longitude": -61
    };
    latlong["TV"] = {
        "latitude": -8,
        "longitude": 178
    };
    latlong["TW"] = {
        "latitude": 23.5,
        "longitude": 121
    };
    latlong["TZ"] = {
        "latitude": -6,
        "longitude": 35
    };
    latlong["UA"] = {
        "latitude": 49,
        "longitude": 32
    };
    latlong["UG"] = {
        "latitude": 1,
        "longitude": 32
    };
    latlong["UM"] = {
        "latitude": 19.2833,
        "longitude": 166.6
    };
    latlong["US"] = {
        "latitude": 38,
        "longitude": -97
    };
    latlong["UY"] = {
        "latitude": -33,
        "longitude": -56
    };
    latlong["UZ"] = {
        "latitude": 41,
        "longitude": 64
    };
    latlong["VA"] = {
        "latitude": 41.9,
        "longitude": 12.45
    };
    latlong["VC"] = {
        "latitude": 13.25,
        "longitude": -61.2
    };
    latlong["VE"] = {
        "latitude": 8,
        "longitude": -66
    };
    latlong["VG"] = {
        "latitude": 18.5,
        "longitude": -64.5
    };
    latlong["VI"] = {
        "latitude": 18.3333,
        "longitude": -64.8333
    };
    latlong["VN"] = {
        "latitude": 16,
        "longitude": 106
    };
    latlong["VU"] = {
        "latitude": -16,
        "longitude": 167
    };
    latlong["WF"] = {
        "latitude": -13.3,
        "longitude": -176.2
    };
    latlong["WS"] = {
        "latitude": -13.5833,
        "longitude": -172.3333
    };
    latlong["YE"] = {
        "latitude": 15,
        "longitude": 48
    };
    latlong["YT"] = {
        "latitude": -12.8333,
        "longitude": 45.1667
    };
    latlong["ZA"] = {
        "latitude": -29,
        "longitude": 24
    };
    latlong["ZM"] = {
        "latitude": -15,
        "longitude": 30
    };
    latlong["ZW"] = {
        "latitude": -20,
        "longitude": 30
    };
    latlong["Unknown"] = {
        "latitude": 15,
        "longitude": 100
    };

    stats_live.init = function (data) {
        mapData = data;

        var minBulletSize = 3;
        var maxBulletSize = 50;
        var min = Infinity;
        var max = -Infinity;
        AmCharts.theme = AmCharts.themes.black;
        // get min and max values
        for (var i = 0; i < mapData.length; i++) {
            var value = mapData[i].value;
            if (value < min) {
                min = value;
            }
            if (value > max) {
                max = value;
            }
        }

        /* Adapt Control Position if RTL or not */
        function generateZoomControl(color) {
            if (color == null) color = "#39B0CA";
            var chartData = {
                "buttonFillColor": color,
                top: 60,
                left: $('body').hasClass('rtl') ? 'auto' : 20,
                right: $('body').hasClass('rtl') ? 20 : 'auto'
            };
            return chartData;
        }

        // build map
        function worldMap() {
            map = new AmCharts.AmMap();
            map.zoomControl = generateZoomControl();
            map.pathToImages = "assets/global/plugins/maps-amcharts/ammap/images/";
            map.areasSettings = {
                unlistedAreasColor: "#FFFFFF",
                unlistedAreasAlpha: 0.1
            };
            map.imagesSettings = {
                balloonText: "<span style='font-size:14px;'><b>[[title]]</b> [[value]]</span>",
                alpha: 0.6
            };
            map.zoomControl = generateZoomControl();
            var targetSVG = "M9,0C4.029,0,0,4.029,0,9s4.029,9,9,9s9-4.029,9-9S13.971,0,9,0z M9,15.93 c-3.83,0-6.93-3.1-6.93-6.93S5.17,2.07,9,2.07s6.93,3.1,6.93,6.93S12.83,15.93,9,15.93 M12.5,9c0,1.933-1.567,3.5-3.5,3.5S5.5,10.933,5.5,9S7.067,5.5,9,5.5 S12.5,7.067,12.5,9z";
            var dataProvider = {
                mapVar: AmCharts.maps.worldLow,
                images: [],
                selectable: true
            };
            // create circle for each country
            for (var i = 0; i < mapData.length; i++) {
                var dataItem = mapData[i];
                var value = dataItem.value;
                // calculate size of a bubble
                var size = (value - min) / (max - min) * (maxBulletSize - minBulletSize) + minBulletSize;
                if (size < minBulletSize) {
                    size = minBulletSize;
                }
                var id = dataItem.code;
                dataProvider.images.push({
                    type: "circle",
                    width: size,
                    height: size,
                    color: dataItem.color,
                    longitude: latlong[id].longitude,
                    latitude: latlong[id].latitude,
                    title: dataItem.name,
                    value: value
                });
            }
            map.showImagesInList = true;
            map.dataProvider = dataProvider;
            map.write("map");
        }

        worldMap();

        $(document).on("click", ".panel-header .panel-maximize", function (event) {
            var panel = $(this).parents(".panel:first");
            if (panel.hasClass("maximized")) {
                map.invalidateSize();
            } else {
                map.invalidateSize();
            }
        });

        $(document).on("click", "#switch-rtl", function (event) {
            map.zoomControl = generateZoomControl();
            map.validateData();
        });

        $(document).on("click", ".theme-color", function (event) {
            var color = $(this).data('color');
            map.zoomControl = generateZoomControl(color);
            map.validateData();
        });
    };

    stats_live.setHeights = function () {
        var widgetMapHeight = $('.widget-map').height();
        var pstatHeadHeight = $('.panel-stat-chart').parent().find('.panel-header').height() + 12;
        var pstatBodyHeight = $('.panel-stat-chart').parent().find('.panel-body').height() + 15;
        var pstatheight = widgetMapHeight - pstatHeadHeight - pstatBodyHeight + 30;
        $('.panel-stat-chart').css('height', pstatheight);
        var clockHeight = $('.jquery-clock ').height();
        var widgetProgressHeight = $('.widget-progress-bar').height();
        $('.widget-progress-bar').css('margin-top', widgetMapHeight - clockHeight - widgetProgressHeight - 3);
    };

    stats_live.reinit = function (data) {
        if (map !== undefined) {
            map.clear();
        }

        stats_live.init(data);
    };

    /////
    var initExamChart = function (chartExamData) {
        if (chartExam != null && typeof chartExam.destroy != 'undefined') {
            chartExam.destroy();
        }
        delete chartExam;
        chartExam = null;
        canvasExam = document.getElementById("exam-chart");
        canvasExamContext = canvasExam.getContext("2d");
        canvasExamContext.clearRect(0, 0, canvasExam.width, canvasExam.height);
        chartExam = new Chart(canvasExamContext).Pie(chartExamData, {
            responsive: true
        });
    };

    stats_live.ExamChart = function (dataExamPass, dataExamNotPass) {
        var ExamChartData = [
            { value: dataExamNotPass, color: "#C9625F", highlight: "#C9625F", label: "แบบทดสอบไม่ผ่าน" },
            { value: dataExamPass, color: "#18a689", highlight: "#18a689", label: "ทำแบบทดสอบผ่าน" }
        ];

        initExamChart(ExamChartData);
    };
    /////

    /////
    var initCompareChart = function (chartCompareData) {
        if (chartCompare != null && typeof chartCompare.destroy != 'undefined') {
            chartCompare.destroy();
        }
        delete chartCompare;
        chartCompare = null;
        canvasCompare = document.getElementById("compare-chart");
        canvasCompareContext = canvasCompare.getContext("2d");
        canvasCompareContext.clearRect(0, 0, canvasCompare.width, canvasCompare.height);
        chartCompare = new Chart(canvasCompareContext).Pie(chartCompareData, {
            responsive: true
        });
    };

    stats_live.CompareChart = function (dataOver, dataUnder) {
        var CompareChartData = [
            { value: dataUnder, color: "#F2A057", highlight: "#F2A057", label: "Post-Test น้อยกว่าหรือเท่ากับ Pre-Test" },
            { value: dataOver, color: "#18a689", highlight: "#18a689", label: "Post-Test คะแนนมากกว่า Pre-Test" }
        ];

        initCompareChart(CompareChartData);
    };

    /////
    var initPrePostChart = function (chartPrePostData) {
        if (chartPrePost != null && typeof chartPrePost.destroy != 'undefined') {
            chartPrePost.destroy();
        }
        delete chartPrePost;
        chartPrePost = null;
        canvasPrePost = document.getElementById("pre-post-chart");
        canvasPrePostContext = canvasPrePost.getContext("2d");
        canvasPrePostContext.clearRect(0, 0, canvasPrePost.width, canvasPrePost.height);
        chartPrePost = new Chart(canvasPrePostContext).Pie(chartPrePostData, {
            responsive: true
        });
    };

    stats_live.PrePostChart = function (dataPreTest, dataPostTest) {
        var PrePostChartData = [
            { value: dataPostTest, color: "#6E62B5", highlight: "#6E62B5", label: "Post-Test" },
            { value: dataPreTest, color: "#4584D1", highlight: "#4584D1", label: "Pre-Test" }
        ];

        initPrePostChart(PrePostChartData);
    };

    var initNotPassChart = function (chartNotPassData) {
        if (chartNotPass != null && typeof chartNotPass.destroy != 'undefined') {
            chartNotPass.destroy();
        }
        delete chartNotPass;
        chartNotPass = null;
        canvasNotPass = document.getElementById("not-pass-chart");
        canvasNotPassContext = canvasNotPass.getContext("2d");
        canvasNotPassContext.clearRect(0, 0, canvasNotPass.width, canvasNotPass.height);
        chartNotPass = new Chart(canvasNotPassContext).Pie(chartNotPassData, {
            responsive: true
        });
    };

    stats_live.notPassChart = function (dataQuizProcess, dataNotLearning, dataLearningNotPass, dataLearningPassNotExam, dataExamNotPass) {
        if(dataQuizProcess){
            var notPassChartData = [
                { value: dataExamNotPass, color: "#C9625F", highlight: "#C9625F", label: "สอบไม่ผ่าน" },
                { value: dataLearningPassNotExam, color: "#6E62B5", highlight: "#6E62B5", label: "ยังไม่สอบ" },
                { value: dataLearningNotPass, color: "#3498db", highlight: "#3498db", label: "กำลังเรียน" },
                { value: dataNotLearning, color: "#2B2E33", highlight: "#2B2E33", label: "ยังไม่เริ่มเรียน" }
            ];
        }else{
            var notPassChartData = [
                { value: dataLearningNotPass, color: "#3498db", highlight: "#3498db", label: "กำลังเรียน" },
                { value: dataNotLearning, color: "#2B2E33", highlight: "#2B2E33", label: "ยังไม่เริ่มเรียน" }
            ];
        }

        initNotPassChart(notPassChartData);
    };

    var initPassChart = function (chartPassData) {
        if (chartPass != null && typeof chartPass.destroy != 'undefined') {
            chartPass.destroy();
        }
        delete chartPass;
        chartPass = null;
        canvasPass = document.getElementById("pass-chart");
        canvasPassContext = canvasPass.getContext("2d");
        canvasPassContext.clearRect(0, 0, canvasPass.width, canvasPass.height);
        chartPass = new Chart(canvasPassContext).Pie(chartPassData, {
            responsive: true
        });
    };

    stats_live.passChart = function (dataNotCertificate, dataCertificate) {
        var passChartData = [
            { value: dataCertificate, color: "#18a689", highlight: "#18a689", label: "พิมพ์วุฒิบัตร" },
            { value: dataNotCertificate, color: "#F2A057", highlight: "#F2A057", label: "ไม่พิมพ์วุฒิบัตร" }
        ];

        initPassChart(passChartData);
    };

    var initLearningChart = function (chartLearningData) {
        if (chartLearning != null && typeof chartLearning.destroy != 'undefined') {
            chartLearning.destroy();
        }
        delete chartLearning;
        chartLearning = null;
        canvasLearning = document.getElementById("learning-chart");
        canvasLearningContext = canvasLearning.getContext("2d");
        canvasLearningContext.clearRect(0, 0, canvasLearning.width, canvasLearning.height);
        chartLearning = new Chart(canvasLearningContext).Pie(chartLearningData, {
            responsive: true
        });
    };

    stats_live.learningChart = function (dataQuizProcess, dataNotLearning, dataLearningNotPass, dataLearningPassNotExam, dataExamNotPass, dataExamPass, dataLearningPass) {
        if (dataQuizProcess) {
            var learningChartData = [
                { value: dataExamPass, color: "#25A589", highlight: "#25A589", label: "ผ่าน" },
                { value: dataExamNotPass, color: "#C9625F", highlight: "#C9625F", label: "สอบไม่ผ่าน" },
                { value: dataLearningPassNotExam, color: "#6E62B5", highlight: "#6E62B5", label: "ยังไม่สอบ" },
                { value: dataLearningNotPass, color: "#3498db", highlight: "#3498db", label: "กำลังเรียน" },
                { value: dataNotLearning, color: "#2B2E33", highlight: "#2B2E33", label: "ยังไม่เริ่มเรียน" }
            ];
        } else {
            var learningChartData = [
                { value: dataLearningPass, color: "#25A589", highlight: "#25A589", label: "ผ่าน" },
                { value: dataLearningNotPass, color: "#3498db", highlight: "#3498db", label: "กำลังเรียน" },
                { value: dataNotLearning, color: "#2B2E33", highlight: "#2B2E33", label: "ยังไม่เริ่มเรียน" }
            ];
        }

        initLearningChart(learningChartData);
    };

    var initMobileChart = function (chartMobileData) {
        if (chartMobile != null && typeof chartMobile.destroy != 'undefined') {
            chartMobile.destroy();
        }
        delete chartMobile;
        chartMobile = null;
        canvasMobile = document.getElementById("mobile-chart");
        canvasMobileContext = canvasMobile.getContext("2d");
        canvasMobileContext.clearRect(0, 0, canvasMobile.width, canvasMobile.height);
        chartMobile = new Chart(canvasMobileContext).Pie(chartMobileData, {
            responsive: true
        });
    };

    stats_live.mobileChart = function (dataiOS, dataAndroid) {
        var mobileChartData = [
            { value: dataiOS, color: "#4584D1", highlight: "#4584D1", label: "iOS" },
            { value: dataAndroid, color: "#DC88E6", highlight: "#DC88E6", label: "Android" }
        ];

        initMobileChart(mobileChartData);
    };

    var initDesktopChart = function (chartDesktopData) {
        if (chartDesktop != null && typeof chartDesktop.destroy != 'undefined') {
            chartDesktop.destroy();
        }
        delete chartDesktop;
        chartDesktop = null;
        canvasDesktop = document.getElementById("desktop-chart");
        canvasDesktopContext = canvasDesktop.getContext("2d");
        canvasDesktopContext.clearRect(0, 0, canvasDesktop.width, canvasDesktop.height);
        chartDesktop = new Chart(canvasDesktopContext).Pie(chartDesktopData, {
            responsive: true
        });
    };

    stats_live.desktopChart = function (dataWindows, dataMac, dataLinux) {
        var desktopChartData = [
            { value: dataWindows, color: "#C9625F", highlight: "#C9625F", label: "Windows" },
            { value: dataMac, color: "#F2A057", highlight: "#F2A057", label: "OS X" },
            { value: dataLinux, color: "#EBC85E", highlight: "#EBC85E", label: "Linux" }
        ];

        initDesktopChart(desktopChartData);
    };


    var initLogEventChart = function (chartLogEventData) {
        if (chartLogEvent != null && typeof chartLogEvent.destroy != 'undefined') {
            chartLogEvent.destroy();
        }
        delete chartLogEvent;
        chartLogEvent = null;
        canvasLogEvent = document.getElementById("log-chart");
        canvasLogEventContext = canvasLogEvent.getContext("2d");
        canvasLogEventContext.clearRect(0, 0, canvasLogEvent.width, canvasLogEvent.height);
        chartLogEvent = new Chart(canvasLogEventContext).Line(chartLogEventData, {
            responsive: true
        });
    };

    stats_live.LogEventChart = function (Labels, Data) {
        var randomScalingFactor = function () { return Math.round(Math.random() * 100) };
        var LogEventData = {
            labels: Labels,
            datasets: [
                {
                    label: "My Second dataset",
                    fillColor: "rgba(49, 157, 181,0.2)",
                    strokeColor: "#319DB5",
                    pointColor: "#319DB5",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "#319DB5",
                    data: Data
                }
            ]
        }

        initLogEventChart(LogEventData);
    };

    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

    stats_live.stockCharts = function (data) {
        // var items = Array(data1, data2, data3, data4);
        // var randomData = items[Math.floor(Math.random() * items.length)];
        // var custom_colors = ['#C9625F', '#18A689', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#8085e8', '#91e8e1'];
        // var custom_color = custom_colors[Math.floor(Math.random() * custom_colors.length)];
        custom_color = '#8085e9';

        // Create the chart
        $('#stats_live-chart').highcharts('StockChart', {
            chart: {
                height: 286,
                borderColor: '#DE0E13'
            },
            credits: {
                enabled: false
            },
            exporting: {
                enabled: false
            },
            rangeSelector: {
                inputEnabled: false,
                selected: 3,
                buttons: [{
                    type: 'hour',
                    count: 1,
                    text: '1h'
                }, {
                    type: 'hour',
                    count: 3,
                    text: '3h'
                }, {
                    type: 'month',
                    count: 1,
                    text: '1m'
                }, {
                    type: 'all',
                    text: 'All',
                }]
            },
            colors: [custom_color],
            scrollbar: {
                enabled: false
            },
            navigator: {
                enabled: true
            },
            xAxis: {
                lineColor: '#EFEFEF',
                tickColor: '#EFEFEF',
                dateTimeLabelFormats: {
                    // hour: '%l %p',
                    day: '%e %b',
                    week: '%e %b',
                },
                type: 'datetime',
                ordinal: false
            },
            yAxis: {
                gridLineColor: '#EFEFEF',
                // min: 0
            },
            series: [
                {
                    name: "View(s)",
                    data: data,
                    tooltip: {
                        valueDecimals: 0
                    },
                    marker: {
                        enabled: null, // auto
                        radius: 3,
                        lineWidth: 1,
                        lineColor: '#FFFFFF'
                    },
                    dataGrouping: {
                        enabled: false
                    }
                }
            ]
        });
    };

    stats_live.getStatsLiveEnroll = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/enroll' + '?' + queryString);
    };

    stats_live.getStatsLiveLogs = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/log' + '?' + queryString);
    };

    stats_live.getStatsLiveDevice = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/device' + '?' + queryString);
    };

    stats_live.getStatsLiveAll = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/all' + '?' + queryString);
    };

    stats_live.getStatsLiveCities = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/city' + '?' + queryString);
    };

    stats_live.getStatsLiveStates = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/state' + '?' + queryString);
    };

    stats_live.getStatsLiveCountries = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/country' + '?' + queryString);
    };

    stats_live.getStatsLiveInfo = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/info' + '?' + queryString);
    };

    stats_live.getStatsLive = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/stats_live' + '?' + queryString);
    };

    stats_live.getStatsLiveLearning = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/learning' + '?' + queryString);
    };

    stats_live.getStatsLiveCourses = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/courses' + '?' + queryString);
    };

    stats_live.getStatsLiveQuiz = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/quiz' + '?' + queryString);
    };

    stats_live.getStatsLiveInfo = function (queryString) {
        return $http.get(settingsFactory.get('stats_live') + '/info' + '?' + queryString);
    };

    stats_live.getCourse = function(theCourses) {
        return $http.get(settingsFactory.get('stats_live') + '/' + theCourses.id + '/course');
    };

    return stats_live;

}]);




