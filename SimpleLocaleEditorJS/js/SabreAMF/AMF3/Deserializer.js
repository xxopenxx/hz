import AMF3Const from './Const.js';
import ByteArray from '../ByteArray.js';
import TypedObject from '../TypedObject.js';
import ClassMapper from '../ClassMapper.js';

/**
 * AMF3 Deserializer for decoding data
 */
export default class Deserializer {
    /**
     * @param {InputStream} stream Input stream
     */
    constructor(stream) {
        this.stream = stream;
        this.objectcount = 0;
        this.storedStrings = [];
        this.storedObjects = [];
        this.storedClasses = [];
        console.log('AMF3 Deserializer created');
    }

    /**
     * Read AMF data
     * @param {number|null} settype Force specific type
     * @return {*} Decoded data
     */
    readAMFData(settype = null) {
        try {
            if (settype === null) {
                if (this.stream.getBytesAvailable() === 0) {
                    console.warn('End of stream reached, no more data to read');
                    return null;
                }
                settype = this.stream.readByte();
                console.log(`Reading AMF3 data of type: 0x${settype.toString(16)}`);
            }

            switch (settype) {
                case AMF3Const.DT_UNDEFINED:
                    console.log('Decoded: undefined');
                    return undefined;
                case AMF3Const.DT_NULL:
                    console.log('Decoded: null');
                    return null;
                case AMF3Const.DT_BOOL_FALSE:
                    console.log('Decoded: false');
                    return false;
                case AMF3Const.DT_BOOL_TRUE:
                    console.log('Decoded: true');
                    return true;
                case AMF3Const.DT_INTEGER:
                    const int = this.readInt();
                    console.log(`Decoded integer: ${int}`);
                    return int;
                case AMF3Const.DT_NUMBER:
                    const num = this.stream.readDouble();
                    console.log(`Decoded number: ${num}`);
                    return num;
                case AMF3Const.DT_STRING:
                    const str = this.readString();
                    console.log(`Decoded string: "${str.substring(0, 50)}${str.length > 50 ? '...' : ''}"`);
                    return str;
                case AMF3Const.DT_XML:
                    const xml = this.readString();
                    console.log('Decoded XML');
                    return xml;
                case AMF3Const.DT_DATE:
                    const date = this.readDate();
                    console.log(`Decoded date: ${date}`);
                    return date;
                case AMF3Const.DT_ARRAY:
                    const array = this.readArray();
                    console.log(`Decoded array with ${Array.isArray(array) ? array.length : Object.keys(array).length} items`);
                    return array;
                case AMF3Const.DT_OBJECT:
                    const obj = this.readObject();
                    console.log('Decoded object');
                    return obj;
                case AMF3Const.DT_XMLSTRING:
                    const xmlStr = this.readXMLString();
                    console.log('Decoded XML string');
                    return xmlStr;
                case AMF3Const.DT_BYTEARRAY:
                    const byteArray = this.readByteArray();
                    console.log('Decoded ByteArray');
                    return byteArray;
                default:
                    const errorMsg = `Unsupported type: 0x${settype.toString(16).padStart(2, '0').toUpperCase()}`;
                    console.error(errorMsg);
                    throw new Error(errorMsg);
            }
        } catch (error) {
            console.error('Error reading AMF3 data:', error);
            throw error;
        }
    }

