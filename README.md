# Shopware 6 WebAuthn Plugin

### Requirements

* HTTPS-enabled environment
* PHP GMP extension installed

If you use docker for development environment, you can use the extended Dockerfile in `docker/Dockerfile` for your PHP container.
It will automatically generate a self-signed certificate and configure apache appropriately.
If you don't use docker, you still copy & paste some commands from there :wink:

### Installation

#### Using composer

```bash
$ composer require reply/web-authn
```

#### From ZIP file

1. Login to your Shopware administration interface
2. Navigate to Settings -> System -> Plugins
3. Click button "Upload plugin"
4. Select ZIP file on your local computer
5. Install and activate the plugin

### Features

* [x] Passwordless login for customers in Storefront
* [x] Key administration for customers in Storefront
* [ ] Passwordless login for admin users
* [ ] Support different configurations for each sales channel

### FAQ

*What's the point of these fake credentials?*

An import security feature of an authentication system is to prevent user discovery by brute-forcing usernames.
In a traditional password-based login you have to make sure that the client cannot distinguish between unknown username
and wrong password. Otherwise a malicious client could use brute-force to discover a list of known users.
This problem becomes complexer, when you are using 2 HTTP requests for login like it is required for WebAuthn. The user
can already be identified in the first step. In case the user is unknown the server cannot tell the client, because this
would open the door for user discovery attacks. So the server will continue the authentication ceremony by providing fake
credentials to the client. 

### Links

* [W3C Specification](https://www.w3.org/TR/webauthn/)
* [Beginner's Guide by DuoLabs](https://webauthn.guide/)
* [Developer Videos by Yubico](https://www.yubico.com/why-yubico/for-developers/developer-videos/)
* [PHP Webauthn Framework @ GitHub](https://github.com/web-auth/webauthn-framework)
