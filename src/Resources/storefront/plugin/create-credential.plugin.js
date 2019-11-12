import Plugin from 'src/script/plugin-system/plugin.class';
import HttpClient from 'src/script/service/http-client.service';
import EncodingHelper from '../helper/encoding.helper';
import PageLoadingIndicatorUtil from 'src/script/utility/loading-indicator/page-loading-indicator.util';
import PseudoModalUtil from 'src/script/utility/modal-extension/pseudo-modal.util';
import DomAccess from 'src/script/helper/dom-access.helper';

export default class CreateCredentialPlugin extends Plugin {

    static options = {
        initUrl: window.router['frontend.account.webauthn.credential.creation-options'],
        saveUrl: window.router['frontend.account.webauthn.credential.save'],
        modalUrl: window.router['frontend.account.webauthn.credential.creation-modal'],
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
        PageLoadingIndicatorUtil.create();
        this._sendRequest(this.options.initUrl, {}, this._createCredential.bind(this));
    }

    /**
     * Create a new credential on authenticator device
     *
     * @param response
     * @private
     */
    _createCredential(response) {
        const options = this._convertOptions(response);

        console.log('Requesting new credential from authenticator', options);

        navigator.credentials.create({publicKey: options})
            .then(this._loadModal.bind(this), error => {
                PageLoadingIndicatorUtil.remove();
                console.log(error.toString()); // Example: timeout, interaction refused...
            });
    }

    /**
     * Loads the modal content for entering a credential name
     *
     * @param authenticatorData
     * @private
     */
    _loadModal(authenticatorData) {
        const credential = this._convertAuthenticatorData(authenticatorData);
        this._client.get(this.options.modalUrl, content => {
            PageLoadingIndicatorUtil.remove();
            const pseudoModal = new PseudoModalUtil(content);
            pseudoModal.open(this._onModalOpen.bind(this, pseudoModal, credential))
        });
    }

    /**
     * Binds various event listeners to modal elements
     *
     * @param pseudoModal
     * @param credential
     * @private
     */
    _onModalOpen(pseudoModal, credential) {
        const form = DomAccess.querySelector(pseudoModal.getModal(), 'form', false);
        const input = DomAccess.querySelector(pseudoModal.getModal(), 'input', false);

        input.focus();

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            PageLoadingIndicatorUtil.create();

            credential.name = input.value;
            this._saveCredential(credential);
            pseudoModal.close();
        });
        form.addEventListener('reset', () => {
            pseudoModal.close();
        });
    }

    /**
     * Saves the credential by sending it to the server
     *
     * @param credential
     * @private
     */
    _saveCredential(credential) {
        console.log('Sending credential to server', credential);
        this._sendRequest(this.options.saveUrl, credential, () => {
            window.location.reload();
        });
    }

    /**
     * Utility function to send XHR requests
     *
     * @param url
     * @param data
     * @param success
     * @private
     */
    _sendRequest(url, data, success) {
        const request = this._client.post(url, JSON.stringify(data), function() {});
        request.addEventListener('loadend', () => {
            if (request.status === 200) {
                success(JSON.parse(request.responseText));
            }
        });
    }

    /**
     * Converts credential creation options from server response to format required by navigator.credentials.create()
     *
     * @param options
     * @returns {*}
     * @private
     */
    _convertOptions(options) {
        options.challenge = EncodingHelper.toByteArray(EncodingHelper.base64UrlDecode(options.challenge));
        options.user.id = EncodingHelper.toByteArray(EncodingHelper.base64Decode(options.user.id));

        return options;
    }

    /**
     * Converts data received from authenticator device to JSON-compatible format
     *
     * @param data
     * @returns {{response: {clientDataJSON: string, attestationObject: string}, rawId: string, id: *, type: *}}
     * @private
     */
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
