<thead>
                    <tr class="fw-bolder text-muted">
                        <th class="ps-5 min-w-200px">Exam Name</th>
                        <th class="min-w-100px">Date Created</th>
                        <th class="min-w-100px">Duration</th>
                        <th class="min-w-100px">Program</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin'])): ?>
                        <th>Password</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-100px">Actions</th>
                    </tr>
                </thead>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/partials/exam-table-header.blade.php ENDPATH**/ ?>