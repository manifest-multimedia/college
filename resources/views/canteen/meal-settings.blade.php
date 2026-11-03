<x-dashboard.default title="Canteen meal windows">
    <div class="container-fluid py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-6">
            <div><h1 class="mb-2">Canteen meal windows</h1><p class="text-muted mb-0">Set when students can redeem Breakfast, Lunch and Supper.</p></div>
            <span class="badge badge-light-primary p-3">{{ $prefix }}</span>
        </div>
        @if(session('canteen_meal_status'))<div class="alert alert-success" role="status">{{ session('canteen_meal_status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Settings were not saved.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <p><a href="{{ route('canteen.meals.activity') }}" class="btn btn-light-primary">View meal attendance</a></p>
        @if($todayPrepared)<div class="alert alert-warning">Today's serving plan is already prepared. Changes to times or serving lanes apply to the next prepared day. Prepared offline scanners keep today's saved schedule.</div>@endif
        <div class="card"><div class="card-header"><h2 class="card-title">Daily serving schedule</h2></div><div class="card-body">
            <p class="text-muted">Before enabling meal service, update all serving scanners and assign every class below. A qualified student can redeem each meal once per day. Changing the schedule does not reset meals already redeemed.</p>
            <form method="POST" action="{{ route('canteen.meals.update') }}">
                @csrf
                <input type="hidden" name="version" value="{{ old('version', $settings['version'] ?? '') }}">
                <div class="row g-5 mb-6">
                    <div class="col-md-6"><label for="timezone" class="form-label">Institution timezone</label><input id="timezone" name="timezone" class="form-control" value="{{ old('timezone', $settings['timezone']) }}" required><div class="form-text">Use Africa/Accra for Ghana. All opening times, cutoffs and meal dates use this timezone.</div></div>
                    <div class="col-md-6 d-flex align-items-center"><input type="hidden" name="enabled" value="0"><label class="form-check"><input type="checkbox" class="form-check-input" name="enabled" value="1" @checked(old('enabled', $settings['enabled']))><span class="form-check-label">Enable meal redemption during these windows</span></label></div>
                </div>
                <div class="row g-5 mb-6">
                    @foreach($windows as $i => $window)
                    <div class="col-lg-4"><div class="border rounded p-5 h-100">
                        <h3 class="mb-5">{{ ucfirst($window['meal']) }}</h3>
                        <input type="hidden" name="windows[{{ $i }}][meal]" value="{{ $window['meal'] }}">
                        <div class="mb-4"><label for="meal-{{ $i }}-opens" class="form-label">Opens at</label><input type="time" id="meal-{{ $i }}-opens" class="form-control" name="windows[{{ $i }}][opens]" value="{{ old('windows.'.$i.'.opens', $window['opens']) }}" required></div>
                        <div><label for="meal-{{ $i }}-closes" class="form-label">Closes at</label><input type="time" id="meal-{{ $i }}-closes" class="form-control" name="windows[{{ $i }}][closes]" value="{{ old('windows.'.$i.'.closes', $window['closes']) }}" required></div>
                    </div></div>
                    @endforeach
                </div>
                <div class="alert alert-primary">Opening time is included; closing time is the cutoff. For a 7:00–7:45 AM window, redemption is allowed from 7:00 AM until just before 7:45 AM. Windows cannot overlap or continue past midnight.</div>
                <button type="submit" class="btn btn-primary">Save meal windows</button>
            </form>
        </div></div>
        <div class="card mt-8"><div class="card-header"><h2 class="card-title">Serving devices and class lanes</h2></div><div class="card-body">
            <p>Sign in on each scanner and register it from the app's workspace. Match its scanner code below, name the lane, and assign every class to one device. Staff can change shifts on that device without resetting meal records.</p>
            @if($devices->isEmpty())<div class="alert alert-primary">No serving devices registered yet. Install the meal-redemption app and register each scanner first.</div>
            @else
            <form method="POST" action="{{ route('canteen.meals.assignments') }}">@csrf
                <div class="row g-4 mb-6">@foreach($devices as $device)<div class="col-md-6"><label class="form-label" for="label-{{ $device->device_id }}">Scanner {{ strtoupper(substr($device->device_id, -6)) }}</label><input class="form-control" id="label-{{ $device->device_id }}" name="device_labels[{{ $device->device_id }}]" value="{{ old('device_labels.'.$device->device_id, $device->label) }}" maxlength="80" required></div>@endforeach</div>
                <div class="table-responsive"><table class="table"><thead><tr><th>Class</th><th>Students</th><th>Serving scanner</th></tr></thead><tbody>
                @foreach($groups as $group)<tr><td>{{ $group['label'] }}</td><td>{{ $group['count'] }}</td><td><label class="visually-hidden" for="group-{{ $loop->index }}">Scanner for {{ $group['label'] }}</label><select class="form-select" id="group-{{ $loop->index }}" name="assignments[{{ $group['key'] }}]" required><option value="">Choose scanner</option>@foreach($devices as $device)<option value="{{ $device->device_id }}" @selected(old('assignments.'.$group['key'], $assignments->get($group['key'])) === $device->device_id)>{{ $device->label }} · {{ strtoupper(substr($device->device_id, -6)) }}</option>@endforeach</select></td></tr>@endforeach
                </tbody></table></div><button class="btn btn-primary" type="submit">Save serving lanes</button>
            </form>
            @endif
        </div></div>
    </div>
</x-dashboard.default>
