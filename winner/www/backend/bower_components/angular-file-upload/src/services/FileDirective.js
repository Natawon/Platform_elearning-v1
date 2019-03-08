'use strict';


import CONFIG from './../config.json';


let {
    extend
    } = angular;


export default () => {


    class FileDirective {
        /**
         * Creates instance of {FileDirective} object
         * @param {Object} options
         * @param {Object} options.uploader
         * @param {HTMLElement} options.element
         * @param {Object} options.courses
         * @param {String} options.prop
         * @constructor
         */
        constructor(options) {
            extend(this, options);
            this.uploader._directives[this.prop].push(this);
            this._saveLinks();
            this.bind();
        }
        /**
         * Binds courses handles
         */
        bind() {
            for(var key in this.events) {
                var prop = this.events[key];
                this.element.bind(key, this[prop]);
            }
        }
        /**
         * Unbinds courses handles
         */
        unbind() {
            for(var key in this.events) {
                this.element.unbind(key, this.events[key]);
            }
        }
        /**
         * Destroys directive
         */
        destroy() {
            var index = this.uploader._directives[this.prop].indexOf(this);
            this.uploader._directives[this.prop].splice(index, 1);
            this.unbind();
            // this.element = null;
        }
        /**
         * Saves links to functions
         * @private
         */
        _saveLinks() {
            for(var key in this.events) {
                var prop = this.events[key];
                this[prop] = this[prop].bind(this);
            }
        }
    }


    /**
     * Map of courses
     * @type {Object}
     */
    FileDirective.prototype.events = {};


    return FileDirective;
}


module.exports.$inject = [
];