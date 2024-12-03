<div class="p-5 m-5 rounded border border-1 border-dark">
    <h1>
        Records for Leticia, RGN Class

    </h1>
    @if (env('APP_ENV') != 'local')
    <p>
        {{-- Check if Env is Production --}}

        <span class="badge bg-success">Production</span>
        @if(\App\Models\ExamSession::where('student_id', 131)->count() > 0)
        @foreach (\App\Models\ExamSession::where('student_id', 131)->get() as $sessions)
        {{ \App\Models\User::where('id', 131)->first()->name }} <br>
        {{-- {{ $sessions->exam->exam_code }} <br> --}}
        Course: {{ $sessions->exam->course->name }} <br>
        Responses: {{ $sessions->responses->count() }} <br>
        Session ID: {{ $sessions->id }} <br>
        <hr>

        @endforeach
        @endif


    </p>
    @endif
    @if (env('APP_ENV') == 'local')

    <p> <span class="badge bg-warning">Local</span>
        {{-- ID on Local is 90 --}}
        @if(\App\Models\ExamSession::where('student_id', 90)->count() > 0)
        @foreach (\App\Models\ExamSession::where('student_id', 90)->get() as $sessions)
        {{ \App\Models\User::where('id', 90)->first()->name }} <br>
        {{-- {{ $sessions->exam->exam_code }} <br> --}}
        Course: {{ $sessions->exam->course->name }} <br>
        Responses: {{ $sessions->responses->count() }} <br>
        Session ID: {{ $sessions->id }} <br>
        <hr>

        @endforeach
        @endif
    </p>
    @endif

</div>
<div class="p-5 m-5 rounded border border-1 border-dark">
    <h1>
        Afia Agyemang | Leticia Reconciliation

    </h1>
    @if (env('APP_ENV') != 'local')
    <p>
        {{-- Check if Env is Production --}}

        <span class="badge bg-success">Production</span>
        @if(\App\Models\ExamSession::where('student_id', 191)->count() > 0)
        @foreach (\App\Models\ExamSession::where('student_id', 191)->get() as $sessions)
        {{ \App\Models\User::where('id', 191)->first()->name }} <br>
        Course: {{ $sessions->exam->course->name }} <br>
        Responses: {{ $sessions->responses->count() }} <br>
        Session ID: {{ $sessions->id }} <br>
        <hr>

        @endforeach
        @endif


    </p>
    @endif
    @if (env('APP_ENV') == 'local')

    <p> <span class="badge bg-warning">Local</span>
        @if(\App\Models\ExamSession::where('student_id', 36)->count() > 0)
        @foreach (\App\Models\ExamSession::where('student_id', 36)->get() as $sessions)
        {{ \App\Models\User::where('id', 36)->first()->name }} <br>
        Course: {{ $sessions->exam->course->name }} <br>
        Responses: {{ $sessions->responses->count() }} <br>
        Session ID: {{ $sessions->id }} <br>
        <hr>

        @endforeach
        @endif
    </p>
    @endif

</div>