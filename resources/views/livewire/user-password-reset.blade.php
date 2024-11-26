<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    <div class="container">
    <div class="card">
        <div class="card-header">

            <div class="card-title">
                <h3 class="h3">
                    Password Reset
                </h3>
            </div>
        </div>

        <div class="gap-3 card-body d-flex flex-column">
            
            <div class="row">
    
                <div class="form-group">
                    <label class="mb-2" for="email">Email</label>
                    <input class="form-control" type="text" name="user_email" id="user_email" placeholder="Please Input Your Email"
                    
                    wire:model.live="user_email"
                    >
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label class="mb-2" for="password">Password</label>
                   <input class="form-control" type="password" name="password" id="password" wire:model.live="password">

                </div>
            </div>
            <div class="row">
                <div class="form-group">

                    <label class="mb-2" for="password_confirm">Password Confirmation</label>
                    <input class="form-control" type="password" name="password_confirm" id="password_confirm" wire:model.live="password_confirm">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">Reset Password</button>
        </div>
        </div>

    </div>
        
</div>
