import InputStream from './SabreAMF/InputStream.js';
import OutputStream from './SabreAMF/OutputStream.js';
import AMF3Serializer from './SabreAMF/AMF3/Serializer.js';
import AMF3Deserializer from './SabreAMF/AMF3/Deserializer.js';

/**
 * AMFProcessor for handling AMF data compression/decompression
 */
export default class AMFProcessor {
    constructor() {
        // Polyfill for pako if needed - assuming pako is available globally
        if (!window.pako) {
            console.error('Pako library required for compression/decompression');
        }
    }

    /**
     * Compress JSON data to AMF3 format
     * @param {Object} data JSON data
     * @return {Uint8Array} Compressed AMF data
     */
    compressAMF3(data) {
        console.log('Starting AMF3 compression', data);
        const amfOutputStream = new OutputStream();
        const amfSerializer = new AMF3Serializer(amfOutputStream);
        amfSerializer.writeAMFData(data);
        
        const rawData = amfOutputStream.getRawData();
        console.log('AMF3 serialized size:', rawData.length);
        
        // Compress using pako (gzip implementation)
        const compressed = window.pako.deflate(rawData);
        console.log('Compressed size:', compressed.length);
        return compressed;
    }

    /**
     * Decompress AMF3 data to JSON
     * @param {ArrayBuffer|Uint8Array} compressedData Compressed AMF data
     * @return {Object} Decompressed JSON data
     */
    decompressAMF3(compressedData) {
        console.log('Starting AMF3 decompression');
        
        // Convert to Uint8Array if ArrayBuffer
        if (compressedData instanceof ArrayBuffer) {
            compressedData = new Uint8Array(compressedData);
        }
        
        try {
            // Decompress using pako
            console.log('Decompressing data with pako');
            const decompressedData = window.pako.inflate(compressedData);
            console.log('Decompressed size:', decompressedData.length);
            
            if (decompressedData.length === 0) {
                console.warn('Warning: Decompressed data is empty');
                return [];
            }
            
            // Logging first few bytes to debug
            let firstBytes = '';
            for (let i = 0; i < Math.min(16, decompressedData.length); i++) {
                firstBytes += decompressedData[i].toString(16).padStart(2, '0') + ' ';
            }
            console.log('First bytes:', firstBytes);
            
            // Parse AMF3 data
            const amfInputStream = new InputStream(decompressedData);
            const amfDeserializer = new AMF3Deserializer(amfInputStream);
            
            let result = amfDeserializer.readAMFData();
            console.log('Raw parsed AMF3 data:', result);
            
            // If result is an empty object but should be an array, convert it
            if (result && typeof result === 'object' && Object.keys(result).length === 0) {
                console.log('Converting empty object to array');
                result = [];
            }
            
            // Fix for associative arrays
            if (result && typeof result === 'object' && !Array.isArray(result)) {
                const keys = Object.keys(result);
                console.log(`Object has ${keys.length} keys`);
                
                // Check if all keys are numeric
                const allNumeric = keys.every(key => !isNaN(parseInt(key)));
                const sequential = allNumeric && keys.every((key, index) => parseInt(key) === index);
                
                if (allNumeric && sequential && keys.length > 0) {
                    // Convert to array if all keys are sequential numeric
                    console.log('Converting numeric object to array');
                    const arr = [];
                    keys.forEach(key => arr.push(result[key]));
                    result = arr;
                }
            }
            
            console.log('Final processed result:', result);
            return result;
        } catch (error) {
            console.error('AMF3 decompression error:', error);
            throw new Error(`AMF3 decompression failed: ${error.message}`);
        }
    }

    /**
     * Compress JSON data using regular gzip (for old formats)
     * @param {string} jsonData JSON string
     * @return {Uint8Array} Compressed data
     */
    compressOldLocale(jsonData) {
        console.log('Starting old locale compression');
        return window.pako.deflate(jsonData);
    }

    /**
     * Decompress old format data to JSON
     * @param {ArrayBuffer|Uint8Array} compressedData Compressed data
     * @return {Object} Decompressed JSON data
     */
    decompressOldLocale(compressedData) {
        console.log('Starting old locale decompression');
        
        // Convert to Uint8Array if ArrayBuffer
        if (compressedData instanceof ArrayBuffer) {
            compressedData = new Uint8Array(compressedData);
        }
        
        try {
            // Decompress using pako
            const decompressedData = window.pako.inflate(compressedData);
            console.log('Decompressed size:', decompressedData.length);
            
            // Convert to string
            const decoder = new TextDecoder('utf-8');
            const jsonString = decoder.decode(decompressedData);
            
            // Parse JSON
            const result = JSON.parse(jsonString);
            console.log('Parsed JSON data:', result);
            return result;
        } catch (error) {
            console.error('Old locale decompression error:', error);
            throw new Error(`Decompression failed: ${error.message}`);
        }
    }
}
