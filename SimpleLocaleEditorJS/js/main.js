import AMFProcessor from './AMFProcessor.js';

// Create processor instance
const processor = new AMFProcessor();
let processedData = null;

// UI elements
const fileInput = document.getElementById('fileInput');
const typeSelect = document.getElementById('typeSelect');
const actionSelect = document.getElementById('actionSelect');
const processBtn = document.getElementById('processBtn');
const resultArea = document.getElementById('resultArea');
const downloadBtn = document.getElementById('downloadBtn');

// Process button click handler
processBtn.addEventListener('click', async () => {
    if (!fileInput.files.length) {
        alert('Please select a file first');
        return;
    }
    
    try {
        resultArea.value = "Processing...";
        const file = fileInput.files[0];
        const type = typeSelect.value;
        const action = actionSelect.value;
        
        console.log(`Processing file: ${file.name}, Type: ${type}, Action: ${action}`);
        
        // Read file as ArrayBuffer
        const arrayBuffer = await readFileAsArrayBuffer(file);
        console.log(`File size: ${arrayBuffer.byteLength} bytes`);
        
        if (type === 'gzip' && action === 'decompress') {
            console.log('Decompressing using GZIP only method');
            // Old format decompression
            const result = processor.decompressOldLocale(arrayBuffer);
            resultArea.value = JSON.stringify(result, null, 2);
            processedData = new Blob([resultArea.value], { type: 'application/json' });
        } 
        else if (type === 'gzip_amf3' && action === 'decompress') {
            console.log('Decompressing using GZIP + AMF3 method');
            // AMF3 decompression
            const result = processor.decompressAMF3(arrayBuffer);
            console.log('Decompression result:', result);
            
            // Handle empty objects or arrays properly
            if (result && typeof result === 'object' && Object.keys(result).length === 0) {
                console.log('Result appears to be an empty object');
                resultArea.value = "{}";
            } else if (Array.isArray(result) && result.length === 0) {
                console.log('Result appears to be an empty array');
                resultArea.value = "[]";
            } else {
                // Check if the result has any associative properties we should preserve
                const hasAssociativeProps = !Array.isArray(result) && 
                    typeof result === 'object' && 
                    Object.keys(result).some(key => isNaN(parseInt(key)));
                
                console.log('Formatting result as JSON with preserveAssociative:', hasAssociativeProps);
                resultArea.value = JSON.stringify(result, null, 2);
            }
            
            processedData = new Blob([resultArea.value], { type: 'application/json' });
        }
        else if (type === 'gzip' && action === 'compress') {
            console.log('Compressing using GZIP only method');
            // Old format compression
            try {
                const jsonData = resultArea.value || await readFileAsText(file);
                const compressedData = processor.compressOldLocale(jsonData);
                resultArea.value = 'Data compressed successfully! Click download to save.';
                processedData = new Blob([compressedData]);
            } catch (e) {
                throw new Error('Invalid JSON: ' + e.message);
            }
        }
        else if (type === 'gzip_amf3' && action === 'compress') {
            console.log('Compressing using GZIP + AMF3 method');
            // AMF3 compression
            try {
                const jsonData = resultArea.value || await readFileAsText(file);
                const data = JSON.parse(jsonData);
                const compressedData = processor.compressAMF3(data);
                resultArea.value = 'Data compressed successfully! Click download to save.';
                processedData = new Blob([compressedData]);
            } catch (e) {
                throw new Error('Invalid JSON: ' + e.message);
            }
        }
        
        // Enable download button
        downloadBtn.disabled = false;
        
    } catch (error) {
        console.error('Processing error:', error);
        resultArea.value = 'Error: ' + error.message + '\n\nCheck browser console for details.';
        downloadBtn.disabled = true;
    }
});

// Download button click handler
downloadBtn.addEventListener('click', () => {
    if (!processedData) return;
    
    const action = actionSelect.value;
    const extension = action === 'decompress' ? '.json' : '.data';
    const fileName = `processed${extension}`;
    
    // Create download link
    const url = URL.createObjectURL(processedData);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    a.click();
    
    // Clean up
    URL.revokeObjectURL(url);
});

// Helper function to read file as ArrayBuffer
function readFileAsArrayBuffer(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('Failed to read file'));
        reader.readAsArrayBuffer(file);
    });
}

// Helper function to read file as text
function readFileAsText(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('Failed to read file'));
        reader.readAsText(file);
    });
}
