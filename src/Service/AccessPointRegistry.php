<?php

namespace Wexample\SymfonyRemotePeppol\Service;

use InvalidArgumentException;
use Wexample\SymfonyRemotePeppol\Interface\AccessPointProviderInterface;
use Wexample\SymfonyRemotePeppol\Interface\DirectoryInterface;
use Wexample\SymfonyRemotePeppol\Interface\InboxInterface;
use Wexample\SymfonyRemotePeppol\Interface\SenderInterface;
use Wexample\SymfonyRemotePeppol\Interface\WebhookParserInterface;

/**
 * The access points the application has, by capability. One sends: the one
 * configured (`wexample_symfony_remote_peppol.sender`), or the only sender there is.
 */
class AccessPointRegistry
{
    /**
     * @param iterable<AccessPointProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers = [],
        private readonly ?string $sender = null,
    ) {
    }

    /**
     * Null when the application sends nothing through Peppol.
     */
    public function findSender(): ?SenderInterface
    {
        $senders = $this->all(SenderInterface::class);

        if (null !== $this->sender) {
            return $senders[$this->sender] ?? throw new InvalidArgumentException(sprintf('No Peppol sender "%s".', $this->sender));
        }

        return 1 === count($senders) ? reset($senders) : null;
    }

    /**
     * The sender's directory if it has one, else the first there is.
     */
    public function findDirectory(): ?DirectoryInterface
    {
        $sender = $this->findSender();

        if ($sender instanceof DirectoryInterface) {
            return $sender;
        }

        $directories = $this->all(DirectoryInterface::class);

        return reset($directories) ?: null;
    }

    /**
     * @return array<string, InboxInterface>
     */
    public function getInboxes(): array
    {
        return $this->all(InboxInterface::class);
    }

    public function findWebhookParser(string $name): ?WebhookParserInterface
    {
        return $this->all(WebhookParserInterface::class)[$name] ?? null;
    }

    /**
     * @template T of AccessPointProviderInterface
     * @param class-string<T> $capability
     * @return array<string, T>
     */
    private function all(string $capability): array
    {
        $found = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof $capability) {
                $found[$provider->getName()] = $provider;
            }
        }

        return $found;
    }
}
