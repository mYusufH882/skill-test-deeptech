<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Request Details') }}
            </h2>
            <div class="flex space-x-2">
                @if($leave->isPending())
                    <a href="{{ route('employee.leaves.edit', $leave) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                        Edit Request
                    </a>
                @endif
                <a href="{{ route('employee.leaves.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to My Leaves
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-8 p-6 rounded-lg 
                @if($leave->status === 'pending') bg-yellow-50 border border-yellow-200
                @elseif($leave->status === 'approved') bg-green-50 border border-green-200
                @else bg-red-50 border border-red-200 @endif">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($leave->status === 'pending')
                            <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11a2 2 0 002 2h4a2 2 0 002-2v-4h2v4a2 2 0 002 2h4a2 2 0 002-2V7l-7-5z"/>
                            </svg>
                        @elseif($leave->status === 'approved')
                            <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="h-8 w-8 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium 
                            @if($leave->status === 'pending') text-yellow-800
                            @elseif($leave->status === 'approved') text-green-800
                            @else text-red-800 @endif">
                            Leave Request {{ ucfirst($leave->status) }}
                        </h3>
                        <p class="text-sm 
                            @if($leave->status === 'pending') text-yellow-700
                            @elseif($leave->status === 'approved') text-green-700
                            @else text-red-700 @endif">
                            @if($leave->status === 'pending')
                                Your leave request is pending admin approval.
                            @elseif($leave->status === 'approved')
                                Your leave request has been approved by {{ $leave->approver->name }}.
                            @else
                                Your leave request has been rejected by {{ $leave->approver->name }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Leave Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Leave Period -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Leave Period</h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-blue-700">Start Date:</span>
                                    <span class="text-sm font-semibold text-blue-900">{{ $leave->start_date->format('l, F d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-blue-700">End Date:</span>
                                    <span class="text-sm font-semibold text-blue-900">{{ $leave->end_date->format('l, F d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-blue-700">Total Days:</span>
                                    <span class="text-lg font-bold text-blue-900">{{ $leave->total_days }} {{ $leave->total_days == 1 ? 'day' : 'days' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Request Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">Request Information</h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-gray-700">Submitted:</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $leave->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-700">Status:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2
                                        @if($leave->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($leave->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </div>
                                @if($leave->status !== 'pending')
                                    <div>
                                        <span class="text-sm text-gray-700">Processed:</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $leave->approved_at->format('M d, Y \a\t g:i A') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-700">By:</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $leave->approver->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-8">
                        <h4 class="text-sm font-medium text-gray-800 mb-2">Reason for Leave</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-900">{{ $leave->reason }}</p>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    @if($leave->admin_notes)
                        <div class="mb-8">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">Admin Notes</h4>
                            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                                <p class="text-yellow-800">{{ $leave->admin_notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Leave Statistics -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-green-800 mb-2">Your Leave Balance ({{ date('Y') }})</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-green-700">Total Allowance:</span>
                                <span class="text-lg font-bold text-green-900 block">12 days</span>
                            </div>
                            <div>
                                <span class="text-sm text-green-700">Days Used:</span>
                                <span class="text-lg font-bold text-green-900 block">{{ $leave->employee->getTotalLeaveDaysThisYear() }} days</span>
                            </div>
                            <div>
                                <span class="text-sm text-green-700">Remaining:</span>
                                <span class="text-lg font-bold text-green-900 block">{{ $leave->employee->getRemainingLeaveDays() }} days</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-green-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full" style="width: {{ ($leave->employee->getTotalLeaveDaysThisYear() / 12) * 100 }}%"></div>
                            </div>
                            <p class="text-xs text-green-700 mt-1">{{ number_format(($leave->employee->getTotalLeaveDaysThisYear() / 12) * 100, 1) }}% of annual leave used</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('employee.leaves.index') }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Back to My Leaves
                        </a>
                        @if($leave->isPending())
                            <a href="{{ route('employee.leaves.edit', $leave) }}" 
                               class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                Edit Request
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>