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
                                        <span class="badge badge-light-primary ms-2">{{ $exam->total_questions_count ?? 0 }} questions</span>
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
                            <span class="text-muted fw-semibold d-block fs-7">
                                <small>Created by: {{ $exam->user ? $exam->user->name : 'Unknown' }}</small>
                            </span>
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
                            @php
                                $statusClass = [
                                    'upcoming' => 'badge-light-primary',
                                    'active' => 'badge-light-success',
                                    'completed' => 'badge-light-info',
                                    'cancelled' => 'badge-light-danger',
                                ][$exam->status] ?? 'badge-light-warning';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="px-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary" type="button" id="dropdownMenuButton-{{ $exam->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $exam->id }}">
                                    <li>
                                        <a href="{{ route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id) }}" class="dropdown-item">
                                            <i class="ki-duotone ki-bank fs-6 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>Question Bank
                                        </a>
                                    </li>
                                    
                                    @if(Auth::user()->role=='admin' || Auth::user()->role=='Super Admin')
                                        <li>
                                            <a href="{{ route('exams.edit', $exam->slug ? $exam->slug : $exam->id) }}" class="dropdown-item">
                                                <i class="ki-duotone ki-pencil fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('exam.results', $exam->slug ? $exam->slug : $exam->id) }}" class="dropdown-item">
                                                <i class="ki-duotone ki-document fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>Generate Results
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a href="javascript:void(0)" wire:click="$dispatch('confirmDelete', { examId: {{ $exam->id }}, examName: '{{ $exam->course ? $exam->course->name : 'this exam' }}' })" class="dropdown-item text-danger">
                                                <i class="ki-duotone ki-trash fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>Delete
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>