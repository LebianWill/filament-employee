<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Country;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $bf  = Country::where('country_code', 'BF')->withCount('employees')->first();
        $uk  = Country::where('country_code', 'UK')->withCount('employees')->first();
        return [
            Stat::make('All Employees', Employee::all()->count()),
            Stat::make($bf->name . ' Employees', $bf->employees()->count()),
            Stat::make($uk->name . ' Employees', $uk->employees()->count()),
        ];
    }
}
