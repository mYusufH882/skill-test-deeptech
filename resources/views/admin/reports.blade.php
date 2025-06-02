<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Overall Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Approved Leaves ({{ date('Y') }})</p>
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
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2L3 7v11a2 2 0 002 2h4a2 2 0 002-2v-4h2v4a2 2 0 002 2h4a2 2 0 002-2V7l-7-5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Pending Requests</p>
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
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total Leave Days</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $leave_statistics['total_leave_days'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Leave Report -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Employee Leave Summary ({{ date('Y') }})</h3>
                        <p class="mt-1 text-sm text-gray-600">Detailed breakdown of leave usage by employee.</p>
                    </div>

                    @if($employees_with_leaves->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Employee
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days Used
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Remaining
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Usage %
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Requests
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Leave
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($employees_with_leaves as $employee)
                                        @php
                                            $daysUsed = $employee->getTotalLeaveDaysThisYear();
                                            $remaining = $employee->getRemainingLeaveDays();
                                            $usagePercent = ($daysUsed / 12) * 100;
                                            $lastLeave = $employee->leaves()->latest()->first();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-green-300 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-green-800">
                                                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $employee->full_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            ID: {{ $employee->id }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $employee->user->email }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->phone }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $daysUsed }}/12</div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                    <div class="h-2 rounded-full {{ $usagePercent > 80 ? 'bg-red-500' : ($usagePercent > 60 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                                         style="width: {{ $usagePercent }}%"></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium {{ $remaining <= 2 ? 'text-red-600' : ($remaining <= 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                                    {{ $remaining }} days
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $usagePercent > 80 ? 'bg-red-100 text-red-800' : ($usagePercent > 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                    {{ number_format($usagePercent, 1) }}%
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $employee->leaves->count() }}
                                                @if($employee->leaves()->pending()->count() > 0)
                                                    <span class="text-yellow-600">({{ $employee->leaves()->pending()->count() }} pending)</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($lastLeave)
                                                    {{ $lastLeave->start_date->format('M d, Y') }}
                                                    <div class="text-xs text-gray-500">{{ $lastLeave->status }}</div>
                                                @else
                                                    <span class="text-gray-400">Never</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
                            <p class="mt-1 text-sm text-gray-500">No employee data available for reporting.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- High Usage Employees -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">High Leave Usage (>75%)</h3>
                        @php
                            $highUsageEmployees = $employees_with_leaves->filter(function($employee) {
                                return ($employee->getTotalLeaveDaysThisYear() / 12) > 0.75;
                            });
                        @endphp
                        
                        @if($highUsageEmployees->count() > 0)
                            <div class="space-y-3">
                                @foreach($highUsageEmployees as $employee)
                                    @php
                                        $usagePercent = ($employee->getTotalLeaveDaysThisYear() / 12) * 100;
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $employee->getTotalLeaveDaysThisYear() }}/12 days used</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ number_format($usagePercent, 1) }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No employees with high leave usage.</p>
                        @endif
                    </div>
                </div>

                <!-- Low Usage Employees -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Low Leave Usage (<25%)</h3>
                        @php
                            $lowUsageEmployees = $employees_with_leaves->filter(function($employee) {
                                return ($employee->getTotalLeaveDaysThisYear() / 12) < 0.25;
                            });
                        @endphp
                        
                        @if($lowUsageEmployees->count() > 0)
                            <div class="space-y-3">
                                @foreach($lowUsageEmployees as $employee)
                                    @php
                                        $usagePercent = ($employee->getTotalLeaveDaysThisYear() / 12) * 100;
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $employee->getTotalLeaveDaysThisYear() }}/12 days used</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ number_format($usagePercent, 1) }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No employees with low leave usage.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.leaves.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Review Pending Leaves
                    </a>
                    <a href="{{ route('admin.employees.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Manage Employees
                    </a>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>