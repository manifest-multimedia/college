<thead>
                    <tr class="fw-bolder text-muted">
                        <th class="ps-5 min-w-200px">Exam Name</th>
                        <th class="min-w-100px">Date Created</th>
                        <th class="min-w-100px">Duration</th>
                        <th class="min-w-100px">Program</th>
                        @if(Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin']))
                        <th>Password</th>
                        @endif
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-100px">Actions</th>
                    </tr>
                </thead>
