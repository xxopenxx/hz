/**
 * AMF3 Wrapper for wrapping AMF3 data into AMF0
 */
export default class Wrapper {
    /**
     * @param {*} data Data to wrap
     */
    constructor(data) {
        this.data = data;
    }
    
    /**
     * Get the wrapped data
     * @return {*} The wrapped data
     */
    getData() {
        return this.data;
    }
    
    /**
     * Set the wrapped data
     * @param {*} data The data to wrap
     */
    setData(data) {
        this.data = data;
    }
}
