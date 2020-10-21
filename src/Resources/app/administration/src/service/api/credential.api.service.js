const ApiService = Shopware.Classes.ApiService;

class CredentialApiService {
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.name = 'credentialApiService';
    }

    challenge() {
        const headers = this.getHeaders();

        return this.httpClient.post('/_action/reply-webauthn/creation-options', {}, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    register(credential) {
        const headers = this.getHeaders();
        const body = {credential: credential};
        return this.httpClient.post('/_action/reply-webauthn/register-credential', body, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    getHeaders() {
        return {
            Accept: 'application/json',
            Authorization: `Bearer ${this.loginService.getToken()}`,
            'Content-Type': 'application/json'
        };
    }
}

export default CredentialApiService;