    /**
     * Read an object
     * @return {Object} Decoded object
     */
    readObject() {
        const objInfo = this.readU29();
        console.log(`Object info: 0x${objInfo.toString(16)}`);
        
        const storedObject = (objInfo & 0x01) === 0;
        const objInfoShifted = objInfo >> 1;

        if (storedObject) {
            const objectReference = objInfoShifted;
            if (objectReference >= this.storedObjects.length) {
                throw new Error(`Object reference #${objectReference} not found (have ${this.storedObjects.length} objects)`);
            }
            return this.storedObjects[objectReference];
        }

        const storedClass = (objInfoShifted & 0x01) === 0;
        const objInfoShifted2 = objInfoShifted >> 1;
        
        let encodingType, propertyNames, className;
        
        if (storedClass) {
            const classReference = objInfoShifted2;
            if (classReference >= this.storedClasses.length) {
                throw new Error(`Class reference #${classReference} not found (have ${this.storedClasses.length} classes)`);
            }
            
            encodingType = this.storedClasses[classReference].encodingType;
            propertyNames = this.storedClasses[classReference].propertyNames;
            className = this.storedClasses[classReference].className;
            console.log(`Using stored class: ${className}`);
        } else {
            className = this.readString();
            encodingType = objInfoShifted2 & 0x03;
            propertyNames = [];
            console.log(`New class: ${className}, encoding type: ${encodingType}`);
        }
        
        // Create object based on class mapping
        let resultObject;
        
        if (className) {
            const localClassName = ClassMapper.getLocalClass(className);
            if (localClassName) {
                // In JS we'd need a factory to create instances
                resultObject = { 
                    __amfClassName: localClassName 
                };
                console.log(`Mapped to local class: ${localClassName}`);
            } else {
                resultObject = new TypedObject(className, {});
                console.log(`Created TypedObject for: ${className}`);
            }
        } else {
            resultObject = {};
            console.log('Created generic object');
        }
        
        // Store object reference
        this.storedObjects.push(resultObject);
        
        if (encodingType & AMF3Const.ET_EXTERNALIZED) {
            console.log('Object uses external serialization');
            if (!storedClass) {
                this.storedClasses.push({
                    className: className,
                    encodingType: encodingType,
                    propertyNames: propertyNames
                });
            }
            
            const externalData = this.readAMFData();
            if (resultObject instanceof TypedObject) {
                resultObject.setAMFData({ externalizedData: externalData });
            } else {
                resultObject.externalizedData = externalData;
            }
        } else {
            let properties = {};
            
            if (encodingType & AMF3Const.ET_SERIAL) {
                console.log('Object uses dynamic serialization');
                if (!storedClass) {
                    this.storedClasses.push({
                        className: className,
                        encodingType: encodingType,
                        propertyNames: propertyNames
                    });
                }
                
                let propertyName = this.readString();
                while (propertyName !== "") {
                    propertyNames.push(propertyName);
                    properties[propertyName] = this.readAMFData();
                    propertyName = this.readString();
                }
                console.log(`Read ${Object.keys(properties).length} dynamic properties`);
            } else {
                console.log('Object uses static property list');
                if (!storedClass) {
                    const propertyCount = objInfoShifted2 >> 2;
                    console.log(`Reading ${propertyCount} static properties`);
                    for (let i = 0; i < propertyCount; i++) {
                        propertyNames.push(this.readString());
                    }
                    
                    this.storedClasses.push({
                        className: className,
                        encodingType: encodingType,
                        propertyNames: propertyNames
                    });
                }
                
                for (const propertyName of propertyNames) {
                    properties[propertyName] = this.readAMFData();
                }
            }
            
            if (resultObject instanceof TypedObject) {
                resultObject.setAMFData(properties);
            } else {
                Object.assign(resultObject, properties);
            }
            console.log(`Populated object with ${Object.keys(properties).length} properties`);
        }
        
        return resultObject;
    }

