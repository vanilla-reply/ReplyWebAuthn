import CreateCredentialPlugin from './plugin/create-credential.plugin';
import LoginPlugin from './plugin/login.plugin';

const PluginManager = window.PluginManager;

PluginManager.register('CreateCredentialPlugin', CreateCredentialPlugin, '[data-create-credential]');
PluginManager.register('LoginPlugin', LoginPlugin, '.login-form');
