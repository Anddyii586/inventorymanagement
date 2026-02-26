/**
 * Device Fingerprint Generator
 * 
 * Menggunakan kombinasi beberapa atribut browser untuk membuat
 * fingerprint yang lebih unik dan spesifik untuk device tertentu.
 * 
 * Fingerprint ini akan digunakan untuk matching session SSO yang lebih akurat
 * dibandingkan hanya menggunakan user_agent saja.
 */

class DeviceFingerprint {
    /**
     * Generate device fingerprint
     * Menggunakan kombinasi beberapa atribut browser
     * 
     * @returns {Promise<string>} Hash fingerprint
     */
    static async generate() {
        const components = {
            // User Agent (sudah digunakan sebelumnya)
            userAgent: navigator.userAgent || '',
            
            // Screen properties
            screenWidth: screen.width || 0,
            screenHeight: screen.height || 0,
            screenColorDepth: screen.colorDepth || 0,
            screenPixelDepth: screen.pixelDepth || 0,
            
            // Viewport
            viewportWidth: window.innerWidth || 0,
            viewportHeight: window.innerHeight || 0,
            
            // Timezone
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
            timezoneOffset: new Date().getTimezoneOffset() || 0,
            
            // Language
            language: navigator.language || '',
            languages: navigator.languages ? navigator.languages.join(',') : '',
            
            // Platform
            platform: navigator.platform || '',
            
            // Hardware concurrency (CPU cores)
            hardwareConcurrency: navigator.hardwareConcurrency || 0,
            
            // Device memory (jika tersedia)
            deviceMemory: navigator.deviceMemory || 0,
            
            // Canvas fingerprint (lebih unik)
            canvas: await this.getCanvasFingerprint(),
            
            // WebGL fingerprint (jika tersedia)
            webgl: this.getWebGLFingerprint(),
            
            // Fonts (jika tersedia)
            fonts: await this.getFontsFingerprint(),
        };
        
        // Convert to string and hash
        const fingerprintString = JSON.stringify(components);
        return this.hash(fingerprintString);
    }
    
    /**
     * Generate canvas fingerprint
     * Canvas rendering berbeda untuk setiap device/browser
     * 
     * @returns {Promise<string>}
     */
    static async getCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 50;
            const ctx = canvas.getContext('2d');
            
            if (!ctx) {
                return '';
            }
            
            // Draw some text and shapes
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('DeviceFingerprint', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('DeviceFingerprint', 4, 17);
            
            // Get image data
            const dataURL = canvas.toDataURL();
            return this.hash(dataURL);
        } catch (e) {
            return '';
        }
    }
    
    /**
     * Generate WebGL fingerprint
     * 
     * @returns {string}
     */
    static getWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                return '';
            }
            
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (!debugInfo) {
                return '';
            }
            
            const vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
            const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
            
            return this.hash(vendor + '|' + renderer);
        } catch (e) {
            return '';
        }
    }
    
    /**
     * Generate fonts fingerprint
     * Deteksi fonts yang tersedia di browser
     * 
     * @returns {Promise<string>}
     */
    static async getFontsFingerprint() {
        // List of common fonts to check
        const baseFonts = [
            'Arial', 'Verdana', 'Times New Roman', 'Courier New',
            'Georgia', 'Palatino', 'Garamond', 'Bookman',
            'Comic Sans MS', 'Trebuchet MS', 'Impact', 'Monaco'
        ];
        
        const availableFonts = [];
        
        // Check each font
        for (const font of baseFonts) {
            if (this.isFontAvailable(font)) {
                availableFonts.push(font);
            }
        }
        
        return availableFonts.join(',');
    }
    
    /**
     * Check if a font is available
     * 
     * @param {string} fontName
     * @returns {boolean}
     */
    static isFontAvailable(fontName) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        if (!ctx) {
            return false;
        }
        
        // Base measurement
        const baseFont = 'monospace';
        ctx.font = `72px ${baseFont}`;
        const baseWidth = ctx.measureText('mmmmmmmmmmlli').width;
        
        // Test font
        ctx.font = `72px ${fontName}, ${baseFont}`;
        const testWidth = ctx.measureText('mmmmmmmmmmlli').width;
        
        return baseWidth !== testWidth;
    }
    
    /**
     * Simple hash function (djb2 algorithm)
     * 
     * @param {string} str
     * @returns {string}
     */
    static hash(str) {
        let hash = 5381;
        for (let i = 0; i < str.length; i++) {
            hash = ((hash << 5) + hash) + str.charCodeAt(i);
            hash = hash & hash; // Convert to 32bit integer
        }
        return Math.abs(hash).toString(36);
    }
    
    /**
     * Get or create device fingerprint
     * Cek localStorage dulu, jika tidak ada generate baru
     * 
     * @returns {Promise<string>}
     */
    static async getOrCreate() {
        const storageKey = 'device_fingerprint';
        
        // Cek localStorage
        let fingerprint = localStorage.getItem(storageKey);
        
        if (fingerprint) {
            return fingerprint;
        }
        
        // Generate baru
        fingerprint = await this.generate();
        
        // Simpan ke localStorage
        try {
            localStorage.setItem(storageKey, fingerprint);
        } catch (e) {
            // Jika localStorage tidak tersedia, gunakan sessionStorage
            try {
                sessionStorage.setItem(storageKey, fingerprint);
            } catch (e2) {
                // Jika keduanya tidak tersedia, return fingerprint saja
            }
        }
        
        return fingerprint;
    }
    
    /**
     * Send fingerprint to server
     * 
     * @param {string} endpoint
     * @returns {Promise<void>}
     */
    static async sendToServer(endpoint = '/sso/device-fingerprint') {
        try {
            const fingerprint = await this.getOrCreate();
            
            // Send via fetch
            await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    fingerprint: fingerprint,
                    timestamp: Date.now()
                }),
                credentials: 'same-origin'
            });
        } catch (e) {
            // Silent fail - tidak perlu mengganggu user experience
            console.debug('Failed to send device fingerprint:', e);
        }
    }
}

// Auto-send fingerprint saat halaman dimuat (jika di halaman yang relevan)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Hanya kirim jika di halaman admin atau login
        if (window.location.pathname.includes('/admin') || 
            window.location.pathname.includes('/login') ||
            window.location.pathname.includes('/sso')) {
            DeviceFingerprint.sendToServer();
        }
    });
} else {
    // DOM sudah loaded
    if (window.location.pathname.includes('/admin') || 
        window.location.pathname.includes('/login') ||
        window.location.pathname.includes('/sso')) {
        DeviceFingerprint.sendToServer();
    }
}

// Export untuk penggunaan global
window.DeviceFingerprint = DeviceFingerprint;

