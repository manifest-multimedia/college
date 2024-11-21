    <div>
        <div class="mt-20 mb-5 card mb-xl-10">
            <div class="border-0 card-header">
                <div class="card-title">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="text-gray-900 card-label fw-bold">Exams</span>
                    </h3>
                </div>
            </div>
            <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                <thead>
                    <tr class="fw-bolder text-muted">
                        <th class="ps-5 min-w-200px">Exam Name</th>
                        <th class="min-w-100px">Date Created</th>
                        <th class="min-w-100px">Duration</th>
                        <th class="min-w-100px">Class</th>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5 ps-5">
                                    <span class="symbol-label bg-light-danger">
                                        <span class="text-white fs-6">A</span>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="#" class="mb-1 text-dark fw-bold text-hover-primary fs-6">{{ $exam->subject }}</a>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $exam->subject }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="mb-1 text-dark fw-bold text-hover-primary d-block fs-6">{{ $exam->subject }}</a>
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->subject }}</span>
                        </td>
                        <td>    
                            <a href="#" class="mb-1 text-dark fw-bold text-hover-primary d-block fs-6">{{ $exam->subject }}</a>
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->subject }}</span>
                        </td>
                        <td>
                            <a href="#" class="mb-1 text-dark fw-bold text-hover-primary d-block fs-6">{{ $exam->subject }}</a>
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->subject }}</span>
                        </td>
                        <td>
                            <a href="#" class="mb-1 text-dark fw-bold text-hover-primary d-block fs-6">{{ $exam->subject }}</a>
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->subject }}</span>
                        </td>
                        <td class="">
                            <a href="#" class="btn btn-sm btn-light btn-active-light-primary">Access Question Bank</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="pt-10 pb-10fs-6 fw-bold">You have not created any exams yet.<br />
                                <a class="mt-5 btn btn-sm btn-success" href="{{ route('exams.create') }}">Create a New Exam</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                
                </tbody>
            </table>
        </div>
    </div>
    