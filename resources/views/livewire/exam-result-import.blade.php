

    <div class="container border p-10 rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Import Exam Results</h2>

        <form wire:submit.prevent="importResults" class="space-y-4">
            <div>
                <label for="examId" class="block text-sm font-medium text-gray-700">Exam</label>
                <select wire:model="examId" id="examId" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm form-select">
                    <option value="">Select an exam</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->course->name  }}</option>
                    @endforeach
                </select>
                @error('examId') 
                <div class="alert alert-danger mt-2">
                    <span class="">{{ $message }}</span> 
                </div>
                @enderror
            </div>

            <div class="mt-4">
                <label for="file" class="block text-sm font-medium text-gray-700">Upload File</label>
                <input wire:model="file" type="file" id="file" class="form-control">
                @error('file') 
                <div class="alert alert-danger mt-2">
                    <span class="">{{ $message }}</span> 
                </div>
                @enderror
            </div>

            <div class="mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 btn btn-primary">
                    Import Results
                </button>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
        </form>
    </div>

