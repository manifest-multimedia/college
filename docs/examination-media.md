# Examination question media

Question media is supplementary material for the existing answer and marking workflow. It does not add student recordings or file answers.

Authors use **Question Sets → Questions → Add/Edit Question → Question Media**. Images (JPG, PNG, WebP), video (MP4, WebM), and audio (MP3, WAV, OGG, M4A) are supported. Each upload accepts up to 10 files, 20 MB each. Browser codec support still determines whether a particular MP4/WebM/audio encoding can play; the application does not transcode files. Players have controls, no autoplay, and do not preload audio/video.

Media stays with its question when queued with “Add Another Question”. Editing supports removing existing files and uploading replacements. Questions already answered by students remain locked, including when a response arrives after the editor was opened.

## Deployment

1. Run `php artisan migrate` to extend attachment types and record their storage disk.
2. Run `php artisan exams:privatize-media` to copy legacy question media to private storage, verify SHA-256 hashes, update attachment records, then remove the public originals. Back up examination files and the database first. The command can be rerun; it reports missing files and returns a failure status for migration errors. Resolve any reported public-source deletion failures before considering migration complete.
3. Run `php artisan optimize:clear` if configuration/routes/views were cached.
4. Ensure `storage/app/private/question-media` is writable by PHP. PHP `upload_max_filesize` must allow 20 MB and `post_max_size` plus proxy/request limits must accommodate the total batch (up to 200 MB). Existing Livewire temporary validation caps each file at 20 MB.

New uploads are private immediately. Legacy files remain publicly accessible through existing storage links until the migration command removes their public originals. Do not expose `storage/app/private` through the web server. The rollback refuses to discard disk/type information while private or audio/video attachments exist.

The browser media route checks the existing academic staff roles or a current exam session token, question assignment, session deadline, exam availability, and institutional device restrictions. HTTP byte ranges support audio/video seeking. Answers, scoring, and question selection are unchanged.

## Offline delivery API

The existing package endpoint now includes `media` and `table_data` on each question. Each media item provides `id`, `type`, `url`, `filename`, `mime_type`, byte `file_size`, formatted `size`, and `display_order`.

The download URL is `GET /api/offline-exam-delivery/media/{attachment}` and uses the same Bearer token as the package endpoint. An offline client must download every media item during package acquisition, store it locally, and replace its URL with the local file URL before going offline. URLs alone do not make media available offline. The separate offline application is not included in this repository, so its download/render integration must consume these new fields.

## Verification

Run `vendor/bin/phpunit tests/Feature/QuestionMediaTest.php`. These tests use a dedicated in-memory SQLite schema and fake media disks, without migrating the institution database.
