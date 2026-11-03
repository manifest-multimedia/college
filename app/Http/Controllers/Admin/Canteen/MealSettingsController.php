<?php

namespace App\Http\Controllers\Admin\Canteen;

use App\Http\Controllers\Controller;
use App\Services\Canteen\CanteenMealLanes;
use App\Services\Canteen\CanteenMealPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealSettingsController extends Controller
{
    public function index(CanteenMealPolicy $policy, CanteenMealLanes $lanes)
    {
        $settings = $policy->get();
        // PNMTC's confirmed schedule is a suggested form value only. A new
        // institution must enter its own windows; no schedule is activated here.
        $suggested = $policy->prefix() === 'PNMTC/DA' ? [
            ['meal' => 'breakfast', 'opens' => '07:00', 'closes' => '07:45'],
            ['meal' => 'lunch', 'opens' => '13:00', 'closes' => '13:45'],
            ['meal' => 'supper', 'opens' => '17:00', 'closes' => '17:45'],
        ] : array_map(fn ($meal) => ['meal' => $meal, 'opens' => '', 'closes' => ''], CanteenMealPolicy::MEALS);

        return view('canteen.meal-settings', ['settings' => $settings, 'windows' => $settings['windows'] ?: $suggested,
            'prefix' => $policy->prefix(), 'groups' => $lanes->roster()['groups'],
            'devices' => DB::table('canteen_meal_devices')->orderBy('label')->get(),
            'assignments' => DB::table('canteen_meal_assignments')->where('institution_prefix', $policy->prefix())->pluck('device_id', 'group_key'),
            'todayPrepared' => $lanes->currentPlan() !== null]);
    }

    public function update(Request $request, CanteenMealPolicy $policy)
    {
        $request->validate(['version' => ['nullable', 'uuid']]);
        $policy->save($request->only(['enabled', 'timezone', 'windows']) + [
            'offline_policy' => $policy->get()['offline_policy'],
        ], $request->user()->id, $request->input('version') ?? '');

        return redirect()->route('canteen.meals.settings')->with('canteen_meal_status', 'Meal windows saved. All times use the selected institution timezone.');
    }

    public function assignments(Request $request, CanteenMealLanes $lanes)
    {
        $data = $request->validate(['assignments' => ['required', 'array'], 'assignments.*' => ['required', 'uuid'],
            'device_labels' => ['nullable', 'array'], 'device_labels.*' => ['required', 'string', 'max:80']]);
        $lanes->assign($data['assignments'], $data['device_labels'] ?? [], $request->user()->id);

        return redirect()->route('canteen.meals.settings')->with('canteen_meal_status', 'Serving lanes saved. An already-prepared day keeps its original assignments.');
    }

    public function activity(Request $request, CanteenMealPolicy $policy)
    {
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $date = $data['date'] ?? now($policy->get()['timezone'])->toDateString();
        $prefix = $policy->prefix();
        $counts = DB::table('canteen_meal_redemptions')->where('institution_prefix', $prefix)->where('service_date', $date)
            ->selectRaw('meal, COUNT(*) as total')->groupBy('meal')->pluck('total', 'meal');
        $attempts = DB::table('canteen_meal_attempts')->where('institution_prefix', $prefix)
            ->where('response->redemption->service_date', $date)->orderByDesc('created_at')->paginate(50)->withQueryString();
        $rejections = DB::table('canteen_meal_sync_rejections')->where('institution_prefix', $prefix)->where('event->service_date', $date)->orderByDesc('created_at')->limit(50)->get();

        return view('canteen.meal-activity', compact('date', 'counts', 'attempts', 'rejections'));
    }
}
