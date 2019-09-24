# Shopware 6 WebAuthn Plugin

### Requirements

* HTTPS-enabled development environment
* PHP GMP extension installed

If you use docker for development environment, you can use the extended Dockerfile in `docker/Dockerfile` for your PHP container.
It will automatically generate a self-signed certificate and configure apache appropriately.
If you don't use docker, you still copy & paste some commands from there :wink:

### Installation

TODO

### Features

* [x] Passwordless login for customers in Storefront
* [x] Key administration for customers in Storefront
* [ ] Passwordless login for admin users

### FAQ

TODO

### Links

* [W3C Specification](https://www.w3.org/TR/webauthn/)
* [Beginner's Guide by DuoLabs](https://webauthn.guide/)
* [Developer Videos by Yubico](https://www.yubico.com/why-yubico/for-developers/developer-videos/)
* [PHP Webauthn Framework @ GitHub](https://github.com/web-auth/webauthn-framework)
