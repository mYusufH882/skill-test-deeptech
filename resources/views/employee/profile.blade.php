<?php
// =============================================================================
// EMPLOYEE PROFILE VIEW
// File: resources/views/employee/profile.blade.php
// =============================================================================
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Profile Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                        <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            Employee
                        </span>
                    </div>

                    <form method="POST" action="{{ route('employee.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="{{ old('first_name', $employee->first_name) }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('first_name') border-red-300 @enderror"
                                    required
                                >
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="{{ old('last_name', $employee->last_name) }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('last_name') border-red-300 @enderror"
                                    required
                                >
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email (Read Only) -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    value="{{ $employee->user->email }}"
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                                    disabled
                                >
                                <p class="mt-1 text-sm text-gray-500">Email cannot be changed. Contact HR for email updates.</p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone', $employee->phone) }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-300 @enderror"
                                    placeholder="+62 812 3456 7890"
                                    required
                                >
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gender (Read Only) -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                    Gender
                                </label>
                                <input 
                                    type="text" 
                                    id="gender" 
                                    value="{{ ucfirst($employee->gender) }}"
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                                    disabled
                                >
                                <p class="mt-1 text-sm text-gray-500">Gender cannot be changed. Contact HR for updates.</p>
                            </div>

                            <!-- Employee ID (Read Only) -->
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Employee ID
                                </label>
                                <input 
                                    type="text" 
                                    id="employee_id" 
                                    value="EMP{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}"
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                                    disabled
                                >
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-6">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="address" 
                                name="address" 
                                rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-300 @enderror"
                                placeholder="Enter your full address"
                                required
                            >{{ old('address', $employee->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex items-center justify-end">
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Account Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name
                            </label>
                            <div class="p-3 bg-gray-50 rounded-md border">
                                <p class="text-sm text-gray-900">{{ $employee->full_name }}</p>
                            </div>
                        </div>

                        <!-- Account Created -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Account Created
                            </label>
                            <div class="p-3 bg-gray-50 rounded-md border">
                                <p class="text-sm text-gray-900">{{ $employee->user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Profile Last Updated
                            </label>
                            <div class="p-3 bg-gray-50 rounded-md border">
                                <p class="text-sm text-gray-900">{{ $employee->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>

                        <!-- User Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Account Type
                            </label>
                            <div class="p-3 bg-gray-50 rounded-md border">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst($employee->user->user_type) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Leave Statistics ({{ date('Y') }})</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Annual Allowance -->
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">12</div>
                            <div class="text-sm text-gray-600">Annual Allowance</div>
                        </div>

                        <!-- Days Used -->
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-2xl font-bold text-red-600">{{ $employee->getTotalLeaveDaysThisYear() }}</div>
                            <div class="text-sm text-gray-600">Days Used</div>
                        </div>

                        <!-- Days Remaining -->
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $employee->getRemainingLeaveDays() }}</div>
                            <div class="text-sm text-gray-600">Days Remaining</div>
                        </div>

                        <!-- Pending Requests -->
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $employee->leaves()->pending()->count() }}</div>
                            <div class="text-sm text-gray-600">Pending Requests</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Leave Usage Progress</span>
                            <span>{{ $employee->getTotalLeaveDaysThisYear() }}/12 days ({{ round(($employee->getTotalLeaveDaysThisYear() / 12) * 100) }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div 
                                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-300" 
                                style="width: {{ min(($employee->getTotalLeaveDaysThisYear() / 12) * 100, 100) }}%"
                            ></div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            @if($employee->getRemainingLeaveDays() > 0)
                                <span class="text-green-600">✓ You have {{ $employee->getRemainingLeaveDays() }} days remaining</span>
                            @elseif($employee->getRemainingLeaveDays() == 0)
                                <span class="text-orange-600">⚠ You have used all your annual leave allowance</span>
                            @else
                                <span class="text-red-600">⚠ You have exceeded your annual leave allowance</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('employee.leaves.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            View My Leaves
                        </a>
                        
                        @if($employee->getRemainingLeaveDays() > 0)
                            <a href="{{ route('employee.leaves.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Request Leave
                            </a>
                        @endif
                        
                        <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>