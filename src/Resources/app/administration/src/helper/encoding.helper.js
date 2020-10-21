export default class EncodingHelper {

    /**
     * @param {string} data
     * @returns {string}
     */
    static base64UrlDecode(data) {
        return window.atob(data.replace(/_/g, '/').replace(/-/g, '+'));
    }

    /**
     * @param {string} data
     * @returns {string}
     */
    static base64Decode(data) {
        return window.atob(data);
    }

    /**
     * @param {Uint8Array} array
     * @returns {string}
     */
    static arrayToBase64String(array) {
        return window.btoa(String.fromCharCode(...array));
    }

    /**
     * @param {string} string
     * @returns {Uint8Array}
     */
    static toByteArray(string) {
        return Uint8Array.from(string, c=>c.charCodeAt(0));
    }
}
