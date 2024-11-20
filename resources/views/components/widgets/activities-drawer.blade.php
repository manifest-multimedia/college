  <!--begin::Activities drawer-->
  <div id="kt_activities" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="activities"
  data-kt-drawer-activate="true" data-kt-drawer-overlay="true"
  data-kt-drawer-width="{default:'300px', 'lg': '900px'}" data-kt-drawer-direction="end"
  data-kt-drawer-toggle="#kt_activities_toggle" data-kt-drawer-close="#kt_activities_close">
  <div class="border-0 shadow-none card rounded-0">
      <!--begin::Header-->
      <div class="card-header" id="kt_activities_header">
          <h3 class="text-gray-900 card-title fw-bold">Activity Logs</h3>
          <div class="card-toolbar">
              <button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5"
                  id="kt_activities_close">
                  <i class="ki-duotone ki-cross fs-1">
                      <span class="path1"></span>
                      <span class="path2"></span>
                  </i>
              </button>
          </div>
      </div>
      <!--end::Header-->
      <!--begin::Body-->
      <div class="card-body position-relative" id="kt_activities_body">
          <!--begin::Content-->
          <div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true"
              data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body"
              data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer"
              data-kt-scroll-offset="5px">
              <!--begin::Timeline items-->
              <div class="timeline timeline-border-dashed">
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-message-text-2 fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                              <span class="path3"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">There are 2 new tasks for you in “AirPlus
                                  Mobile App” project:</div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Added at 4:23 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top" title="Nina Nilson">
                                      <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                          <!--begin::Timeline details-->
                          <div class="overflow-auto pb-5">
                              <!--begin::Record-->
                              <div
                                  class="px-7 py-3 mb-5 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-750px">
                                  <!--begin::Title-->
                                  <a href="apps/projects/project.html"
                                      class="text-gray-900 fs-5 text-hover-primary fw-semibold w-375px min-w-200px">Meeting
                                      with customer</a>
                                  <!--end::Title-->
                                  <!--begin::Label-->
                                  <div class="min-w-175px pe-2">
                                      <span class="badge badge-light text-muted">Application Design</span>
                                  </div>
                                  <!--end::Label-->
                                  <!--begin::Users-->
                                  <div
                                      class="flex-nowrap symbol-group symbol-hover flex-grow-1 min-w-100px pe-2">
                                      <!--begin::User-->
                                      <div class="symbol symbol-circle symbol-25px">
                                          <img src="assets/media/avatars/300-2.jpg" alt="img" />
                                      </div>
                                      <!--end::User-->
                                      <!--begin::User-->
                                      <div class="symbol symbol-circle symbol-25px">
                                          <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                      </div>
                                      <!--end::User-->
                                      <!--begin::User-->
                                      <div class="symbol symbol-circle symbol-25px">
                                          <div
                                              class="symbol-label fs-8 fw-semibold bg-primary text-inverse-primary">
                                              A</div>
                                      </div>
                                      <!--end::User-->
                                  </div>
                                  <!--end::Users-->
                                  <!--begin::Progress-->
                                  <div class="min-w-125px pe-2">
                                      <span class="badge badge-light-primary">In Progress</span>
                                  </div>
                                  <!--end::Progress-->
                                  <!--begin::Action-->
                                  <a href="apps/projects/project.html"
                                      class="btn btn-sm btn-light btn-active-light-primary">View</a>
                                  <!--end::Action-->
                              </div>
                              <!--end::Record-->
                              <!--begin::Record-->
                              <div
                                  class="px-7 py-3 mb-0 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-750px">
                                  <!--begin::Title-->
                                  <a href="apps/projects/project.html"
                                      class="text-gray-900 fs-5 text-hover-primary fw-semibold w-375px min-w-200px">Project
                                      Delivery Preparation</a>
                                  <!--end::Title-->
                                  <!--begin::Label-->
                                  <div class="min-w-175px">
                                      <span class="badge badge-light text-muted">CRM System Development</span>
                                  </div>
                                  <!--end::Label-->
                                  <!--begin::Users-->
                                  <div class="flex-nowrap symbol-group symbol-hover flex-grow-1 min-w-100px">
                                      <!--begin::User-->
                                      <div class="symbol symbol-circle symbol-25px">
                                          <img src="assets/media/avatars/300-20.jpg" alt="img" />
                                      </div>
                                      <!--end::User-->
                                      <!--begin::User-->
                                      <div class="symbol symbol-circle symbol-25px">
                                          <div
                                              class="symbol-label fs-8 fw-semibold bg-success text-inverse-primary">
                                              B</div>
                                      </div>
                                      <!--end::User-->
                                  </div>
                                  <!--end::Users-->
                                  <!--begin::Progress-->
                                  <div class="min-w-125px">
                                      <span class="badge badge-light-success">Completed</span>
                                  </div>
                                  <!--end::Progress-->
                                  <!--begin::Action-->
                                  <a href="apps/projects/project.html"
                                      class="btn btn-sm btn-light btn-active-light-primary">View</a>
                                  <!--end::Action-->
                              </div>
                              <!--end::Record-->
                          </div>
                          <!--end::Timeline details-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon me-4">
                          <i class="text-gray-500 ki-duotone ki-flag fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n2">
                          <!--begin::Timeline heading-->
                          <div class="overflow-auto pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">Invitation for crafting engaging designs that
                                  speak human workshop</div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Sent at 4:23 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top" title="Alan Nilson">
                                      <img src="assets/media/avatars/300-1.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-disconnect fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                              <span class="path3"></span>
                              <span class="path4"></span>
                              <span class="path5"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <a href="#"
                                  class="mb-2 text-gray-800 fs-5 fw-semibold text-hover-primary">3 New Incoming
                                  Project Files:</a>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Sent at 10:30 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top" title="Jan Hummer">
                                      <img src="assets/media/avatars/300-23.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                          <!--begin::Timeline details-->
                          <div class="overflow-auto pb-5">
                              <div
                                  class="p-5 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-700px">
                                  <!--begin::Item-->
                                  <div class="d-flex flex-aligns-center pe-10 pe-lg-20">
                                      <!--begin::Icon-->
                                      <img alt="" class="w-30px me-3"
                                          src="assets/media/svg/files/pdf.svg" />
                                      <!--end::Icon-->
                                      <!--begin::Info-->
                                      <div class="ms-1 fw-semibold">
                                          <!--begin::Desc-->
                                          <a href="apps/projects/project.html"
                                              class="fs-6 text-hover-primary fw-bold">Finance KPI App
                                              Guidelines</a>
                                          <!--end::Desc-->
                                          <!--begin::Number-->
                                          <div class="text-gray-500">1.9mb</div>
                                          <!--end::Number-->
                                      </div>
                                      <!--begin::Info-->
                                  </div>
                                  <!--end::Item-->
                                  <!--begin::Item-->
                                  <div class="d-flex flex-aligns-center pe-10 pe-lg-20">
                                      <!--begin::Icon-->
                                      <img alt="apps/projects/project.html" class="w-30px me-3"
                                          src="assets/media/svg/files/doc.svg" />
                                      <!--end::Icon-->
                                      <!--begin::Info-->
                                      <div class="ms-1 fw-semibold">
                                          <!--begin::Desc-->
                                          <a href="#" class="fs-6 text-hover-primary fw-bold">Client UAT
                                              Testing Results</a>
                                          <!--end::Desc-->
                                          <!--begin::Number-->
                                          <div class="text-gray-500">18kb</div>
                                          <!--end::Number-->
                                      </div>
                                      <!--end::Info-->
                                  </div>
                                  <!--end::Item-->
                                  <!--begin::Item-->
                                  <div class="d-flex flex-aligns-center">
                                      <!--begin::Icon-->
                                      <img alt="apps/projects/project.html" class="w-30px me-3"
                                          src="assets/media/svg/files/css.svg" />
                                      <!--end::Icon-->
                                      <!--begin::Info-->
                                      <div class="ms-1 fw-semibold">
                                          <!--begin::Desc-->
                                          <a href="#" class="fs-6 text-hover-primary fw-bold">Finance
                                              Reports</a>
                                          <!--end::Desc-->
                                          <!--begin::Number-->
                                          <div class="text-gray-500">20mb</div>
                                          <!--end::Number-->
                                      </div>
                                      <!--end::Icon-->
                                  </div>
                                  <!--end::Item-->
                              </div>
                          </div>
                          <!--end::Timeline details-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-abstract-26 fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">Task
                                  <a href="#" class="text-primary fw-bold me-1">#45890</a>merged with
                                  <a href="#" class="text-primary fw-bold me-1">#45890</a>in “Ads Pro
                                  Admin Dashboard project:
                              </div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Initiated at 4:23 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top" title="Nina Nilson">
                                      <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-pencil fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">3 new application design concepts added:</div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Created at 4:23 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top"
                                      title="Marcus Dotson">
                                      <img src="assets/media/avatars/300-2.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                          <!--begin::Timeline details-->
                          <div class="overflow-auto pb-5">
                              <div
                                  class="p-7 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-700px">
                                  <!--begin::Item-->
                                  <div class="overlay me-10">
                                      <!--begin::Image-->
                                      <div class="overlay-wrapper">
                                          <img alt="img" class="rounded w-150px"
                                              src="assets/media/stock/600x400/img-29.jpg" />
                                      </div>
                                      <!--end::Image-->
                                      <!--begin::Link-->
                                      <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                          <a href="#"
                                              class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                      </div>
                                      <!--end::Link-->
                                  </div>
                                  <!--end::Item-->
                                  <!--begin::Item-->
                                  <div class="overlay me-10">
                                      <!--begin::Image-->
                                      <div class="overlay-wrapper">
                                          <img alt="img" class="rounded w-150px"
                                              src="assets/media/stock/600x400/img-31.jpg" />
                                      </div>
                                      <!--end::Image-->
                                      <!--begin::Link-->
                                      <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                          <a href="#"
                                              class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                      </div>
                                      <!--end::Link-->
                                  </div>
                                  <!--end::Item-->
                                  <!--begin::Item-->
                                  <div class="overlay">
                                      <!--begin::Image-->
                                      <div class="overlay-wrapper">
                                          <img alt="img" class="rounded w-150px"
                                              src="assets/media/stock/600x400/img-40.jpg" />
                                      </div>
                                      <!--end::Image-->
                                      <!--begin::Link-->
                                      <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                          <a href="#"
                                              class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                      </div>
                                      <!--end::Link-->
                                  </div>
                                  <!--end::Item-->
                              </div>
                          </div>
                          <!--end::Timeline details-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-sms fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">New case
                                  <a href="#" class="text-primary fw-bold me-1">#67890</a>is assigned to
                                  you in Multi-platform Database Design project
                              </div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="overflow-auto pb-5">
                                  <!--begin::Wrapper-->
                                  <div class="mt-1 d-flex align-items-center fs-6">
                                      <!--begin::Info-->
                                      <div class="text-muted me-2 fs-7">Added at 4:23 PM by</div>
                                      <!--end::Info-->
                                      <!--begin::User-->
                                      <a href="#" class="text-primary fw-bold me-1">Alice Tan</a>
                                      <!--end::User-->
                                  </div>
                                  <!--end::Wrapper-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-pencil fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="mb-10 timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">You have received a new order:</div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Placed at 5:05 AM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                      data-bs-boundary="window" data-bs-placement="top" title="Robert Rich">
                                      <img src="assets/media/avatars/300-4.jpg" alt="img" />
                                  </div>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                          <!--begin::Timeline details-->
                          <div class="overflow-auto pb-5">
                              <!--begin::Notice-->
                              <div
                                  class="flex-shrink-0 p-6 rounded border border-dashed notice d-flex bg-light-primary border-primary min-w-lg-600px">
                                  <!--begin::Icon-->
                                  <i class="ki-duotone ki-devices-2 fs-2tx text-primary me-4">
                                      <span class="path1"></span>
                                      <span class="path2"></span>
                                      <span class="path3"></span>
                                  </i>
                                  <!--end::Icon-->
                                  <!--begin::Wrapper-->
                                  <div class="flex-wrap d-flex flex-stack flex-grow-1 flex-md-nowrap">
                                      <!--begin::Content-->
                                      <div class="mb-3 mb-md-0 fw-semibold">
                                          <h4 class="text-gray-900 fw-bold">Database Backup Process Completed!
                                          </h4>
                                          <div class="text-gray-700 fs-6 pe-7">Login into Admin Dashboard to
                                              make sure the data integrity is OK</div>
                                      </div>
                                      <!--end::Content-->
                                      <!--begin::Action-->
                                      <a href="#"
                                          class="px-6 btn btn-primary align-self-center text-nowrap">Proceed</a>
                                      <!--end::Action-->
                                  </div>
                                  <!--end::Wrapper-->
                              </div>
                              <!--end::Notice-->
                          </div>
                          <!--end::Timeline details-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
                  <!--begin::Timeline item-->
                  <div class="timeline-item">
                      <!--begin::Timeline line-->
                      <div class="timeline-line"></div>
                      <!--end::Timeline line-->
                      <!--begin::Timeline icon-->
                      <div class="timeline-icon">
                          <i class="text-gray-500 ki-duotone ki-basket fs-2">
                              <span class="path1"></span>
                              <span class="path2"></span>
                              <span class="path3"></span>
                              <span class="path4"></span>
                          </i>
                      </div>
                      <!--end::Timeline icon-->
                      <!--begin::Timeline content-->
                      <div class="timeline-content mt-n1">
                          <!--begin::Timeline heading-->
                          <div class="mb-5 pe-3">
                              <!--begin::Title-->
                              <div class="mb-2 fs-5 fw-semibold">New order
                                  <a href="#" class="text-primary fw-bold me-1">#67890</a>is placed for
                                  Workshow Planning & Budget Estimation
                              </div>
                              <!--end::Title-->
                              <!--begin::Description-->
                              <div class="mt-1 d-flex align-items-center fs-6">
                                  <!--begin::Info-->
                                  <div class="text-muted me-2 fs-7">Placed at 4:23 PM by</div>
                                  <!--end::Info-->
                                  <!--begin::User-->
                                  <a href="#" class="text-primary fw-bold me-1">Jimmy Bold</a>
                                  <!--end::User-->
                              </div>
                              <!--end::Description-->
                          </div>
                          <!--end::Timeline heading-->
                      </div>
                      <!--end::Timeline content-->
                  </div>
                  <!--end::Timeline item-->
              </div>
              <!--end::Timeline items-->
          </div>
          <!--end::Content-->
      </div>
      <!--end::Body-->
      <!--begin::Footer-->
      <div class="py-5 text-center card-footer" id="kt_activities_footer">
          <a href="pages/user-profile/activity.html" class="btn btn-bg-body text-primary">View All Activities
              <i class="ki-duotone ki-arrow-right fs-3 text-primary">
                  <span class="path1"></span>
                  <span class="path2"></span>
              </i></a>
      </div>
      <!--end::Footer-->
  </div>
</div>
<!--end::Activities drawer-->