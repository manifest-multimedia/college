<thead>
                    <tr class="fw-bolder text-muted">
                        <th class="ps-5 min-w-200px">Exam Name</th>
                        <th class="min-w-100px">Date Created</th>
                        <th class="min-w-100px">Duration</th>
                        <th class="min-w-100px">Class</th>
                        @if(Auth::user()->role=='admin' || Auth::user()->role=='Super Admin')
                        <th>Password</th>
                        @endif
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-100px">Actions</th>
                    </tr>
                </thead>