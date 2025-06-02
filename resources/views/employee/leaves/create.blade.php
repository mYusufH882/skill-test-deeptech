<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Request Leave') }}
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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Leave Statistics Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Leave Balance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['remaining_days'] }}</div>
                            <div class="text-sm text-gray-600">Days Remaining</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['used_days'] }}</div>
                            <div class="text-sm text-gray-600">Days Used</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Request Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('employee.leaves.store') }}">
                        @csrf

                        <!-- Reason -->
                        <div class="mb-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Leave <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="reason" 
                                name="reason" 
                                rows="4" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('reason') border-red-300 @enderror" 
                                placeholder="Please describe the reason for your leave request..."
                                required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    name="start_date" 
                                    value="{{ old('start_date') }}"
                                    min="{{ date('Y-m-d') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror" 
                                    required>
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    End Date <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="end_date" 
                                    name="end_date" 
                                    value="{{ old('end_date') }}"
                                    min="{{ date('Y-m-d') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 @enderror" 
                                    required>
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Leave Duration Display -->
                        <div id="duration-display" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">
                                    Duration: <span id="leave-days" class="font-bold text-blue-600">0</span> day(s)
                                </span>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-800 mb-2">Important Notes:</h4>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• You have {{ $stats['remaining_days'] }} leave days remaining this year</li>
                                <li>• Maximum of 1 leave request per month is allowed</li>
                                <li>• Leave requests must be submitted in advance</li>
                                <li>• Once submitted, you can edit pending requests only</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('employee.leaves.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Date Calculation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const durationDisplay = document.getElementById('duration-display');
            const leaveDaysSpan = document.getElementById('leave-days');

            function calculateDuration() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    
                    if (end >= start) {
                        const timeDiff = end.getTime() - start.getTime();
                        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                        
                        leaveDaysSpan.textContent = daysDiff;
                        durationDisplay.classList.remove('hidden');
                    } else {
                        durationDisplay.classList.add('hidden');
                    }
                } else {
                    durationDisplay.classList.add('hidden');
                }
            }

            // Update end date minimum when start date changes
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
                calculateDuration();
            });

            endDateInput.addEventListener('change', calculateDuration);
        });
    </script>
</x-app-layout>