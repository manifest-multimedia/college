<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5 ps-5">
                                    <span class="symbol-label bg-primary">
                                        <span class="text-white fs-6">{{ $exam->course ? getFirstLetter($exam->course->name) : 'N/A' }}</span>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id) }}" class="mb-1 text-dark fw-bold text-hover-primary fs-6">{{ $exam->course ? $exam->course->name : 'No Course Name' }}</a>
                                        <span class="badge badge-light-primary ms-2">{{ $exam->questions_count ?? 0 }} questions</span>
                                    </div>
                                    <span class="text-muted fw-semibold d-block fs-7">
                                        @if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester)
                                            {{ $exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')' }}
                                        @else
                                            No details available
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->created_at }}</span>
                        </td>
                        <td>    
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->duration }}</span>
                        </td>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">
                                @if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester)
                                    {{ $exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')' }}
                                @else
                                    No details available
                                @endif
                            </span>
                        </td>
                        @if(Auth::user()->role=='admin' || Auth::user()->role=='Super Admin')
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->password }}</span>
                        </td>
                        @endif
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="px-3">
                            <a href="{{ route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id) }}" class="btn btn-sm btn-light btn-active-light-primary">Question Bank</a>
                            {{-- Delete --}}
                            @if(Auth::user()->role=='admin' || Auth::user()->role=='Super Admin')
                            <!-- Edit -->
                            <a href="{{ route('exams.edit', $exam->slug ? $exam->slug : $exam->id) }}" class="btn btn-sm btn-light btn-active-light-primary">Edit</a>
                            {{-- Generate Results --}}
                            <a href="{{ route('exam.results', $exam->slug ? $exam->slug : $exam->id) }}" class="btn btn-sm btn-light btn-active-light-primary">Generate Results</a>
                            <!-- Delete -->
                            <a href="javascript:void(0)" wire:click="deleteExam({{ $exam->id }})" class="btn btn-sm btn-light btn-active-light-danger">Delete</a>
                            @endif
                        </td>
                    </tr>