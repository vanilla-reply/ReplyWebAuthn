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
                    this.credentialApiService.register(credential).then(() => {
                        console.log('Credential successfully registered.');
                    });
                });
            });
        }
    },
    data() {
        return {
            credentialName: null,
            usersCredentials: null
        }
    },
    async created () {
        const repository = this.repositoryFactory.create('reply_webauthn_credential');
        repository.search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                console.log(result);
            })
    }
});