    /**
     * Read an array
     * @return {Array|Object} Decoded array
     */
    readArray() {
        const arrId = this.readU29();
        console.log(`Array ID: 0x${arrId.toString(16)}`);
        
        if ((arrId & 0x01) === 0) {
            const arrRef = arrId >> 1;
            if (arrRef >= this.storedObjects.length) {
                throw new Error(`Undefined array reference: ${arrRef} (have ${this.storedObjects.length} objects)`);
            }
            console.log(`Using stored array reference: ${arrRef}`);
            return this.storedObjects[arrRef]; 
        }
        
        const arrLen = arrId >> 1;
        console.log(`New array with dense portion length: ${arrLen}`);
        
        // Create an Object for better handling of associative arrays
        const data = {};
        // Store reference early to handle circular references
        this.storedObjects.push(data);
        
        // Handle associative keys first - these are non-numeric properties
        let hasAssociativeKeys = false;
        let key = this.readString();
        
        while (key !== "") {
            hasAssociativeKeys = true;
            console.log(`Reading associative key: "${key}"`);
            data[key] = this.readAMFData();
            key = this.readString();
        }
        
        // Then handle numeric indices - these are array elements
        if (arrLen > 0) {
            for (let i = 0; i < arrLen; i++) {
                data[i] = this.readAMFData();
            }
        }
        
        // Convert to array if we have numeric indices only
        let result;
        if (arrLen > 0 && !hasAssociativeKeys) {
            // If it's a pure array with no associative keys, convert to real array
            result = [];
            for (let i = 0; i < arrLen; i++) {
                result.push(data[i]);
            }
            // Update the reference to point to the array
            this.storedObjects[this.storedObjects.length - 1] = result;
        } else {
            // If it has associative keys or is empty, keep as object
            result = data;
        }
        
        const numericCount = arrLen;
        const associativeCount = hasAssociativeKeys ? Object.keys(data).filter(k => isNaN(parseInt(k))).length : 0;
        
        console.log(`Array complete with ${numericCount} numeric elements and ${associativeCount} associative elements`);
        return result;
    }

    /**
     * Read a string
     * @return {string} Decoded string
     */
    readString() {
        const strref = this.readU29();
        
        if ((strref & 0x01) === 0) {
            const ref = strref >> 1;
            if (ref >= this.storedStrings.length) {
                throw new Error(`Undefined string reference: ${ref} (have ${this.storedStrings.length} strings)`);
            }
            return this.storedStrings[ref];
        }
        
        const strlen = strref >> 1;
        if (strlen === 0) return '';
        
        const buffer = this.stream.readBuffer(strlen);
        const decoder = new TextDecoder();
        const str = decoder.decode(buffer);
        
        if (str !== "") {
            this.storedStrings.push(str);
        }
        
        return str;
    }

    /**
     * Read XML string
     * @return {string} XML string
     */
    readXMLString() {
        const strref = this.readU29();
        const strlen = strref >> 1;
        
        const buffer = this.stream.readBuffer(strlen);
        const decoder = new TextDecoder();
        return decoder.decode(buffer);
    }

    /**
     * Read ByteArray
     * @return {ByteArray} ByteArray object
     */
    readByteArray() {
        const strref = this.readU29();
        const strlen = strref >> 1;
        
        const buffer = this.stream.readBuffer(strlen);
        return new ByteArray(buffer);
    }

    /**
     * Read a 29-bit unsigned integer
     * @return {number} Unsigned integer value
     */
    readU29() {
        let count = 1;
        let u29 = 0;
        
        let byte = this.stream.readByte();
        
        while ((byte & 0x80) !== 0 && count < 4) {
            u29 <<= 7;
            u29 |= (byte & 0x7f);
            byte = this.stream.readByte();
            count++;
        }
        
        if (count < 4) {
            u29 <<= 7;
            u29 |= byte;
        } else {
            // Use all 8 bits from the 4th byte
            u29 <<= 8;
            u29 |= byte;
        }
        
        return u29;
    }

    /**
     * Read a signed integer
     * @return {number} Signed integer value
     */
    readInt() {
        const int = this.readU29();
        
        // Check if the integer is signed
        if ((int & 0x18000000) === 0x18000000) {
            return ((int ^ 0x1fffffff) * -1) - 1;
        } else if ((int & 0x10000000) === 0x10000000) {
            // Remove the signed flag
            return int & 0x0fffffff;
        }
        
        return int;
    }

    /**
     * Read a date
     * @return {Date} Date object
     */
    readDate() {
        const dateref = this.readU29();
        
        if ((dateref & 0x01) === 0) {
            const ref = dateref >> 1;
            if (ref >= this.storedObjects.length) {
                throw new Error(`Undefined date reference: ${ref} (have ${this.storedObjects.length} objects)`);
            }
            return this.storedObjects[ref];
        }
        
        const timestamp = this.stream.readDouble();
        const date = new Date(timestamp);
        
        this.storedObjects.push(date);
        return date;
    }
}
