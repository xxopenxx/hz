import Const from './Const.js';
import AMF0Serializer from './AMF0/Serializer.js';
import AMF0Deserializer from './AMF0/Deserializer.js';
import AMF3Wrapper from './AMF3/Wrapper.js';

/**
 * Message class for AMF request/response packages
 */
export default class Message {
    constructor() {
        this.clientType = 0;
        this.bodies = [];
        this.headers = [];
        this.encoding = Const.AMF0;
    }

    /**
     * Serialize message to binary format
     * @param {OutputStream} stream Output stream
     */
    serialize(stream) {
        this.outputStream = stream;
        stream.writeByte(0x00);
        stream.writeByte(this.encoding);
        stream.writeInt(this.headers.length);
        
        // Write headers
        for (const header of this.headers) {
            const serializer = new AMF0Serializer(stream);
            serializer.writeString(header.name);
            stream.writeByte(header.required === true ? 1 : 0);
            stream.writeLong(-1);
            serializer.writeAMFData(header.data);
        }
        
        // Write bodies
        stream.writeInt(this.bodies.length);
        
        for (const body of this.bodies) {
            const serializer = new AMF0Serializer(stream);
            serializer.writeString(body.target);
            serializer.writeString(body.response);
            stream.writeLong(-1);
            
            switch (this.encoding) {
                case Const.AMF0:
                    serializer.writeAMFData(body.data);
                    break;
                case Const.AMF3:
                    serializer.writeAMFData(new AMF3Wrapper(body.data));
                    break;
            }
        }
    }

    /**
     * Deserialize binary data into message
     * @param {InputStream} stream Input stream
     */
    deserialize(stream) {
        this.headers = [];
        this.bodies = [];
        this.inputStream = stream;
        
        stream.readByte(); // Version
        this.clientType = stream.readByte();
        
        const deserializer = new AMF0Deserializer(stream);
        
        // Read headers
        const totalHeaders = stream.readInt();
        
        for (let i = 0; i < totalHeaders; i++) {
            const header = {
                name: deserializer.readString(),
                required: stream.readByte() === 1
            };
            
            stream.readLong(); // Header length, always -1
            header.data = deserializer.readAMFData(null, true);
            this.headers.push(header);
        }
        
        // Read bodies
        const totalBodies = stream.readInt();
        
        for (let i = 0; i < totalBodies; i++) {
            let target;
            
            try {
                target = deserializer.readString();
            } catch (e) {
                // Could not fetch next body, stop decoding
                break;
            }
            
            const body = {
                target: target,
                response: deserializer.readString(),
                length: stream.readLong(),
                data: deserializer.readAMFData(null, true)
            };
            
            // Handle AMF3 wrapped data
            if (typeof body.data === 'object' && body.data.getData) {
                body.data = body.data.getData();
                this.encoding = Const.AMF3;
            } else if (Array.isArray(body.data) && body.data[0] && body.data[0].getData) {
                body.data = body.data[0].getData();
                this.encoding = Const.AMF3;
            }
            
            this.bodies.push(body);
        }
    }

    /**
     * Get client type
     * @return {number} Client type
     */
    getClientType() {
        return this.clientType;
    }

    /**
     * Get message bodies
     * @return {Array} Bodies
     */
    getBodies() {
        return this.bodies;
    }

    /**
     * Get message headers
     * @return {Array} Headers
     */
    getHeaders() {
        return this.headers;
    }

    /**
     * Add body to message
     * @param {Object} body Body object
     */
    addBody(body) {
        this.bodies.push(body);
    }

    /**
     * Add header to message
     * @param {Object} header Header object
     */
    addHeader(header) {
        this.headers.push(header);
    }

    /**
     * Set AMF encoding
     * @param {number} encoding AMF encoding (AMF0 or AMF3)
     */
    setEncoding(encoding) {
        this.encoding = encoding;
    }

    /**
     * Get AMF encoding
     * @return {number} Encoding
     */
    getEncoding() {
        return this.encoding;
    }
}
