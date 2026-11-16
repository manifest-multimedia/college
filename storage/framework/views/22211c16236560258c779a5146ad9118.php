<div data-kt-search-element="content"
                                        class="overflow-hidden px-7 py-7 menu menu-sub menu-sub-dropdown w-300px w-md-350px">
                                        <!--begin::Wrapper-->
                                        <div data-kt-search-element="wrapper">
                                            <!--begin::Recently viewed-->
                                            <div data-kt-search-element="results" class="d-none">
                                                <!--begin::Items-->
                                                
                                                <!--end::Items-->
                                            </div>
                                            <!--end::Recently viewed-->
                                            <!--begin::Recently viewed-->
                                            
                                            <!--end::Recently viewed-->
                                            <!--begin::Empty-->
                                            <div data-kt-search-element="empty" class="text-center d-none">
                                                <!--begin::Icon-->
                                                <div class="pt-10 pb-10">
                                                    <i class="opacity-50 ki-duotone ki-search-list fs-4x">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                </div>
                                                <!--end::Icon-->
                                                <!--begin::Message-->
                                                <div class="pb-15 fw-semibold">
                                                    <h3 class="mb-2 text-gray-600 fs-5">No result found</h3>
                                                    <div class="text-muted fs-7">Please try again with a different
                                                        query</div>
                                                </div>
                                                <!--end::Message-->
                                            </div>
                                            <!--end::Empty-->
                                        </div>
                                        <!--end::Wrapper-->
                                        <!--begin::Preferences-->
                                        <form data-kt-search-element="advanced-options-form" class="pt-1 d-none">
                                            <!--begin::Heading-->
                                            <h3 class="mb-7 text-gray-900 fw-semibold">Advanced Search</h3>
                                            <!--end::Heading-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <input type="text"
                                                    class="form-control form-control-sm form-control-solid"
                                                    placeholder="Contains the word" name="query" />
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <!--begin::Radio group-->
                                                <div class="nav-group nav-group-fluid">
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="type"
                                                            value="has" checked="checked" />
                                                        <span
                                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary">All</span>
                                                    </label>
                                                    <!--end::Option-->
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="type"
                                                            value="users" />
                                                        <span
                                                            class="px-4 btn btn-sm btn-color-muted btn-active btn-active-primary">Users</span>
                                                    </label>
                                                    <!--end::Option-->
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="type"
                                                            value="orders" />
                                                        <span
                                                            class="px-4 btn btn-sm btn-color-muted btn-active btn-active-primary">Orders</span>
                                                    </label>
                                                    <!--end::Option-->
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="type"
                                                            value="projects" />
                                                        <span
                                                            class="px-4 btn btn-sm btn-color-muted btn-active btn-active-primary">Projects</span>
                                                    </label>
                                                    <!--end::Option-->
                                                </div>
                                                <!--end::Radio group-->
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <input type="text" name="assignedto"
                                                    class="form-control form-control-sm form-control-solid"
                                                    placeholder="Assigned to" value="" />
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <input type="text" name="collaborators"
                                                    class="form-control form-control-sm form-control-solid"
                                                    placeholder="Collaborators" value="" />
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <!--begin::Radio group-->
                                                <div class="nav-group nav-group-fluid">
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="attachment"
                                                            value="has" checked="checked" />
                                                        <span
                                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary">Has
                                                            attachment</span>
                                                    </label>
                                                    <!--end::Option-->
                                                    <!--begin::Option-->
                                                    <label>
                                                        <input type="radio" class="btn-check" name="attachment"
                                                            value="any" />
                                                        <span
                                                            class="px-4 btn btn-sm btn-color-muted btn-active btn-active-primary">Any</span>
                                                    </label>
                                                    <!--end::Option-->
                                                </div>
                                                <!--end::Radio group-->
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <select name="timezone" aria-label="Select a Timezone"
                                                    data-control="select2" data-dropdown-parent="#kt_header_search"
                                                    data-placeholder="date_period"
                                                    class="form-select form-select-sm form-select-solid">
                                                    <option value="next">Within the next</option>
                                                    <option value="last">Within the last</option>
                                                    <option value="between">Between</option>
                                                    <option value="on">On</option>
                                                </select>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-8 row">
                                                <!--begin::Col-->
                                                <div class="col-6">
                                                    <input type="number" name="date_number"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Lenght" value="" />
                                                </div>
                                                <!--end::Col-->
                                                <!--begin::Col-->
                                                <div class="col-6">
                                                    <select name="date_typer" aria-label="Select a Timezone"
                                                        data-control="select2"
                                                        data-dropdown-parent="#kt_header_search"
                                                        data-placeholder="Period"
                                                        class="form-select form-select-sm form-select-solid">
                                                        <option value="days">Days</option>
                                                        <option value="weeks">Weeks</option>
                                                        <option value="months">Months</option>
                                                        <option value="years">Years</option>
                                                    </select>
                                                </div>
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Actions-->
                                            <div class="d-flex justify-content-end">
                                                <button type="reset"
                                                    class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                                    data-kt-search-element="advanced-options-form-cancel">Cancel</button>
                                                <a href="utilities/search/horizontal.html"
                                                    class="btn btn-sm fw-bold btn-primary"
                                                    data-kt-search-element="advanced-options-form-search">Search</a>
                                            </div>
                                            <!--end::Actions-->
                                        </form>
                                        <!--end::Preferences-->
                                        <!--begin::Preferences-->
                                        <form data-kt-search-element="preferences" class="pt-1 d-none">
                                            <!--begin::Heading-->
                                            <h3 class="mb-7 text-gray-900 fw-semibold">Search Preferences</h3>
                                            <!--end::Heading-->
                                            <!--begin::Input group-->
                                            <div class="pb-4 border-bottom">
                                                <label
                                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                    <span
                                                        class="text-gray-700 form-check-label fs-6 fw-semibold ms-0 me-2">Projects</span>
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        checked="checked" />
                                                </label>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="py-4 border-bottom">
                                                <label
                                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                    <span
                                                        class="text-gray-700 form-check-label fs-6 fw-semibold ms-0 me-2">Targets</span>
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        checked="checked" />
                                                </label>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="py-4 border-bottom">
                                                <label
                                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                    <span
                                                        class="text-gray-700 form-check-label fs-6 fw-semibold ms-0 me-2">Affiliate
                                                        Programs</span>
                                                    <input class="form-check-input" type="checkbox"
                                                        value="1" />
                                                </label>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="py-4 border-bottom">
                                                <label
                                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                    <span
                                                        class="text-gray-700 form-check-label fs-6 fw-semibold ms-0 me-2">Referrals</span>
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        checked="checked" />
                                                </label>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="py-4 border-bottom">
                                                <label
                                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                                    <span
                                                        class="text-gray-700 form-check-label fs-6 fw-semibold ms-0 me-2">Users</span>
                                                    <input class="form-check-input" type="checkbox"
                                                        value="1" />
                                                </label>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Actions-->
                                            <div class="pt-7 d-flex justify-content-end">
                                                <button type="reset"
                                                    class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                                    data-kt-search-element="preferences-dismiss">Cancel</button>
                                                <button type="submit" class="btn btn-sm fw-bold btn-primary">Save
                                                    Changes</button>
                                            </div>
                                            <!--end::Actions-->
                                        </form>
                                        <!--end::Preferences-->
                                    </div><?php /**PATH /home/johnsonsebire/www/college/resources/views/components/partials/recently-searched.blade.php ENDPATH**/ ?>