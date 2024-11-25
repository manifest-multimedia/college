  <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5 ps-5">
                                    <span class="symbol-label bg-primary">
                                        <span class="text-white fs-6">{{ getFirstLetter($exam->course->name )}}</span>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="{{ route('questionbank.with.id', $exam->id) }}" class="mb-1 text-dark fw-bold text-hover-primary fs-6">{{ $exam->course->name  }}</a>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')' }}</span>
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
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ $exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')' }}</span>
                        </td>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="">
                            <a href="{{ route('questionbank.with.id', $exam->id) }}" class="btn btn-sm btn-light btn-active-light-primary">Access Question Bank</a>
                        </td>
                    </tr>