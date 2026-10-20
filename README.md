# symfony-remote-peppol

Version: 2.0.3

```php
$sender = $accessPointRegistry->findSender();          // null: the application sends nothing through Peppol
$receiver = PeppolParticipant::fromString('0208:0417497106');

if ($accessPointRegistry->findDirectory()?->findDocumentTypes($receiver)) {
    $transmission = $sender->send(PeppolDocument::billing($ourParticipant, $receiver, $ubl));
    $transmission->reference;                           // keep it: news about it comes under it
}
```

News arrives as events, from the webhook route (`/_peppol/webhook/{provider}`) or from `peppol:collect`:

- `TransmissionUpdatedEvent`: a sent document was delivered, or failed;
- `DocumentReceivedEvent`: a document arrived. A listener that cannot handle it throws: collected, the document stays in the inbox; posted, the webhook answers 500 and the access point posts again.

What to do with a document is the application's, or the package owning it: `symfony-accounting` turns received invoices into purchase drafts.

## Table of Contents

- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

## Dependencies

- php: >=8.5
- wexample/symfony-helpers: >=18.0.0
- symfony/console: ^7.4 || ^8.0
- symfony/event-dispatcher: ^7.4 || ^8.0
- symfony/routing: ^7.4 || ^8.0
- symfony/http-foundation: ^7.4 || ^8.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

Built and maintained by [Romain Weeger](https://weeger.io).

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
