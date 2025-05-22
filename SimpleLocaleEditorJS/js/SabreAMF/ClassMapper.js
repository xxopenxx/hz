/**
 * ClassMapper for handling class mapping between local and remote classes
 */
class ClassMapper {
    constructor() {
        // Default mappings
        this.maps = {
            'flex.messaging.messages.RemotingMessage': 'SabreAMF.AMF3.RemotingMessage',
            'flex.messaging.messages.CommandMessage': 'SabreAMF.AMF3.CommandMessage',
            'flex.messaging.messages.AcknowledgeMessage': 'SabreAMF.AMF3.AcknowledgeMessage',
            'flex.messaging.messages.ErrorMessage': 'SabreAMF.AMF3.ErrorMessage',
            'flex.messaging.io.ArrayCollection': 'SabreAMF.ArrayCollection'
        };
        
        this.onGetLocalClass = null;
        this.onGetRemoteClass = null;
    }

    /**
     * Register a new class to be mapped
     * @param {string} remoteClass Remote class name
     * @param {string} localClass Local class name
     */
    registerClass(remoteClass, localClass) {
        this.maps[remoteClass] = localClass;
    }

    /**
     * Get the local class name for a remote class
     * @param {string} remoteClass Remote class name
     * @return {string|boolean} Local class name or false
     */
    getLocalClass(remoteClass) {
        let localClass = this.maps[remoteClass] || false;
        
        if (!localClass && typeof this.onGetLocalClass === 'function') {
            localClass = this.onGetLocalClass(remoteClass);
        }
        
        if (!localClass) return false;
        
        return localClass;
    }

    /**
     * Get the remote class name for a local class
     * @param {string} localClass Local class name
     * @return {string|boolean} Remote class name or false
     */
    getRemoteClass(localClass) {
        let remoteClass = false;
        
        // Find the key by value
        for (const [key, value] of Object.entries(this.maps)) {
            if (value === localClass) {
                remoteClass = key;
                break;
            }
        }
        
        if (!remoteClass && typeof this.onGetRemoteClass === 'function') {
            remoteClass = this.onGetRemoteClass(localClass);
        }
        
        return remoteClass;
    }
}

// Export singleton instance
export default new ClassMapper();
