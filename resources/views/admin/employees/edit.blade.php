<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Employee: ') . $employee->full_name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.employees.show', $employee) }}" 
                   class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    View Employee
                </a>
                <a href="{{ route('admin.employees.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Update Employee Information</h3>
                        <p class="mt-1 text-sm text-gray-600">Modify the employee details below. Leave password fields blank to keep current password.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Account Information -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-4">Login Account Details</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Display Name</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $employee->user->name) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('name') border-red-300 @enderror"
                                           placeholder="e.g. John Employee">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email', $employee->user->email) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('email') border-red-300 @enderror"
                                           placeholder="employee@company.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-800 mb-2">Password Update (Optional)</h4>
                            <p class="text-sm text-yellow-700 mb-4">Leave these fields blank if you don't want to change the password.</p>
                            
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('password') border-red-300 @enderror"
                                           placeholder="Leave blank to keep current">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" 
                                       name="first_name" 
                                       id="first_name" 
                                       value="{{ old('first_name', $employee->first_name) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('first_name') border-red-300 @enderror"
                                       placeholder="John">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" 
                                       name="last_name" 
                                       id="last_name" 
                                       value="{{ old('last_name', $employee->last_name) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('last_name') border-red-300 @enderror"
                                       placeholder="Doe">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $employee->phone) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('phone') border-red-300 @enderror"
                                       placeholder="+62 812 3456 7890">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                <select name="gender" 
                                        id="gender"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('gender') border-red-300 @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" 
                                      id="address" 
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('address') border-red-300 @enderror"
                                      placeholder="Enter full address...">{{ old('address', $employee->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Leave Statistics -->
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-green-800 mb-2">Current Leave Status</h4>
                            <div class="grid grid-cols-3 gap-4 text-sm text-green-700">
                                <div>
                                    <span class="font-medium">Used This Year:</span><br>
                                    <span class="text-lg font-bold">{{ $employee->getTotalLeaveDaysThisYear() }}/12 days</span>
                                </div>
                                <div>
                                    <span class="font-medium">Remaining:</span><br>
                                    <span class="text-lg font-bold">{{ $employee->getRemainingLeaveDays() }} days</span>
                                </div>
                                <div>
                                    <span class="font-medium">Total Requests:</span><br>
                                    <span class="text-lg font-bold">{{ $employee->leaves()->count() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">Account Information</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Created:</span> {{ $employee->created_at->format('M d, Y \a\t g:i A') }}
                                </div>
                                <div>
                                    <span class="font-medium">Last Updated:</span> {{ $employee->updated_at->format('M d, Y \a\t g:i A') }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.employees.index') }}" 
                               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                Update Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>