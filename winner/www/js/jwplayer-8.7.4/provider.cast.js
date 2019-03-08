/*!
   JW Player version 8.7.4
   Copyright (c) 2019, JW Player, All Rights Reserved 
   This source code and its use and distribution is subject to the terms 
   and conditions of the applicable license agreement. 
   https://www.jwplayer.com/tos/
   This product includes portions of other software. For the full text of licenses, see
   https://ssl.p.jwpcdn.com/player/v/8.7.4/notice.txt
*/
(window.webpackJsonpjwplayer=window.webpackJsonpjwplayer||[]).push([[15],{150:function(e,t,a){"use strict";a.r(t);var i=a(3),r=a(0),n=a(5),s=a(1),c=function(e){var t=this,a=window.chrome.cast,c=a.media,d=window.cast.framework,u=d.CastContext.getInstance(),o=null,l=d.CastContextEventType.CAST_STATE_CHANGED,f=e||c.DEFAULT_MEDIA_RECEIVER_APP_ID;function v(e,a,i){var n=e.allSources.slice(0).sort(function(e,t){return!e.default&&t.default?1:0}),s=Object(r.l)(n,function(e){var t=!Object(r.G)(e.mediaTypes)||!Object(r.e)(e.mediaTypes,'video/webm; codecs="vp9"'),a=!Object(r.G)(e.drm)||Object(r.b)(e.drm,function(e,t){return"fairplay"!==t});return t&&a});if(s){var d=function(e){switch(e){case"mp4":case"webm":return"video/"+e;case"mpd":case"dash":return"application/dash+xml";case"m3u8":case"hls":return"application/x-mpegURL";case"aac":return"audio/x-aac";case"mp3":return"audio/mpeg";default:return e}}(s.type),u=T(s.file),o=e.image?T(e.image):null,l=s.drm,f=new c.MediaInfo(u,d);return f.metadata=new c.GenericMediaMetadata,f.metadata.title=e.title,f.metadata.subtitle=e.description,f.metadata.index=a||0,f.metadata.playerId=t.getPlayerId(),e.tracks&&e.tracks.length&&(f.tracks=function(e){return e.map(function(e,t){var a=t+1,i=new c.Track(a,c.TrackType.TEXT);return i.trackContentId=e.file,i.trackContentType="text/vtt",i.subtype=c.TextTrackType.SUBTITLES,i.name=e.label||a,i.language="en-US",i.customData="side-loaded captions",i})}(e.tracks)),i&&(f.textTrackStyle=t.obtainTrackStyles(i)),o&&(f.metadata.images=[{url:o}]),l&&(f.customData={drm:l}),f}}function T(e){var t=document.createElement("a");return t.href=e,t.href}function h(){var e=u.getCastState()!==d.CastState.NO_DEVICES_AVAILABLE,a="";(o=u.getCurrentSession())&&(a=o.getCastDevice().friendlyName||a),t.trigger("castState",{available:e,active:!!o,deviceName:a})}function m(){var e=t.getMedia();e&&t.trigger("mediaUpdate",{field:"media",value:e})}Object(r.j)(t,n.a),u.removeEventListener(l,h),u.addEventListener(l,h),u.setOptions({receiverApplicationId:f,autoJoinPolicy:a.AutoJoinPolicy.ORIGIN_SCOPED}),t.updateCastState=h,t.setPlaylist=function(e){var a=e.get("playlist"),i=e.get("item"),r=e.mediaModel.get("position"),n=e.get("repeat"),s=e.get("captions"),d=void 0,u=[],l=1;"complete"===e.get("state")&&(i=0,r=0);for(var f=i;f<a.length;f++){var T=v(a[f],f,s),h=void 0;if(T){h=new c.QueueItem(T),T.metadata.index===i&&(h.startTime=r);var m=JSON.stringify(h).length+1;if(!(l+m<32e3))break;u.push(h),l+=m}}(d=new c.QueueLoadRequest(u)).startIndex=0,n&&(d.repeatMode=c.RepeatMode.ALL),o.getSessionObj().queueLoad(d,t.loaded,t.error)},t.getPlayerId=function(){var e=t.getMedia();return e&&e.media?e.media.metadata.playerId:o?o.getSessionObj().playerId:null},t.setPlayerId=function(e){o&&(o.getSessionObj().playerId=e)},t.loaded=function(e){(t.trigger("mediaUpdate",{field:"volume",value:{volume:o.getVolume(),isMute:o.isMute()}}),o)&&o.getSessionObj().addMediaListener(function(e){e.addUpdateListener(m)});e.addUpdateListener(m),t.play()},t.addListeners=function(){if(!o)return null;o.getSessionObj().addUpdateListener(h),o.addEventListener(d.SessionEventType.VOLUME_CHANGED,function(e){t.trigger("mediaUpdate",{field:"volume",value:e})})},t.reset=function(){t.removeListeners(),u&&u.removeEventListener(l,h)},t.removeListeners=function(){var e=void 0;if(!o)return null;(e=o.getSessionObj()).removeUpdateListener(h),e.media.forEach(function(e){e.removeUpdateListener(m)}),o.removeEventListener(d.SessionEventType.VOLUME_CHANGED)},t.getMedia=function(){if(o){var e=o.getSessionObj().media;if(e&&e.length)return e[0]}return null},t.error=function(e){t.trigger(i.Ua,new s.s(null,305e3,{errorCode:e})),t.disconnect()},t.item=function(e){var a=t.getMedia();if(a){var i=v(e),n=Object(r.l)(a.items,function(e){return e.media.contentId===i.contentId&&e.media.index===i.index});n?a.queueJumpToItem(n.itemId):t.trigger("setPlaylist")}else t.trigger("setPlaylist")},t.play=function(){t.getMedia()&&t.getMedia().play()},t.pause=function(){t.getMedia().pause()},t.next=function(){t.getMedia().queueNext()},t.disconnect=function(){o.endSession(!0)},t.seek=function(e,a){var i=new c.SeekRequest;i.currentTime=e,i.resumeState=c.ResumeState.PLAYBACK_START,t.getMedia().seek(i,a)},t.mute=function(e){o.setMute(e)},t.volume=function(e){o.setVolume(e/100)},t.editTracksInfo=function(e,a){var i=t.getMedia();if(i){var r=new c.EditTracksInfoRequest(e,a);i.editTracksInfo(r)}},t.extractEmbeddedCaptions=function(){var e=t.getMedia();if(e&&e.media.tracks){var a=e.media.tracks.filter(function(e){return"TEXT"===e.type&&"side-loaded captions"!==e.customData}).map(function(e,t){return e.mapId=t,e.kind="subtitles",e.cues=[],e});a.length&&t.trigger("mediaUpdate",{field:"captions",value:{tracks:a}})}},t.obtainTrackStyles=function(e){var t=function(e){return Math.round(e/100*255).toString(16)},a=new c.TextTrackStyle;return a.foregroundColor=e.color+t(e.fontOpacity),a.backgroundColor=e.backgroundColor+t(e.backgroundOpacity),a.windowColor=e.windowColor+t(e.windowOpacity),a.fontFamily=e.fontFamily,a.fontStyle=c.TextTrackFontStyle.NORMAL,a.fontScale=e.fontSize/14,a.edgeType=function(e){var t=c.TextTrackEdgeType;switch(e){case"dropshadow":return t.DROP_SHADOW;case"raised":return t.RAISED;case"depressed":return t.DEPRESSED;case"uniform":return t.OUTLINE;default:return t.NONE}}(e.edgeStyle),a.windowType=c.TextTrackWindowType.NORMAL,a}},d=a(9),u=a(55),o=a(77),l=function(){var e=this,t=void 0,a=void 0,r=void 0;function n(e){if(a){var t=Array.prototype.slice.call(arguments,1);a[e]&&a[e].apply(a,t)}}function s(e){if(a){var t=a.getMedia();return t?"currentTime"===e?t.getEstimatedTime():t[e]||t.media&&t.media[e]:null}}e.destroy=function(){clearInterval(e.timeInterval)},e.setService=function(t){a=t,e.updateScreen()},e.setup=function(t){e.setState(i.Ka),n("setup",t)},e.init=function(e){r!==e&&(r=e,n("item",e))},e.load=function(t){e.init(t),e.play()},e.play=function(){n("play")},e.pause=function(){n("pause")},e.seek=function(t){e.trigger(i.Q,{position:s("currentTime"),offset:t}),n("seek",t,function(){e.trigger(i.R)})},e.next=function(e){n("next",e)},e.volume=function(e){n("volume",e)},e.mute=function(e){n("mute",e)},e.setSubtitlesTrack=function(e){e>0&&a.editTracksInfo([e+function(){var e=0,t=a.getMedia();if(!t)return e;var i=t.media.tracks;if(!i)return e;for(var r=0;r<i.length;r++){var n=i[r];if("TEXT"===n.type){e=r;break}}return e}()])},e.updateScreen=function(e,a){Object(d.o)(t,function(e,t){return'<div class="jw-cast jw-reset jw-preview" style="'+(t?'background-image:url("'+t+'")':"")+'"><div class="jw-cast-container"><div class="jw-cast-text jw-reset">'+(e||"")+"</div></div></div>"}(e,a))},e.setContainer=function(e){t=e},e.getContainer=function(){return t},e.remove=function(){clearInterval(e.timeInterval)},e.getDuration=function(){return s("duration")||1/0},e.stop=function(){e.clearTracks()},e.castEventHandlers={media:function(t){var i=s("items"),r="IDLE"===t.playerState&&"FINISHED"===t.idleReason,n="IDLE"===t.playerState&&"ERROR"===t.idleReason,c=r&&!i;e.castEventHandlers.playerState(c?"complete":t.playerState),e.castEventHandlers.currentTime(),clearInterval(e.timeInterval),"PLAYING"===t.playerState?e.timeInterval=setInterval(e.castEventHandlers.currentTime,100):c?(e.setState("complete"),a.disconnect()):n&&(e.setState("error"),a.disconnect())},volume:function(t){e.trigger("volume",{volume:Math.round(100*t.volume)}),e.trigger("mute",{mute:t.isMute})},captions:function(t){e.clearTracks(),e.setTextTracks(t.tracks)},playerState:function(t){var a=[i.Ka,i.Na,i.Qa,i.Pa,i.Ra,i.Oa,i.La,i.Ma];if(t&&-1!==a.indexOf(t.toLowerCase())){var r=t.toLowerCase();r!==i.Na&&r!==i.Ka||e.trigger(i.D,{bufferPercent:0,currentTime:s("currentTime"),position:s("currentTime"),duration:e.getDuration()}),e.setState(r)}},currentTime:function(){e.trigger(i.S,{position:s("currentTime"),duration:e.getDuration()})},duration:function(){e.trigger(i.S,{position:s("currentTime"),duration:e.getDuration()})},isPaused:function(t){t?e.setState(i.Pa):e.setState(i.Qa)},supports:function(){return!0}}};Object(r.j)(l.prototype,u.a,n.a,o.a,{getName:function(){return{name:"chromecast"}},getQualityLevels:Object(r.d)(["Auto"])});var f=l,v=a(24),T="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1",h=void 0;var m=h||(h=new Promise(function(e,t){window.__onGCastApiAvailable=function(a){a?e(a):t(),delete window.__onGCastApiAvailable},new v.a(T).load().catch(t)})),k={};t.default=function(e,t){var a=k[t.get("id")],n=null;function s(){var e=this,r=t.get("cast")||{};t.set("castState",{available:!1,active:!1,deviceName:""}),a&&(a.off(),a.reset()),(a=new c(r.customAppId)).on("castState",g),a.on("mediaUpdate",T),a.on("mediaUpdate",h),a.on("setPlaylist",d),a.on(i.Ua,function(t){e.trigger(i.Ua,t)}),a.updateCastState(),k[t.get("id")]=a}function d(){t.set("state",i.Ka);var e=t.get("playlistItem");n.updateScreen("Connecting",e.image),a.setPlaylist(t)}function u(){var i;t.get("castClicked")&&!a.getPlayerId()&&a.setPlayerId(t.get("id")),p()&&(e.setFullscreen(!1),n=new f(t.get("id"),t.getConfiguration()),e.castVideo(n,t.get("playlistItem")),n.setService(a),a.addListeners(),(i=a.getMedia())?a.loaded(i):d(),t.on("change:playlist",d),t.on("change:itemReady",l),t.change("captions",v))}function o(r){r?u():n&&function(){var r=t.get("state"),s=r===i.La,c=r===i.Na,u=r===i.Ma,o=t.get("item"),f=t.get("playlist"),v=t.get("playlistItem");if(a.removeListeners(),n&&n.remove(),v&&u&&(void 0===(v=f[o+1])?s=!0:(t.set("item",o+1),t.set("playlistItem",v))),t.set("castActive",!1),t.set("castClicked",!1),e.stopCast(),t.off("change:playlist",d),t.off("change:itemReady",l),v)if(s)e.trigger(i.Ca,{});else if(!c){var T=t.mediaModel;e.playVideo("interaction").catch(function(e){n&&T===t.mediaModel&&n.trigger("error",{message:e.message})})}}()}function l(){a.extractEmbeddedCaptions(),n.setSubtitlesTrack(t.get("captionsIndex"))}function v(e,t){var i=a.getMedia();if(i){var r=a.obtainTrackStyles(t);a.editTracksInfo(i.activeTrackIds,r)}}function T(e){var a=e.field,i=e.value;if(n){"media"===a&&function(e){var a=t.get("playlist"),i=void 0;if(e.media){i=e.media.metadata;var s=a[i.index];Object(r.v)(i.index)&&i.index!==t.get("item")&&(t.attributes.itemReady=!1,t.set("item",i.index),t.set("playlistItem",s),t.set("itemReady",!0));var c=t.get("castState").deviceName,d=c?"Casting to "+c:"";n.updateScreen(d,s.image)}}(i);var s=n.castEventHandlers[a];s&&s(i)}}function h(e){"media"===e.field&&(a.off("mediaUpdate",h),l())}function g(e){var a=t.get("castActive"),i=e.active;a!==i&&o(i),i=i&&p(),t.set("castAvailable",e.available),t.set("castActive",i),t.set("castState",{available:e.available,active:i,deviceName:e.deviceName})}function p(){return a.getPlayerId()===t.get("id")}this.init=function(){return m.then(s)},this.castToggle=function(){}}},74:function(e,t,a){"use strict";a.d(t,"c",function(){return r}),a.d(t,"b",function(){return n}),a.d(t,"a",function(){return s});var i={TIT2:"title",TT2:"title",WXXX:"url",TPE1:"artist",TP1:"artist",TALB:"album",TAL:"album"};function r(e,t){for(var a=e.length,i=void 0,r=void 0,n=void 0,s="",c=t||0;c<a;)if(0!==(i=e[c++])&&3!==i)switch(i>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:s+=String.fromCharCode(i);break;case 12:case 13:r=e[c++],s+=String.fromCharCode((31&i)<<6|63&r);break;case 14:r=e[c++],n=e[c++],s+=String.fromCharCode((15&i)<<12|(63&r)<<6|(63&n)<<0)}return s}function n(e){var t=function(e){for(var t="0x",a=0;a<e.length;a++)e[a]<16&&(t+="0"),t+=e[a].toString(16);return parseInt(t)}(e);return 127&t|(32512&t)>>1|(8323072&t)>>2|(2130706432&t)>>3}function s(){return(arguments.length>0&&void 0!==arguments[0]?arguments[0]:[]).reduce(function(e,t){if(!("value"in t)&&"data"in t&&t.data instanceof ArrayBuffer){var a=new Uint8Array(t.data),s=a.length;t={value:{key:"",data:""}};for(var c=10;c<14&&c<a.length&&0!==a[c];)t.value.key+=String.fromCharCode(a[c]),c++;var d=19,u=a[d];3!==u&&0!==u||(u=a[++d],s--);var o=0;if(1!==u&&2!==u)for(var l=d+1;l<s;l++)if(0===a[l]){o=l-d;break}if(o>0){var f=r(a.subarray(d,d+=o),0);if("PRIV"===t.value.key){if("com.apple.streaming.transportStreamTimestamp"===f){var v=1&n(a.subarray(d,d+=4)),T=n(a.subarray(d,d+=4))+(v?4294967296:0);t.value.data=T}else t.value.data=r(a,d+1);t.value.info=f}else t.value.info=f,t.value.data=r(a,d+1)}else{var h=a[d];t.value.data=1===h||2===h?function(e,t){for(var a=e.length-1,i="",r=t||0;r<a;)254===e[r]&&255===e[r+1]||(i+=String.fromCharCode((e[r]<<8)+e[r+1])),r+=2;return i}(a,d+1):r(a,d+1)}}if(i.hasOwnProperty(t.value.key)&&(e[i[t.value.key]]=t.value.data),t.value.info){var m=e[t.value.key];m!==Object(m)&&(m={},e[t.value.key]=m),m[t.value.info]=t.value.data}else e[t.value.key]=t.value.data;return e},{})}},77:function(e,t,a){"use strict";var i=a(68),r=a(69),n=a(74),s=a(7),c=a(3),d=a(0),u={_itemTracks:null,_textTracks:null,_tracksById:null,_cuesByTrackId:null,_cachedVTTCues:null,_metaCuesByTextTime:null,_currentTextTrackIndex:-1,_unknownCount:0,_activeCues:null,_initTextTracks:function(){this._textTracks=[],this._tracksById={},this._metaCuesByTextTime={},this._cuesByTrackId={},this._cachedVTTCues={},this._unknownCount=0},addTracksListener:function(e,t,a){if(!e)return;if(o(e,t,a),this.instreamMode)return;e.addEventListener?e.addEventListener(t,a):e["on"+t]=a},clearTracks:function(){Object(i.a)(this._itemTracks);var e=this._tracksById&&this._tracksById.nativemetadata;(this.renderNatively||e)&&(v(this.renderNatively,this.video.textTracks),e&&(e.oncuechange=null));this._itemTracks=null,this._textTracks=null,this._tracksById=null,this._cuesByTrackId=null,this._metaCuesByTextTime=null,this._unknownCount=0,this._currentTextTrackIndex=-1,this._activeCues=null,this.renderNatively&&(this.removeTracksListener(this.video.textTracks,"change",this.textTrackChangeHandler),v(this.renderNatively,this.video.textTracks))},clearMetaCues:function(){var e=this._tracksById&&this._tracksById.nativemetadata;e&&(v(this.renderNatively,[e]),e.mode="hidden",e.inuse=!0,this._cachedVTTCues[e._id]={})},clearCueData:function(e){var t=this._cachedVTTCues;t&&t[e]&&(t[e]={},this._tracksById&&(this._tracksById[e].data=[]))},disableTextTrack:function(){if(this._textTracks){var e=this._textTracks[this._currentTextTrackIndex];if(e){e.mode="disabled";var t=e._id;t&&0===t.indexOf("nativecaptions")&&(e.mode="hidden")}}},enableTextTrack:function(){if(this._textTracks){var e=this._textTracks[this._currentTextTrackIndex];e&&(e.mode="showing")}},getSubtitlesTrack:function(){return this._currentTextTrackIndex},removeTracksListener:o,addTextTracks:l,setTextTracks:function(e){if(this._currentTextTrackIndex=-1,!e)return;this._textTracks?(this._unknownCount=0,this._textTracks=this._textTracks.filter(function(e){var t=e._id;return this.renderNatively&&t&&0===t.indexOf("nativecaptions")?(delete this._tracksById[t],!1):(e.name&&0===e.name.indexOf("Unknown")&&this._unknownCount++,!0)},this),delete this._tracksById.nativemetadata):this._initTextTracks();if(e.length)for(var t=0,a=e.length;t<a;t++){var i=e[t];if(!i._id){if("captions"===i.kind||"metadata"===i.kind){if(i._id="native"+i.kind+t,!i.label&&"captions"===i.kind){var n=Object(r.b)(i,this._unknownCount);i.name=n.label,this._unknownCount=n.unknownCount}}else i._id=Object(r.a)(i,this._textTracks.length);if(this._tracksById[i._id])continue;i.inuse=!0}if(i.inuse&&!this._tracksById[i._id])if("metadata"===i.kind)i.mode="hidden",i.oncuechange=k.bind(this),this._tracksById[i._id]=i;else if(T(i.kind)){var c=i.mode,u=void 0;if(i.mode="hidden",!i.cues.length&&i.embedded)continue;if(i.mode=c,this._cuesByTrackId[i._id]&&!this._cuesByTrackId[i._id].loaded){for(var o=this._cuesByTrackId[i._id].cues;u=o.shift();)f(this.renderNatively,i,u);i.mode=c,this._cuesByTrackId[i._id].loaded=!0}m.call(this,i)}}this.renderNatively&&(this.textTrackChangeHandler=this.textTrackChangeHandler||function(){var e=this.video.textTracks,t=Object(d.k)(e,function(e){return(e.inuse||!e._id)&&T(e.kind)});if(!this._textTracks||function(e){if(e.length>this._textTracks.length)return!0;for(var t=0;t<e.length;t++){var a=e[t];if(!a._id||!this._tracksById[a._id])return!0}return!1}.call(this,t))return void this.setTextTracks(e);for(var a=-1,i=0;i<this._textTracks.length;i++)if("showing"===this._textTracks[i].mode){a=i;break}a!==this._currentTextTrackIndex&&this.setSubtitlesTrack(a+1)}.bind(this),this.addTracksListener(this.video.textTracks,"change",this.textTrackChangeHandler),(s.Browser.edge||s.Browser.firefox||s.Browser.safari)&&(this.addTrackHandler=this.addTrackHandler||function(){this.setTextTracks(this.video.textTracks)}.bind(this),this.addTracksListener(this.video.textTracks,"addtrack",this.addTrackHandler)));this._textTracks.length&&this.trigger("subtitlesTracks",{tracks:this._textTracks})},setupSideloadedTracks:function(e){if(!this.renderNatively)return;var t=e===this._itemTracks;t||Object(i.a)(this._itemTracks);if(this._itemTracks=e,!e)return;t||(this.disableTextTrack(),function(){if(!this._textTracks)return;var e=this._textTracks.filter(function(e){return e.embedded||"subs"===e.groupid});this._initTextTracks(),e.forEach(function(e){this._tracksById[e._id]=e}),this._textTracks=e}.call(this),this.addTextTracks(e))},setSubtitlesTrack:function(e){if(!this.renderNatively)return void(this.setCurrentSubtitleTrack&&this.setCurrentSubtitleTrack(e-1));if(!this._textTracks)return;0===e&&this._textTracks.forEach(function(e){e.mode=e.embedded?"hidden":"disabled"});if(this._currentTextTrackIndex===e-1)return;this.disableTextTrack(),this._currentTextTrackIndex=e-1,this._textTracks[this._currentTextTrackIndex]&&(this._textTracks[this._currentTextTrackIndex].mode="showing");this.trigger("subtitlesTrackChanged",{currentTrack:this._currentTextTrackIndex+1,tracks:this._textTracks})},textTrackChangeHandler:null,addTrackHandler:null,addCuesToTrack:function(e){var t=this._tracksById[e.name];if(!t)return;t.source=e.source;for(var a=e.captions||[],r=[],n=!1,s=0;s<a.length;s++){var c=a[s],d=e.name+"_"+c.begin+"_"+c.end;this._metaCuesByTextTime[d]||(this._metaCuesByTextTime[d]=c,r.push(c),n=!0)}n&&r.sort(function(e,t){return e.begin-t.begin});var u=Object(i.b)(r);Array.prototype.push.apply(t.data,u)},addCaptionsCue:function(e){if(!e.text||!e.begin||!e.end)return;var t=e.trackid.toString(),a=this._tracksById&&this._tracksById[t];a||(a={kind:"captions",_id:t,data:[]},this.addTextTracks([a]),this.trigger("subtitlesTracks",{tracks:this._textTracks}));var r=void 0;e.useDTS&&(a.source||(a.source=e.source||"mpegts"));r=e.begin+"_"+e.text;var n=this._metaCuesByTextTime[r];if(!n){n={begin:e.begin,end:e.end,text:e.text},this._metaCuesByTextTime[r]=n;var s=Object(i.b)([n])[0];a.data.push(s)}},addVTTCue:function(e,t){this._tracksById||this._initTextTracks();var a=e.track?e.track:"native"+e.type,i=this._tracksById[a],r="captions"===e.type?"Unknown CC":"ID3 Metadata",n=e.cue;if(!i){var s={kind:e.type,_id:a,label:r,embedded:!0};i=h.call(this,s),this.renderNatively||"metadata"===i.kind?this.setTextTracks(this.video.textTracks):l.call(this,[i])}if(function(e,t,a){var i=e.kind;this._cachedVTTCues[e._id]||(this._cachedVTTCues[e._id]={});var r=this._cachedVTTCues[e._id],n=void 0;switch(i){case"captions":case"subtitles":n=a||Math.floor(20*t.startTime);var s="_"+t.line,c=Math.floor(20*t.endTime),d=r[n+s]||r[n+1+s]||r[n-1+s];return!(d&&Math.abs(d-c)<=1)&&(r[n+s]=c,!0);case"metadata":var u=t.data?new Uint8Array(t.data).join(""):t.text;return n=a||t.startTime+u,r[n]?!1:(r[n]=t.endTime,!0);default:return!1}}.call(this,i,n,t))return this.renderNatively||"metadata"===i.kind?f(this.renderNatively,i,n):i.data.push(n),n;return null},addVTTCuesToTrack:function(e,t){if(!this.renderNatively)return;var a=this._tracksById[e._id];if(!a)return this._cuesByTrackId||(this._cuesByTrackId={}),void(this._cuesByTrackId[e._id]={cues:t,loaded:!1});if(this._cuesByTrackId[e._id]&&this._cuesByTrackId[e._id].loaded)return;var i=void 0;this._cuesByTrackId[e._id]={cues:t,loaded:!0};for(;i=t.shift();)f(this.renderNatively,a,i)},triggerActiveCues:function(e){var t=this;if(!e||!e.length)return void(this._activeCues=null);var a=this._activeCues||[],i=Array.prototype.filter.call(e,function(e){if(a.some(function(t){return function(e,t){return e.startTime===t.startTime&&e.endTime===t.endTime&&e.text===t.text&&e.data===t.data&&e.value===t.value}(e,t)}))return!1;if(e.data||e.value)return!0;if(e.text){var i=JSON.parse(e.text),r=e.startTime,n={metadataTime:r,metadata:i};i.programDateTime&&(n.programDateTime=i.programDateTime),i.metadataType&&(n.metadataType=i.metadataType,delete i.metadataType),t.trigger(c.K,n)}return!1});if(i.length){var r=Object(n.a)(i),s=i[0].startTime;this.trigger(c.K,{metadataType:"id3",metadataTime:s,metadata:r})}this._activeCues=Array.prototype.slice.call(e)},renderNatively:!1};function o(e,t,a){e&&(e.removeEventListener?e.removeEventListener(t,a):e["on"+t]=null)}function l(e){var t=this;e&&(this._textTracks||this._initTextTracks(),e.forEach(function(e){if(!e.kind||T(e.kind)){var a=h.call(t,e);m.call(t,a),e.file&&(e.data=[],Object(i.c)(e,function(e){t.addVTTCuesToTrack(a,e)},function(e){t.trigger(c.Ua,e)}))}}),this._textTracks&&this._textTracks.length&&this.trigger("subtitlesTracks",{tracks:this._textTracks}))}function f(e,t,a){var i=a;s.Browser.ie&&e&&(i=new window.TextTrackCue(a.startTime,a.endTime,a.text)),s.Browser.ie?function(e,t){var a=[],i=e.mode;e.mode="hidden";for(var r=e.cues,n=r.length-1;n>=0&&r[n].startTime>t.startTime;n--)a.push(r[n]),e.removeCue(r[n]);e.addCue(t),a.forEach(function(t){return e.addCue(t)}),e.mode=i}(t,i):t.addCue(i)}function v(e,t){t&&t.length&&Object(d.i)(t,function(t){if(!(s.Browser.ie&&e&&/^(native|subtitle|cc)/.test(t._id))){t.mode="disabled",t.mode="hidden";for(var a=t.cues.length;a--;)t.removeCue(t.cues[a]);t.embedded||(t.mode="disabled"),t.inuse=!1}})}function T(e){return"subtitles"===e||"captions"===e}function h(e){var t=void 0,a=Object(r.b)(e,this._unknownCount),i=a.label;if(this._unknownCount=a.unknownCount,this.renderNatively||"metadata"===e.kind){var n=this.video.textTracks;(t=Object(d.m)(n,{label:i}))||(t=this.video.addTextTrack(e.kind,i,e.language||"")),t.default=e.default,t.mode="disabled",t.inuse=!0}else(t=e).data=t.data||[];return t._id||(t._id=Object(r.a)(e,this._textTracks.length)),t}function m(e){this._textTracks.push(e),this._tracksById[e._id]=e}function k(e){this.triggerActiveCues(e.currentTarget.activeCues)}t.a=u}}]);