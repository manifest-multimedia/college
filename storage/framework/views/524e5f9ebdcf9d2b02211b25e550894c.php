<div>
    
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">
            <h3>Extra Time Module</h3>
        </div>
        <div class="card-actions">
            
            <input type="text" wire:model.live="exam_filter" class="form-control" placeholder="Filter by Exam">
        </div>
    </div>
    <div class="card-body">
<div class="border-collapse table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Student</th>
                <th>Exam Session</th>
                <th>Exam Duration</th>
                <th>Extra Time</th> 
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
           <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      
               <tr>
                   <td> 
                       <div class="d-flex align-items-start flex-column">
                           <div class="flex-row d-flex me-2">
                               <strong>
                               <?php echo e($student->user ? $student->user->name : ''); ?>

                               </strong>
                           </div>
                       <div class="flex-row d-flex"><small><?php echo e($student->student_id); ?></small></div>
                       <div class="flex-row d-flex">
                           <small><?php echo e($student->email); ?></small>
                       </div>
                       </div>
                   </td>
                   <td>
                       <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->user && $student->user->examSessions): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $student->user->examSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($item->exam->display_title); ?> — <?php echo e($item->exam->course->name ?? 'Unknown Course'); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?> 
                            <span class="text-danger">No Exam Session</span>
                       <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                   </td>
                   <td>
                       <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->user && $student->user->examSessions): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $student->user->examSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($item->exam->duration); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?> 
                            <span class="text-danger">No Exam Session</span>
                       <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                   </td>
                   <td>
                    <div class="flex-row gap-2 d-flex align-items-center justify-content-center">
                        <div class="div">

                            <input type="text" wire:model="extraTime.<?php echo e($student->id); ?>" class="form-control" placeholder="Extra Time">
                        </div>
                        <div class="div">

                            <button class="btn btn-primary" wire:click="addMinutes(<?php echo e($student->id); ?>)">Add Minutes</button>
                        </div>
                    </div>


                   </td>
                   <td>
                       <button class="btn btn-primary" wire:click="viewDetails(<?php echo e($student->id); ?>)">View</button>
                       
                   </td>
               </tr>
                       
           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
       
    </div>
    <div class="card-footer">

    </div>
</div>
   


</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/extra-time-module.blade.php ENDPATH**/ ?>