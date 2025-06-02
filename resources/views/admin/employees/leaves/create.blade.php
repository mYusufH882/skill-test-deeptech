<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Request New Leave') }}
            </h2>
            <a href="{{ route('employee.leaves.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                Back to My Leaves
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Leave Balance Summary -->
            <div class="bg-gradient-to-r from-green-400 to-blue-500 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-white">
                    <h3 class="text-lg font-semibold mb-4">Your Leave Balance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-green-100">Days Used This Year</p>
                            <p class="text-3xl font-bold">{{ 12 - $stats['remaining_days'] }}/12</p>
                            <div class="w-full bg-green-200 rounded-full h-2 mt-2">
                                <div class="bg-white h-2 rounded-full" style="width: {{ ((12 - $stats['remaining_days']) / 12) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-blue-100">Remaining Days</p>
                            <p class="text-3xl font-bold">{{ $stats['remaining_days'] }}</p>
                            <p class="text-sm text-blue-100 mt-2">Available for new requests</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Leave Request Form</h3>
                        <p class="mt-1 text-sm text-gray-600">Fill out the details for your leave request. Please ensure all information is accurate.</p>
                    </div>

                    <form method="POST" action="{{ route('employee.leaves.store') }}" class="space-y-6">
                        @csrf

                        <!-- Leave Period -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date" 
                                       value="{{ old('start_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('start_date') border-red-300 @enderror"
                                       onchange="calculateDays()">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end_date" 
                                       value="{{ old('end_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('end_date') border-red-300 @enderror"
                                       onchange="calculateDays()">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Days Calculation Display -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Leave Duration</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Total days requested: <span id="total-days" class="font-semibold">0</span></p>
                                        <p>Days remaining after this request: <span id="remaining-after" class="font-semibold">{{ $stats['remaining_days'] }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Leave</label>
                            <textarea name="reason" 
                                      id="reason" 
                                      rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('reason') border-red-300 @enderror"
                                      placeholder="Please provide a detailed reason for your leave request...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Be specific about the reason for your leave to help with the approval process.</p>
                        </div>

                        <!-- Leave Policy Reminder -->
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Leave Policy Reminders</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>You can take maximum 12 leave days per year</li>
                                            <li>Only 1 leave request per month is allowed</li>
                                            <li>Leave requests must be submitted in advance</li>
                                            <li>All leave requests require admin approval</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('employee.leaves.index') }}" 
                               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                Cancel
                            </a>
                            <button type="submit" 
                                    id="submit-btn"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                Submit Leave Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const totalDaysSpan = document.getElementById('total-days');
            const remainingAfterSpan = document.getElementById('remaining-after');
            const submitBtn = document.getElementById('submit-btn');
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end >= start) {
                    const timeDiff = end.getTime() - start.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                    
                    totalDaysSpan.textContent = daysDiff;
                    
                    const remaining = {{ $stats['remaining_days'] }} - daysDiff;
                    remainingAfterSpan.textContent = remaining;
                    
                    // Update colors based on remaining days
                    if (remaining < 0) {
                        remainingAfterSpan.className = 'font-semibold text-red-600';
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        remainingAfterSpan.className = 'font-semibold text-green-600';
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    totalDaysSpan.textContent = '0';
                    remainingAfterSpan.textContent = '{{ $stats['remaining_days'] }}';
                }
            }
        }

        // Set minimum end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</x-app-layout>