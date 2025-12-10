<x-dashboard.default>
    <x-slot name="title">
        Add Question
    </x-slot>

    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('question.sets') }}" class="text-decoration-none">
                                <i class="bi bi-collection me-1"></i>Question Sets
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('question.sets.questions', $questionSetId) }}" class="text-decoration-none">
                                <i class="bi bi-list me-1"></i>Questions
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi bi-plus-circle me-1"></i>Add Question
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="bi bi-plus-circle me-2"></i>Add Question
                            </h1>
                            <p class="text-muted mb-0">Create a new question for the question set</p>
                        </div>
                        <div>
                            <a href="{{ route('question.sets.questions', $questionSetId) }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-1"></i>Back to Questions
                            </a>
                            <a href="{{ route('question.sets.import', $questionSetId) }}" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>Bulk Import
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Form -->
        <div class="row">
            <div class="col-12">
                @livewire('question-set-question-form', ['questionSetId' => $questionSetId])
            </div>
        </div>
    </div>
</x-dashboard.default>
