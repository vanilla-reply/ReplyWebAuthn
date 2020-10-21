import template from './reply-webauthn-register-credential.html.twig';
import ConverterHelper from "../../helper/converter.helper";

Shopware.Component.register('reply-webauthn-register-credential', {
    template,

    inject: ['credentialApiService'],

    methods: {
        registerCredential() {
            console.log('register!');
            // TODO: use this.credentialApiService.challenge() to get creationOptions
            // TODO: convert creationOptions with ConverterHelper
            // TODO: get credential via navigator.credentials.create({publicKey: options})
            // TODO: convert credential with ConverterHelper.convertAuthenticatorData()
            // TODO: Add a name to the converted credential
            // TODO: use this.credentialApiService.register() to register credential
        }
    },
});

