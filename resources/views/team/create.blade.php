@extends('main')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Add New Employee</h2>
                <p class="text-sm text-slate-400 mt-1">Create a new team member and sync with GoHighLevel.</p>
            </div>
            <a href="{{ route('team.index') }}" class="text-slate-400 hover:text-white text-sm font-medium">← Back to Team</a>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
            <form action="{{ route('team.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500"
                            placeholder="e.g. John Doe">
                        @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500"
                            placeholder="e.g. john@sofloshine.com">
                        @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500"
                            placeholder="e.g. (786) 555-0132">
                        @error('phone') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase">Role</label>
                        <select name="role"
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                            <option value="account-user">Account User</option>
                            <option value="admin">Admin</option>
                            <option value="agency">Agency</option>
                        </select>
                        @error('role') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-2 uppercase">Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500"
                            placeholder="••••••••">
                        @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 border-t border-slate-800 pt-6">
                    <a href="{{ route('team.index') }}"
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-medium transition">Cancel</a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/20 transition">Save & Sync with GHL</button>
                </div>
            </form>
        </div>
    </div>
@endsection