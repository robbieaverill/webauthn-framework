<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Webauthn\SecurityBundle\Security\Authentication\Token;

use Assert\Assertion;
use function Safe\json_encode;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientOutputs;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

class WebauthnToken extends AbstractToken
{
    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var PublicKeyCredentialRequestOptions
     */
    private $publicKeyCredentialRequestOptions;

    /**
     * @var PublicKeyCredentialDescriptor
     */
    private $publicKeyCredentialDescriptor;

    /**
     * @var bool
     */
    private $isUserPresent;

    /**
     * @var bool
     */
    private $isUserVerified;

    /**
     * @var int
     */
    private $signCount;

    /**
     * @var AuthenticationExtensionsClientOutputs|null
     */
    private $extensions;

    /**
     * @var int
     */
    private $reservedForFutureUse1;

    /**
     * @var int
     */
    private $reservedForFutureUse2;

    public function __construct(string $username, PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions, PublicKeyCredentialDescriptor $publicKeyCredentialDescriptor, bool $isUserPresent, bool $isUserVerified, int $reservedForFutureUse1, int $reservedForFutureUse2, int $signCount, ?AuthenticationExtensionsClientOutputs $extensions, string $providerKey, array $roles = [])
    {
        parent::__construct($roles);
        Assertion::notEmpty($providerKey, '$providerKey must not be empty.');

        $this->setUser($username);
        $this->providerKey = $providerKey;
        $this->publicKeyCredentialRequestOptions = $publicKeyCredentialRequestOptions;
        $this->publicKeyCredentialDescriptor = $publicKeyCredentialDescriptor;
        $this->isUserPresent = $isUserPresent;
        $this->isUserVerified = $isUserVerified;
        $this->signCount = $signCount;
        $this->extensions = $extensions;
        $this->reservedForFutureUse1 = $reservedForFutureUse1;
        $this->reservedForFutureUse2 = $reservedForFutureUse2;
    }

    public function getPublicKeyCredentialRequestOptions(): PublicKeyCredentialRequestOptions
    {
        return $this->publicKeyCredentialRequestOptions;
    }

    public function getCredentials(): PublicKeyCredentialDescriptor
    {
        return $this->publicKeyCredentialDescriptor;
    }

    public function isUserPresent(): bool
    {
        return $this->isUserPresent;
    }

    public function isUserVerified(): bool
    {
        return $this->isUserVerified;
    }

    public function getReservedForFutureUse1(): int
    {
        return $this->reservedForFutureUse1;
    }

    public function getReservedForFutureUse2(): int
    {
        return $this->reservedForFutureUse2;
    }

    public function getSignCount(): int
    {
        return $this->signCount;
    }

    public function getExtensions(): ?AuthenticationExtensionsClientOutputs
    {
        return $this->extensions;
    }

    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    public function serialize(): string
    {
        return serialize([
            json_encode($this->publicKeyCredentialRequestOptions),
            json_encode($this->publicKeyCredentialDescriptor),
            $this->isUserPresent,
            $this->isUserVerified,
            $this->reservedForFutureUse1,
            $this->reservedForFutureUse2,
            $this->signCount,
            $this->extensions,
            $this->providerKey,
            parent::serialize(),
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        list(
            $publicKeyCredentialRequestOptions,
            $publicKeyCredentialDescriptor,
            $this->isUserPresent,
            $this->isUserVerified,
            $this->reservedForFutureUse1,
            $this->reservedForFutureUse2,
            $this->signCount,
            $extensions,
            $this->providerKey,
            $parentStr
            ) = unserialize($serialized);
        $this->publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($publicKeyCredentialRequestOptions);
        $this->publicKeyCredentialDescriptor = PublicKeyCredentialDescriptor::createFromString($publicKeyCredentialDescriptor);

        $this->extensions = null;
        if (null !== $extensions) {
            $this->extensions = AuthenticationExtensionsClientOutputs::createFromString($extensions);
        }

        parent::unserialize($parentStr);
    }
}
