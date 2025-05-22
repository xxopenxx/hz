/**
 * Output stream for writing binary data
 */
export default class OutputStream {
    constructor() {
        this.rawData = new Uint8Array(0);
    }

    /**
     * Write a buffer to the output stream
     * @param {string|Uint8Array} str Data to write
     */
    writeBuffer(str) {
        let buffer;
        if (typeof str === 'string') {
            const encoder = new TextEncoder();
            buffer = encoder.encode(str);
        } else {
            buffer = str;
        }

        const newData = new Uint8Array(this.rawData.length + buffer.length);
        newData.set(this.rawData);
        newData.set(buffer, this.rawData.length);
        this.rawData = newData;
    }

    /**
     * Write a byte to the output stream
     * @param {number} byte Byte value
     */
    writeByte(byte) {
        this.writeBuffer(new Uint8Array([byte & 0xFF]));
    }

    /**
     * Write a 16-bit integer to the output stream
     * @param {number} int Integer value
     */
    writeInt(int) {
        this.writeBuffer(new Uint8Array([
            (int >> 8) & 0xFF,
            int & 0xFF
        ]));
    }

    /**
     * Write a 64-bit double to the output stream
     * @param {number} double Double value
     */
    writeDouble(double) {
        const buffer = new ArrayBuffer(8);
        const view = new DataView(buffer);
        view.setFloat64(0, double, false); // Big-endian
        this.writeBuffer(new Uint8Array(buffer));
    }

    /**
     * Write a 32-bit long to the output stream
     * @param {number} long Long value
     */
    writeLong(long) {
        this.writeBuffer(new Uint8Array([
            (long >> 24) & 0xFF,
            (long >> 16) & 0xFF,
            (long >> 8) & 0xFF,
            long & 0xFF
        ]));
    }

    /**
     * Get the raw binary data
     * @return {Uint8Array} Binary data
     */
    getRawData() {
        return this.rawData;
    }
}
