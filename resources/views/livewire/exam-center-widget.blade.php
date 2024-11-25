    <div>
        <div class="mt-20 mb-5 card mb-xl-10">
            <div class="border-0 card-header d-flex align-items-center justify-content-between">
                <!-- Card Title (left aligned) -->
                <div class="card-title d-flex align-items-center">
                  <h3 class="m-0 card-title align-items-start flex-column">
                    <span class="text-gray-900 card-label fw-bold">Exams</span>
                  </h3>
                </div>
                
                <!-- Toolbar (right aligned) -->
                <div class="card-toolbar d-flex align-items-center">
                  <!-- Search -->
                  <div class="position-relative me-3">
                    <form data-kt-search-element="form" class="w-100 position-relative" autocomplete="off">
                      <!--begin::Hidden input (Added to disable form autocomplete)-->
                      <input type="hidden">
                      <!--end::Hidden input-->
                      <!--begin::Icon-->
                      <i class="ki-duotone ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>
                      <!--end::Icon-->
                      <!--begin::Input-->
                      <input type="text" class="form-control form-control-solid ps-8" name="search" placeholder="Search Exams" wire:model.live="search">
                      <!--end::Input-->
                      <!--begin::Spinner-->
                      <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none" data-kt-search-element="spinner">
                        <span class="text-gray-500 align-middle spinner-border h-15px w-15px"></span>
                      </span>
                      <!--end::Spinner-->
                      <!--begin::Reset-->
                      <span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none" data-kt-search-element="clear">
                        <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                          <span class="path1"></span>
                          <span class="path2"></span>
                        </i>
                      </span>
                      <!--end::Reset-->
                    </form>
                  </div>
              {{-- Filter Dropdown --}}
              <div class="me-3">
                <div class="position-relative">
                  <select class="form-select form-select-sm" wire:model.live="filter">
                    <option value="">All</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
              </div>
                  <!-- Button -->
                  <a href="{{ route('exams.create') }}" class="btn btn-sm btn-success">Create a New Exam</a>
                </div>
              </div>
              
            <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                @include('components.partials.exam-table-header')
                <tbody>
                    @forelse ($exams as $exam)
                  @include('components.partials.exam-table-row', ['exam' => $exam])
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="pt-10 pb-10fs-6 fw-bold">No exams found.<br />
                                <a class="mt-5 btn btn-sm btn-success" href="{{ route('exams.create') }}">Create a New Exam</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                
                </tbody>
            </table>
        </div>
    </div>
    