<?php

namespace Reply\WebAuthn\DataAbstractionLayer\Definition;

use ReplyWebAuthn\DataAbstractionLayer\Entity\WebauthnCredentialCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use ReplyWebAuthn\DataAbstractionLayer\Entity\WebauthnCredentialEntity;

class WebauthnCredentialDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'reply_webauthn_credential';

    public function getEntityName(): string
    {
        return static::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WebauthnCredentialEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WebauthnCredentialCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            (new IdField('external_id', 'external_id')),
            (new IdField('user_handle', 'userHandle')),
            (new StringField('name', 'name'))->setFlags(new Required()),
            (new StringField('type', 'type'))->setFlags(new Required()),
            (new JsonField('transports', 'transports'))->setFlags(new Required()),
            (new StringField('attestation_type', 'attestationType'))->setFlags(new Required()),
            (new JsonField('trust_path', 'trustPath'))->setFlags(new Required()),
            (new StringField('aaguid', 'aaguid'))->setFlags(new Required()),
            (new StringField('public_key', 'publicKey'))->setFlags(new Required()),
            (new IntField('counter', 'counter'))->setFlags(new Required()),
            (new CreatedAtField()),
            (new UpdatedAtField())
        ]);
    }
}
