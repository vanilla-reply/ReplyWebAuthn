import template from './reply-webauthn-register-credential.html.twig';
import ConverterHelper from "../../helper/converter.helper";

const { Criteria } = Shopware.Data;

Shopware.Component.register('reply-webauthn-register-credential', {
    template,

    inject: ['credentialApiService', 'repositoryFactory'],

    methods: {
        registerCredential() {
            this.credentialApiService.challenge().then((options) => {
                console.log('Received creation options from server.');
                console.log('Trying to communicate with local authenticator.');

                navigator.credentials.create({
                    publicKey: ConverterHelper.convertOptions(options)
                }).then((authenticatorData) => {
                    console.log('Received data from local authenticator.');

                    const credential = ConverterHelper.convertAuthenticatorData(authenticatorData);
                    credential.name = this.credentialName;

                    console.log('Sending credential to server.');
                    this.credentialApiService.register(credential).then((response) => {
                        console.log('Credential successfully registered.');
                        this.credentials = response;
                    });
                });
            });
        },
        deleteCredential(id) {
            this.credentialApiService.delete(id).then((response) => {
                this.credentials = response;
            });
        },
        _pushCredential (credential) {
            this.credentials.push(credential)
        }
    },
    data() {
        return {
            credentialName: null,
            credentials: []
        }
    },
    async created () {
        const repository = this.repositoryFactory.create('webauthn_credential');
        repository.search(new Criteria(), Shopware.Context.api)
            .then((credentialRepository) => {
                credentialRepository.forEach(this._pushCredential);
            })
    }
});
