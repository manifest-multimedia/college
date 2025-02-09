<div>
    {{-- Do your work, then step back. --}}

    <div class="modal fade" id="kt_modal_support_request" tabindex="-1" aria-hidden="true" wire:ignore.self
        data-bs-backdrop="static">
        <!--begin::Modal dialog-->
        <div class="modal-dialog">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header header-bg">
                    <!--begin::Modal title-->
                    </p>
                    <h2 class="text-white">Support Request
                        <small class="ms-2 fs-7 fw-normal text-white opacity-50">Request Technical Support</small>
                    </h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-color-white btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <p>Need help? We're here for you! Let us know how we can assist—just submit your request using the form below.
                        <!--begin::Form-->
                    <form class="mx-auto w-100 mw-600px " novalidate="novalidate" id="kt_modal_feature_request_form"
                        method="post">
                        <!--begin::Input group-->
                        
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-15">
                            <!--begin::Label-->
                            <label class="required fs-6 fw-semibold mb-2">Message</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea class="form-control form-control-solid" rows="7" name="feature_description"
                                wire:model="feature_description" placeholder="Tell us about your challenge."></textarea>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Modal body-->
                <!--begin::Modal footer-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="submitRequest">Submit</button>
                </div>
                <!--end::Modal footer-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>

</div>
