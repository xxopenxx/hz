import AMF3Const from './Const.js';
import ByteArray from '../ByteArray.js';
import ClassMapper from '../ClassMapper.js';

/**
 * AMF3 Serializer for encoding data
 */
export default class Serializer {
    /**
     * @param {OutputStream} stream Output stream
     */
    constructor(stream) {
        this.stream = stream;
    }

    /**
     * Write AMF data
     * @param {*} data Data to write
     * @param {number|null} forcetype Force specific type
     */
    writeAMFData(data, forcetype = null) {
        let type = forcetype;

        if (type === null) {
            // Auto-detect type
            if (data === null) {
                type = AMF3Const.DT_NULL;
            } else if (typeof data === 'boolean') {
                type = data ? AMF3Const.DT_BOOL_TRUE : AMF3Const.DT_BOOL_FALSE;
            } else if (typeof data === 'number') {
                if (Number.isInteger(data)) {
                    // Check integer range for AMF3
                    if (data > 0xFFFFFFF || data < -268435456) {
                        type = AMF3Const.DT_NUMBER;
                    } else {
                        type = AMF3Const.DT_INTEGER;
                    }
                } else {
                    type = AMF3Const.DT_NUMBER;
                }
            } else if (typeof data === 'string') {
                type = AMF3Const.DT_STRING;
            } else if (Array.isArray(data)) {
                type = AMF3Const.DT_ARRAY;
            } else if (data instanceof Date) {
                type = AMF3Const.DT_DATE;
            } else if (data instanceof ByteArray) {
                type = AMF3Const.DT_BYTEARRAY;
            } else if (typeof data === 'object') {
                type = AMF3Const.DT_OBJECT;
            } else {
                throw new Error(`Unhandled data-type: ${typeof data}`);
            }
        }

        this.stream.writeByte(type);

        switch (type) {
            case AMF3Const.DT_NULL:
            case AMF3Const.DT_BOOL_FALSE:
            case AMF3Const.DT_BOOL_TRUE:
                break;
            case AMF3Const.DT_INTEGER:
                this.writeInt(data);
                break;
            case AMF3Const.DT_NUMBER:
                this.stream.writeDouble(data);
                break;
            case AMF3Const.DT_STRING:
                this.writeString(data);
                break;
            case AMF3Const.DT_DATE:
                this.writeDate(data);
                break;
            case AMF3Const.DT_ARRAY:
                this.writeArray(data);
                break;
            case AMF3Const.DT_OBJECT:
                this.writeObject(data);
                break;
            case AMF3Const.DT_BYTEARRAY:
                this.writeByteArray(data);
                break;
            default:
                throw new Error(`Unsupported type: ${type}`);
        }
    }

    /**
     * Write an integer
     * @param {number} int Integer value
     */
    writeInt(int) {
        if ((int & 0xffffff80) === 0) {
            this.stream.writeByte(int & 0x7f);
            return;
        }

        if ((int & 0xffffc000) === 0) {
            this.stream.writeByte((int >> 7) | 0x80);
            this.stream.writeByte(int & 0x7f);
            return;
        }

        if ((int & 0xffe00000) === 0) {
            this.stream.writeByte((int >> 14) | 0x80);
            this.stream.writeByte((int >> 7) | 0x80);
            this.stream.writeByte(int & 0x7f);
            return;
        }

        this.stream.writeByte((int >> 22) | 0x80);
        this.stream.writeByte((int >> 15) | 0x80);
        this.stream.writeByte((int >> 8) | 0x80);
        this.stream.writeByte(int & 0xff);
    }

    /**
     * Write a string
     * @param {string} str String value
     */
    writeString(str) {
        const strref = str.length << 1 | 0x01;
        this.writeInt(strref);
        this.stream.writeBuffer(str);
    }

    /**
     * Write a ByteArray
     * @param {ByteArray} data ByteArray object
     */
    writeByteArray(data) {
        this.writeString(data.toString());
    }

    /**
     * Write an array
     * @param {Array} arr Array data
     */
    writeArray(arr) {
        if (this.isPureArray(arr)) {
            // Write numeric array
            const arrLen = arr.length;
            const arrId = (arrLen << 1) | 0x01;
            
            this.writeInt(arrId);
            this.writeString('');
            
            for (const value of arr) {
                this.writeAMFData(value);
            }
        } else {
            // Write associative array
            this.writeInt(1);
            for (const [key, value] of Object.entries(arr)) {
                this.writeString(key);
                this.writeAMFData(value);
            }
            this.writeString('');
        }
    }

    /**
     * Write an object
     * @param {Object} data Object data
     */
    writeObject(data) {
        let encodingType = AMF3Const.ET_PROPLIST;
        let classname = '';
        
        // Handle TypedObject interface
        if (data.getAMFClassName && data.getAMFData) {
            classname = data.getAMFClassName();
            data = data.getAMFData();
        } else {
            // Try to find class name from class mapper
            const className = data.constructor.name;
            const remoteName = ClassMapper.getRemoteClass(className);
            if (remoteName) {
                classname = remoteName;
            }
        }
        
        // For simple objects just use property list encoding
        let objectInfo = 0x03;
        objectInfo |= encodingType << 2;
        
        if (encodingType === AMF3Const.ET_PROPLIST) {
            const properties = Object.keys(data);
            const propertyCount = properties.length;
            
            objectInfo |= (propertyCount << 4);
            
            this.writeInt(objectInfo);
            this.writeString(classname);
            
            // Write all property names
            for (const key of properties) {
                this.writeString(key);
            }
            
            // Write all property values
            for (const key of properties) {
                this.writeAMFData(data[key]);
            }
        }
    }

    /**
     * Write a date object
     * @param {Date} date Date object
     */
    writeDate(date) {
        // Always sending actual date objects, never references
        this.writeInt(0x01);
        this.stream.writeDouble(date.getTime());
    }

    /**
     * Check if array has only numeric indexes and no gaps
     * @param {Array} arr Array to check
     * @return {boolean} True if pure array, false if associative
     */
    isPureArray(arr) {
        if (!Array.isArray(arr)) return false;
        
        for (let i = 0; i < arr.length; i++) {
            if (!(i in arr)) return false;
        }
        
        return true;
    }
}
