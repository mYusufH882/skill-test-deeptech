<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Leave Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Scope Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                @if(auth()->user()->isSuperAdmin())
                                    System-wide Leave Report
                                @else
                                    My Employees Leave Report
                                @endif
                            </h3>
                            <p class="text-gray-600">
                                @if(auth()->user()->isSuperAdmin())
                                    Comprehensive leave statistics for all employees in the system.
                                @else
                                    Leave statistics for employees you have created and manage.
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Report Period</p>
                            <p class="text-lg font-medium text-gray-900">{{ date('Y') }}</p>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ auth()->user()->isSuperAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ auth()->user()->isSuperAdmin() ? 'SuperAdmin View' : 'Admin View' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Level Notice for Regular Admin -->
            @if(auth()->user()->isRegularAdmin())
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">Report Scope</h3>
                            <p class="text-sm text-blue-700 mt-1">
                                This report shows data only for employees you created. To view system-wide reports, contact your SuperAdmin.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Leave Statistics Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Approved Leaves</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $leave_statistics['total_approved_leaves'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Leaves</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $leave_statistics['total_pending_leaves'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Leave Days</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $leave_statistics['total_leave_days'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Leave Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            Employee Leave Details ({{ date('Y') }})
                        </h3>
                        <div class="text-sm text-gray-500">
                            Showing {{ $employees_with_leaves->count() }} 
                            {{ Str::plural('employee', $employees_with_leaves->count()) }}
                        </div>
                    </div>

                    @if($employees_with_leaves->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Employee
                                        </th>
                                        @if(auth()->user()->isSuperAdmin())
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created By
                                        </th>
                                        @endif
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Leaves
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days Used
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days Remaining
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Usage %
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($employees_with_leaves as $employee)
                                    @php
                                        $totalUsed = $employee->getTotalLeaveDaysThisYear();
                                        $remaining = $employee->getRemainingLeaveDays();
                                        $usagePercent = round(($totalUsed / 12) * 100);
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $employee->full_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $employee->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if(auth()->user()->isSuperAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($employee->creator)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-6 w-6">
                                                        <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-indigo-600">
                                                                {{ substr($employee->creator->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-2">
                                                        <div class="text-sm text-gray-900">{{ $employee->creator->name }}</div>
                                                        <div class="text-xs text-gray-500">
                                                            {{ $employee->creator->isSuperAdmin() ? 'SuperAdmin' : 'Admin' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">System</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $employee->leaves->where('status', 'approved')->count() }} approved
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $totalUsed }} / 12 days
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium {{ $remaining <= 2 ? 'text-red-600' : ($remaining <= 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ $remaining }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="h-2 rounded-full {{ $usagePercent >= 100 ? 'bg-red-500' : ($usagePercent >= 80 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                                         style="width: {{ min($usagePercent, 100) }}%"></div>
                                                </div>
                                                <span class="text-sm font-medium {{ $usagePercent >= 100 ? 'text-red-600' : ($usagePercent >= 80 ? 'text-yellow-600' : 'text-green-600') }}">
                                                    {{ $usagePercent }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($usagePercent >= 100)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Exceeded
                                                </span>
                                            @elseif($usagePercent >= 80)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    High Usage
                                                </span>
                                            @elseif($usagePercent >= 50)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Moderate
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Low Usage
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Footer -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <div class="text-sm text-gray-500">Total Employees</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ $employees_with_leaves->count() }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Average Usage</div>
                                    <div class="text-lg font-semibold text-gray-900">
                                        {{ $employees_with_leaves->count() > 0 ? round($employees_with_leaves->avg(function($emp) { return ($emp->getTotalLeaveDaysThisYear() / 12) * 100; })) : 0 }}%
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">High Usage (≥80%)</div>
                                    <div class="text-lg font-semibold text-red-600">
                                        {{ $employees_with_leaves->filter(function($emp) { return ($emp->getTotalLeaveDaysThisYear() / 12) * 100 >= 80; })->count() }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Low Usage (<50%)</div>
                                    <div class="text-lg font-semibold text-green-600">
                                        {{ $employees_with_leaves->filter(function($emp) { return ($emp->getTotalLeaveDaysThisYear() / 12) * 100 < 50; })->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(auth()->user()->isRegularAdmin())
                                    You haven't created any employees yet. Create employees to see their leave reports here.
                                @else
                                    No employees have been created in the system yet.
                                @endif
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Employee
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>