`symfony-remote-peppol` carries business documents over Peppol, the European e-delivery network, through an access point, whatever the provider. It holds the vocabulary and the plumbing; each provider comes in a package of its own (`symfony-remote-peppol-<provider>`), as payment providers do beside `symfony-remote-payment`.

- `PeppolParticipant` is an address on the network: `0208:0123456749` (Belgian enterprise number), `0009:<SIRET>`, `9925:BE…` (Belgian VAT number).
- `PeppolDocument` is what is sent: sender, receiver, document type and process, XML. `PeppolDocument::billing()` types a Peppol BIS Billing 3.0 invoice or credit note.
- `PeppolTransmission` tells where a sent document stands: `queued`, `delivered`, `failed`.
- `ReceivedDocument` is a document that reached us.

An access point offers capabilities, each an interface: `SenderInterface` (send, track), `InboxInterface` (fetch, acknowledge), `WebhookParserInterface` (its news, signed), `DirectoryInterface` (who is on Peppol, accepting what). `FakeAccessPoint` offers them all, in memory, for tests and demos.
