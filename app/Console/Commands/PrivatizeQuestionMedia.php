<?php

namespace App\Console\Commands;

use App\Models\QuestionAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeQuestionMedia extends Command
{
    protected $signature = 'exams:privatize-media';

    protected $description = 'Move legacy question media from public examination storage to private storage';

    public function handle(): int
    {
        $failed = false;
        QuestionAttachment::whereIn('attachment_type', ['image', 'video', 'audio'])
            ->where(fn ($query) => $query->where('disk', 'exams')->orWhereNull('disk'))
            ->chunkById(100, function ($attachments) use (&$failed) {
                foreach ($attachments as $attachment) {
                    $source = Storage::disk('exams');
                    $target = Storage::disk('question_media');
                    $oldPath = $attachment->file_path;
                    $newPath = 'legacy/'.$attachment->id.'/'.basename((string) $oldPath);
                    try {
                        if (! $oldPath || ! $source->exists($oldPath)) {
                            throw new \RuntimeException('Source file is missing');
                        }
                        $stream = $source->readStream($oldPath);
                        try {
                            $target->put($newPath, $stream);
                        } finally {
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }
                        if (hash_file('sha256', $source->path($oldPath)) !== hash_file('sha256', $target->path($newPath))) {
                            throw new \RuntimeException('File verification failed');
                        }
                        $attachment->update(['disk' => 'question_media', 'file_path' => $newPath]);
                        // A legacy file may have been referenced by more than one attachment.
                        if (! QuestionAttachment::where('file_path', $oldPath)
                            ->where(fn ($query) => $query->where('disk', 'exams')->orWhereNull('disk'))->exists()) {
                            if (! $source->delete($oldPath)) {
                                throw new \RuntimeException('Private copy saved, but public source could not be deleted: '.$oldPath);
                            }
                        }
                        $this->info('Moved attachment '.$attachment->id);
                    } catch (\Throwable $exception) {
                        $failed = true;
                        $this->error('Attachment '.$attachment->id.': '.$exception->getMessage());
                    }
                }
            });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
