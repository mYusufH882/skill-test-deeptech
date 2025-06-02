<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Leave Request Details') }}
            </h2>
            <a href="{{ route('employee.leaves.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Leaves
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Leave Request Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header with Status -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Leave Request #{{ $leave->id }}</h3>
                            <p class="text-sm text-gray-500">Submitted on {{ $leave->created_at->format('M d, Y \a\t H:i') }}</p>
                        </div>
                        <div>
                            @if($leave->status === 'pending')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending Approval
                                </span>
                            @elseif($leave->status === 'approved')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Start Date</h4>
                            <p class="mt-1 text-lg text-gray-900">{{ $leave->start_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $leave->start_date->format('l') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">End Date</h4>
                            <p class="mt-1 text-lg text-gray-900">{{ $leave->end_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $leave->end_date->format('l') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Duration</h4>
                            <p class="mt-1 text-lg text-gray-900">{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Request Date</h4>
                            <p class="mt-1 text-lg text-gray-900">{{ $leave->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Reason</h4>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-900">{{ $leave->reason }}</p>
                        </div>
                    </div>

                    <!-- Admin Response (if any) -->
                    @if($leave->approved_by || $leave->admin_notes)
                    <div class="border-t pt-6">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Admin Response</h4>
                        
                        @if($leave->approved_by)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                Reviewed by: <span class="font-medium">{{ $leave->approver->name }}</span>
                                @if($leave->approved_at)
                                    on {{ $leave->approved_at->format('M d, Y \a\t H:i') }}
                                @endif
                            </p>
                        </div>
                        @endif

                        @if($leave->admin_notes)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-900">{{ $leave->admin_notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Actions -->
                    @if($leave->isPending())
                    <div class="border-t pt-6">
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('employee.leaves.edit', $leave) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Request
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>