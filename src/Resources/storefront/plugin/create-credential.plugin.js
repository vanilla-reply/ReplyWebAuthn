import Plugin from 'src/script/plugin-system/plugin.class';
import HttpClient from 'src/script/service/http-client.service';
import EncodingHelper from '../helper/encoding.helper';

export default class CreateCredentialPlugin extends Plugin {

    static options = {
        initUrl: window.router['frontend.account.webauthn.credential.creation-options'],
        saveUrl: window.router['frontend.account.webauthn.credential.save'],
    };

    init() {
        this._button = this.el;

        if (!this._button) {
            throw new Error(`No button found for the plugin: ${this.constructor.name}`);
        }

        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerEvents();
    }

    /**
     * registers all needed events
     *
     * @private
     */
    _registerEvents() {
        this._button.addEventListener('click', this._getOptions.bind(this));
    }

    /**
     * Retrieve credential creation options from server
     *
     * @private
     */
    _getOptions() {
        this._sendRequest(this.options.initUrl, {}, this._createCredential.bind(this));
    }

    _createCredential(response) {
        const options = this._convertOptions(response);

        console.log('Requesting new credential from authenticator', options);

        navigator.credentials.create({publicKey: options})
            .then(this._promptUserForName.bind(this), error => {
                console.log(error.toString()); // Example: timeout, interaction refused...
            });
    }

    _promptUserForName(authenticatorData) {
        const credential = this._convertAuthenticatorData(authenticatorData);
        credential.name = window.prompt('Please enter a name for this credential', '');

        this._saveCredential(credential);
    }

    _saveCredential(credential) {
        console.log('Sending credential to server', credential);
        this._sendRequest(this.options.saveUrl, credential, this._onCredentialSaveSuccess.bind(this));
    }

    _sendRequest(url, data, success) {
        const request = this._client.post(url, JSON.stringify(data), function() {});
        request.addEventListener('loadend', () => {
            if (request.status === 200) {
                success(JSON.parse(request.responseText));
            }
        });
    }

    _onCredentialSaveSuccess() {
        window.location.reload();
    }

    _convertOptions(options) {
        options.challenge = EncodingHelper.toByteArray(EncodingHelper.base64UrlDecode(options.challenge));
        options.user.id = EncodingHelper.toByteArray(EncodingHelper.base64Decode(options.user.id));

        return options;
    }

    _convertAuthenticatorData(data) {
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
}
