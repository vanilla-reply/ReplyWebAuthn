import './component/reply-webauthn-register-credential';
import template from './extension/sw-profile-index.html.twig';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
import CredentialApiService from "./service/api/credential.api.service";

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
Shopware.Component.override('sw-profile-index', {
    template
});

Shopware.Application
    .addServiceProvider('credentialApiService', (container) => {
        return new CredentialApiService(container.httpClient, container.loginService);
    });
