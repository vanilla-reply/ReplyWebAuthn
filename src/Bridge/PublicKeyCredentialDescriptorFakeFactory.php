<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialDescriptor;

class PublicKeyCredentialDescriptorFakeFactory
{
    public function create(string $username): PublicKeyCredentialDescriptor
    {
        $secret = hex2bin(getenv('APP_SECRET'));

        return new PublicKeyCredentialDescriptor(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            $this->xorCrypt($this->strFill($username, 64), $this->strFill($secret, 64))
        );
    }

    private function xorCrypt(string $data, string $key): string
    {
        if (strlen($data) !== strlen($key)) {
            throw new \RuntimeException('XOR Encryption failed due to unequal string lengths');
        }
        $encrypted = '';
        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $encrypted .= $data[$i] ^ $key[$i];
        }
        return $encrypted;
    }

    private function strFill(string $string, int $targetLength): string
    {
        while (strlen($string) < $targetLength) {
            $string .= $string;
        }

        return substr($string, 0, $targetLength);
    }
}
