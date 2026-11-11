<?php

namespace Tests\Feature;

use App\Http\Controllers\QuestionMediaController;
use App\Livewire\QuestionSetQuestionForm;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\Question;
use App\Models\QuestionAttachment;
use App\Models\QuestionSet;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class QuestionMediaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'media_tests', 'database.connections.media_tests' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['0001_01_01_000000_create_users_table.php', '2025_05_04_154455_create_permission_tables.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });
        (require database_path('migrations/2025_09_29_000001_create_question_sets_table.php'))->up();
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exam_id')->nullable();
            $table->integer('question_set_id')->nullable();
            $table->text('question_text');
            $table->string('type')->default('MCQ');
            $table->string('difficulty_level')->default('medium');
            $table->integer('mark')->default(1);
            $table->string('exam_section')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id');
            $table->text('option_text');
            $table->boolean('is_correct');
            $table->timestamps();
        });
        Schema::create('responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id');
        });
        foreach (['2025_12_03_075631_create_question_attachments_table.php', '2026_11_11_000001_add_media_to_question_attachments.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        Schema::create('exams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->integer('duration')->default(60);
            $table->integer('questions_per_session')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exam_id');
            $table->integer('student_id');
            $table->string('session_token');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2025_09_29_000003_create_exam_session_questions_table.php'))->up();
        (require database_path('migrations/2025_09_29_000004_create_exam_question_set_pivot_table.php'))->up();
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            foreach (['student_id', 'first_name', 'last_name', 'other_name', 'email'] as $column) {
                $table->string($column)->nullable();
            }
        });
        Storage::fake('question_media');
        Storage::fake('exams');
    }

    private function form()
    {
        $user = User::create(['name' => 'Staff', 'email' => uniqid().'@example.test', 'password' => 'password']);
        $course = DB::table('subjects')->insertGetId(['name' => 'Test subject']);
        $set = QuestionSet::create(['name' => 'Media questions', 'course_id' => $course, 'created_by' => $user->id]);

        return Livewire::test(QuestionSetQuestionForm::class, ['questionSetId' => $set->id])
            ->set('questionText', 'Identify the sound or object shown.')
            ->set('options', ['Answer A', 'Answer B'])
            ->set('correctOptions', [0]);
    }

    public function test_image_audio_and_video_are_saved_privately(): void
    {
        $this->form()->set('questionMedia', [
            UploadedFile::fake()->image('diagram.png'),
            UploadedFile::fake()->create('sound.mp3', 100, 'audio/mpeg'),
            UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ])->call('save')->assertHasNoErrors()->assertRedirect();
        $this->assertSame(['image', 'audio', 'video'], QuestionAttachment::orderBy('id')->pluck('attachment_type')->all());
        foreach (QuestionAttachment::all() as $attachment) {
            $this->assertSame('question_media', $attachment->disk);
            Storage::disk('question_media')->assertExists($attachment->file_path);
            $this->assertStringContainsString('/question-media/', $attachment->url);
        }
    }

    public function test_invalid_and_oversized_files_are_rejected(): void
    {
        $this->form()->set('questionMedia', [UploadedFile::fake()->create('script.php', 1)])
            ->assertHasErrors('questionMedia.0');
        $this->form()->set('questionMedia', [UploadedFile::fake()->create('huge.mp4', 20481, 'video/mp4')])
            ->assertHasErrors('questionMedia.0');
        $this->assertSame(0, Question::count());
    }

    public function test_queued_questions_keep_their_own_media(): void
    {
        $this->form()->set('questionMedia', [UploadedFile::fake()->create('first.mp3', 10, 'audio/mpeg')])
            ->call('addAnotherQuestion')->assertHasNoErrors()
            ->set('questionText', 'Identify the second example.')
            ->set('options', ['One', 'Two'])->set('correctOptions', [1])
            ->set('questionMedia', [UploadedFile::fake()->create('second.mp4', 10, 'video/mp4')])
            ->call('save')->assertHasNoErrors()->assertRedirect();
        $questions = Question::with('attachments')->get();
        $this->assertCount(2, $questions);
        $this->assertSame('first.mp3', $questions[0]->attachments[0]->original_filename);
        $this->assertSame('second.mp4', $questions[1]->attachments[0]->original_filename);
    }

    private function attachment(string $type = 'audio'): QuestionAttachment
    {
        $question = Question::create(['question_text' => 'Listen carefully.']);
        Storage::disk('question_media')->put('sound.mp3', 'audio-test-content');

        return $question->attachments()->create([
            'attachment_type' => $type, 'disk' => 'question_media', 'file_path' => 'sound.mp3',
            'mime_type' => 'audio/mpeg', 'original_filename' => 'sound.mp3',
        ]);
    }

    private function mediaRequest(?string $token = null): Request
    {
        $request = Request::create('/question-media/1');
        $request->setLaravelSession(app('session.store'));
        if ($token) {
            $request->session()->put('exam_session_token', $token);
        }

        return $request;
    }

    public function test_guests_cannot_read_media(): void
    {
        $attachment = $this->attachment();
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(0);
        app(QuestionMediaController::class)->show($this->mediaRequest(), $attachment);
    }

    public function test_staff_can_read_media_with_byte_range_support(): void
    {
        $attachment = $this->attachment();
        $user = User::create(['name' => 'Staff', 'email' => 'staff@example.test', 'password' => 'password']);
        $user->assignRole(Role::findOrCreate('Lecturer', 'web'));
        $request = $this->mediaRequest();
        $request->setUserResolver(fn () => $user);
        $response = app(QuestionMediaController::class)->show($request, $attachment);
        $request->headers->set('Range', 'bytes=0-4');
        $response->prepare($request);
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 0-4/18', $response->headers->get('Content-Range'));
    }

    public function test_session_can_only_read_assigned_questions_while_active(): void
    {
        $attachment = $this->attachment();
        $exam = Exam::withoutEvents(fn () => Exam::create(['duration' => 60, 'status' => 'active']));
        $session = ExamSession::create([
            'exam_id' => $exam->id, 'student_id' => 1, 'session_token' => 'valid-token', 'started_at' => now(),
        ]);
        ExamSessionQuestion::create(['exam_session_id' => $session->id, 'question_id' => $attachment->question_id, 'display_order' => 1]);
        $request = $this->mediaRequest('valid-token');
        $this->assertSame(200, app(QuestionMediaController::class)->show($request, $attachment)->getStatusCode());
        $session->update(['completed_at' => now()]);
        try {
            app(QuestionMediaController::class)->show($request, $attachment);
            $this->fail('Completed session accessed media');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_unassigned_question_is_denied(): void
    {
        $attachment = $this->attachment();
        $this->expectException(HttpException::class);
        app(QuestionMediaController::class)->show($this->mediaRequest('different-token'), $attachment);
    }

    public function test_offline_media_requires_bearer_token(): void
    {
        $attachment = $this->attachment();
        config(['services.offline_exam_app.api_token' => 'offline-secret']);
        $url = route('offline-exam-media.show', $attachment);
        $this->getJson($url)->assertUnauthorized();
        $this->withToken('offline-secret')->get($url)->assertOk()->assertHeader('Content-Type', 'audio/mpeg');
    }

    public function test_players_render_without_autoplay_and_escape_filenames(): void
    {
        $attachment = $this->attachment();
        $attachment->original_filename = '<script>alert(1)</script>';
        $html = Blade::render('<x-question-media :items="$items" />', ['items' => [$attachment->mediaData()]]);
        $this->assertStringContainsString('<audio controls preload="none"', $html);
        $this->assertStringNotContainsString('autoplay', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_legacy_media_is_moved_and_public_source_removed(): void
    {
        $attachment = $this->attachment();
        $attachment->update(['disk' => 'exams', 'file_path' => 'questions/legacy.mp3']);
        Storage::disk('exams')->put('questions/legacy.mp3', 'legacy audio');
        $this->artisan('exams:privatize-media')->assertSuccessful();
        $attachment->refresh();
        $this->assertSame('question_media', $attachment->disk);
        $this->assertSame('legacy audio', Storage::disk('question_media')->get($attachment->file_path));
        Storage::disk('exams')->assertMissing('questions/legacy.mp3');
        $this->artisan('exams:privatize-media')->assertSuccessful();
    }
    public function test_edit_replaces_media_and_cannot_delete_another_questions_attachment(): void
    {
        $form = $this->form();
        $setId = $form->get('questionSetId');
        $form->set('questionMedia', [UploadedFile::fake()->create('old.mp3', 10, 'audio/mpeg')])->call('save');
        $question = Question::firstOrFail();
        $old = $question->attachments()->firstOrFail();
        $other = $this->attachment();
        Livewire::test(QuestionSetQuestionForm::class, ['questionSetId' => $setId, 'questionId' => $question->id])
            ->call('removeExistingMedia', $old->id)
            ->set('mediaToDelete', [$old->id, $other->id])
            ->set('questionMedia', [UploadedFile::fake()->create('new.mp4', 10, 'video/mp4')])
            ->call('save')->assertHasNoErrors()->assertRedirect();
        $this->assertNull(QuestionAttachment::find($old->id));
        $this->assertNotNull(QuestionAttachment::find($other->id));
        Storage::disk('question_media')->assertMissing($old->file_path);
        $this->assertSame('new.mp4', $question->attachments()->firstOrFail()->original_filename);
    }

    public function test_question_answered_after_editor_opened_cannot_be_modified(): void
    {
        $form = $this->form();
        $setId = $form->get('questionSetId');
        $form->call('save');
        $question = Question::firstOrFail();
        $editor = Livewire::test(QuestionSetQuestionForm::class, ['questionSetId' => $setId, 'questionId' => $question->id]);
        DB::table('responses')->insert(['question_id' => $question->id]);
        $editor->set('questionText', 'Changed after someone answered.')->call('save');
        $this->assertSame('Identify the sound or object shown.', $question->fresh()->question_text);
    }

    public function test_preview_and_offline_package_include_media_and_tables(): void
    {
        $attachment = $this->attachment();
        $exam = Exam::withoutEvents(fn () => Exam::create(['title' => 'Media exam', 'duration' => 60, 'status' => 'active']));
        $attachment->question->update(['exam_id' => $exam->id]);
        $attachment->question->attachments()->create(['attachment_type' => 'table', 'metadata' => ['headers' => ['Value'], 'rows' => [[1]]]]);
        $preview = new \App\Livewire\ExamPreview;
        $preview->exam = $exam;
        (new \ReflectionMethod($preview, 'loadQuestions'))->invoke($preview);
        $this->assertSame($attachment->id, $preview->questions[0]['media'][0]['id']);
        $this->assertSame(['Value'], $preview->questions[0]['table_data']['headers']);
        $response = app(\App\Http\Controllers\Api\OfflineExamPackageController::class)->package($exam->id);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $question = $response->getData(true)['package']['questions'][0];
        $this->assertSame(route('offline-exam-media.show', $attachment), $question['media'][0]['url']);
        $this->assertSame(['Value'], $question['table_data']['headers']);
    }

    public function test_expired_session_cannot_read_media(): void
    {
        $attachment = $this->attachment();
        $exam = Exam::withoutEvents(fn () => Exam::create(['duration' => 1, 'status' => 'active']));
        $session = ExamSession::create([
            'exam_id' => $exam->id, 'student_id' => 1, 'session_token' => 'expired-token', 'started_at' => now()->subMinutes(5),
        ]);
        ExamSessionQuestion::create(['exam_session_id' => $session->id, 'question_id' => $attachment->question_id, 'display_order' => 1]);
        try {
            app(QuestionMediaController::class)->show($this->mediaRequest('expired-token'), $attachment);
            $this->fail('Expired session accessed media');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_failed_attachment_save_rolls_back_question_and_uploaded_files(): void
    {
        \Illuminate\Support\Facades\Event::listen('eloquent.creating: '.QuestionAttachment::class, function () {
            throw new \RuntimeException('Simulated database failure');
        });
        $this->form()->set('questionMedia', [UploadedFile::fake()->create('sound.mp3', 10, 'audio/mpeg')])
            ->call('save')->assertNoRedirect();
        $this->assertSame(0, Question::count());
        $this->assertSame([], Storage::disk('question_media')->allFiles());
    }

}
