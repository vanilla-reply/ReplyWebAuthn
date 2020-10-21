import EncodingHelper from "./encoding.helper";

export default class ConverterHelper {

    /**
     * Converts data received from navigator.credentials.create() to JSON-compatible format
     *
     * @param data
     * @returns {{response: {clientDataJSON: string, attestationObject: string}, rawId: string, id: *, type: *}}
     */
    static convertAuthenticatorData(data) {
        return {
            id: data.id,
            type: data.type,
            rawId: EncodingHelper.arrayToBase64String(new Uint8Array(data.rawId)),
            response: {
                clientDataJSON: EncodingHelper.arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                attestationObject: EncodingHelper.arrayToBase64String(new Uint8Array(data.response.attestationObject)),
            },
        };
    }

    /**
     * Converts credential creation options from server response to format required by navigator.credentials.create()
     *
     * @param options
     * @returns {{options: {challenge: Uint8Array, user: {id: Uint8Array}}}}
     */
    static convertOptions(options) {
        options.challenge = EncodingHelper.toByteArray(EncodingHelper.base64UrlDecode(options.challenge));
        options.user.id = EncodingHelper.toByteArray(EncodingHelper.base64Decode(options.user.id));

        if (options.excludeCredentials && options.excludeCredentials instanceof Array) {
            options.excludeCredentials.map(function (credential) {
                credential.id = EncodingHelper.toByteArray(EncodingHelper.base64UrlDecode(credential.id));
            });
        }

        return options;
    }
}
