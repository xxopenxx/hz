/**
 * Input stream for reading binary data
 */
export default class InputStream {
    /**
     * @param {string|ArrayBuffer|Uint8Array} data Binary data
     */
    constructor(data) {
        this.cursor = 0;
        
        if (typeof data === 'string') {
            // Convert string to binary
            const encoder = new TextEncoder();
            this.rawData = encoder.encode(data);
        } else if (data instanceof ArrayBuffer) {
            this.rawData = new Uint8Array(data);
        } else if (data instanceof Uint8Array) {
            this.rawData = data;
        } else {
            throw new Error('Input data must be string, ArrayBuffer or Uint8Array');
        }
        
        console.log(`InputStream created with ${this.rawData.length} bytes of data`);
    }

    /**
     * Read buffer of specified length
     * @param {number} length Number of bytes to read
     * @return {Uint8Array} Buffer containing the data
     */
    readBuffer(length) {
        if (this.cursor + length > this.rawData.length) {
            throw new Error(`Buffer underrun at position ${this.cursor}. Trying to fetch ${length} bytes, but only ${this.rawData.length - this.cursor} available.`);
        }
        
        const buffer = this.rawData.slice(this.cursor, this.cursor + length);
        this.cursor += length;
        return buffer;
    }

    /**
     * Read a single byte
     * @return {number} Byte value
     */
    readByte() {
        if (this.cursor >= this.rawData.length) {
            throw new Error(`End of stream reached at position ${this.cursor}`);
        }
        return this.rawData[this.cursor++];
    }

    /**
     * Read a 16-bit integer
     * @return {number} Int value
     */
    readInt() {
        const buffer = this.readBuffer(2);
        return (buffer[0] << 8) | buffer[1];
    }

    /**
     * Read a 64-bit double
     * @return {number} Double value
     */
    readDouble() {
        const buffer = this.readBuffer(8);
        const dataView = new DataView(buffer.buffer, buffer.byteOffset, buffer.byteLength);
        return dataView.getFloat64(0, false); // Big-endian
    }

    /**
     * Read a 32-bit long
     * @return {number} Long value
     */
    readLong() {
        const buffer = this.readBuffer(4);
        return (buffer[0] << 24) | (buffer[1] << 16) | (buffer[2] << 8) | buffer[3];
    }

    /**
     * Read a 24-bit integer
     * @return {number} Int24 value
     */
    readInt24() {
        const buffer = this.readBuffer(3);
        return (buffer[0] << 16) | (buffer[1] << 8) | buffer[2];
    }
    
    /**
     * Get current cursor position
     * @return {number} Current position
     */
    getPosition() {
        return this.cursor;
    }
    
    /**
     * Get remaining bytes
     * @return {number} Remaining bytes
     */
    getBytesAvailable() {
        return this.rawData.length - this.cursor;
    }
}
