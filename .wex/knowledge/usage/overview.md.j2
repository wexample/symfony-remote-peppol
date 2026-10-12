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
