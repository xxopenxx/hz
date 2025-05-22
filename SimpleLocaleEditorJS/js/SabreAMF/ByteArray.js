/**
 * ByteArray class for binary data handling
 */
export default class ByteArray {
    /**
     * @param {string|Uint8Array} data Initial data
     */
    constructor(data = '') {
        if (typeof data === 'string') {
            const encoder = new TextEncoder();
            this.data = encoder.encode(data);
        } else {
            this.data = data;
        }
    }

    /**
     * Get the binary data
     * @return {Uint8Array} Binary data
     */
    getData() {
        return this.data;
    }

    /**
     * Set the binary data
     * @param {string|Uint8Array} data The data to set
     */
    setData(data) {
        if (typeof data === 'string') {
            const encoder = new TextEncoder();
            this.data = encoder.encode(data);
        } else {
            this.data = data;
        }
    }

    /**
     * Get the data as string
     * @return {string} String representation
     */
    toString() {
        const decoder = new TextDecoder();
        return decoder.decode(this.data);
    }
}
