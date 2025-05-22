/**
 * TypedObject for handling AMF class objects
 */
export default class TypedObject {
    /**
     * @param {string} classname AMF class name
     * @param {Object} data Object data
     */
    constructor(classname, data) {
        this.amfClassName = classname;
        this.amfData = data;
    }

    /**
     * Get the AMF class name
     * @return {string} Class name
     */
    getAMFClassName() {
        return this.amfClassName;
    }

    /**
     * Get the AMF data
     * @return {Object} Object data
     */
    getAMFData() {
        return this.amfData;
    }

    /**
     * Set the AMF class name
     * @param {string} classname Class name
     */
    setAMFClassName(classname) {
        this.amfClassName = classname;
    }

    /**
     * Set the AMF data
     * @param {Object} data Object data
     */
    setAMFData(data) {
        this.amfData = data;
    }
}
