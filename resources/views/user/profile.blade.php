@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <!-- Profile Card Wrapper -->
        <div class="card shadow-sm p-4" style="background-color: #FFFFFF; border-radius: 10px;">
            <h1 class="user-profile-title mb-4" >User Profile</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <!-- Left Container (Profile Picture and Username) -->
                <div class="col-md-4 text-center">
                    <div class="profile-left-container">
                        <img src="{{ $user->profile && $user->profile->profile_picture 
                            ? asset('storage/' . $user->profile->profile_picture) 
                            : asset('images/default-profile.jpg') }}" 
                            class="rounded-circle" width="150" height="150" alt="Profile Picture">

                        <h4 class="mt-3">{{ $user->username }}</h4>
                    </div>
                </div>

                <!-- Right Container (Name, Email, Gender, Bio, 2FA) -->
                <div class="col-md-8">
                    <div class="profile-right-container border p-3" style="border-radius: 10px; border: 2px solid #5D6E54;">
                        <p><strong>Name:</strong> {{ $user->fname }} {{ $user->lname }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Gender:</strong> {{ ucfirst($user->profile->gender ?? 'Not specified') }}</p>
                        <p><strong>Two-Factor Authentication:</strong> {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</p>
                    </div>
                </div>
            </div>

            @if(session('edit_mode') || session('password_mode'))
                <!-- Profile Edit Form -->
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form"
                      style="{{ session('password_mode') ? 'display: none;' : '' }}">
                    @csrf
                    @method('PUT')

                    <div id="profile-fields">
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <input type="checkbox" id="remove_profile_picture" name="remove_profile_picture" value="1">
                            <label for="remove_profile_picture">Remove profile picture</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
                            @error('username') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="fname" value="{{ old('fname', $user->fname) }}" required>
                            @error('fname') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="lname" value="{{ old('lname', $user->lname) }}" required>
                            @error('lname') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-control" name="gender">
                                <option value="" {{ is_null($user->profile->gender) ? 'selected' : '' }}>Not specified</option>
                                <option value="male" {{ $user->profile->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $user->profile->gender === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $user->profile->gender === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Enable Two-Factor Authentication (2FA)</label>
                            <input type="hidden" name="two_factor_enabled" value="0">
                            <input type="checkbox" name="two_factor_enabled" id="two_factor_enabled" value="1"
                             {{ $user->two_factor_enabled ? 'checked' : '' }}>
                            <label for="two_factor_enabled">Enable 2FA</label>
                        </div>

                        <button type="submit" class="btn green-button mt-3">Update Profile</button>
                        <a href="{{ route('user.profile') }}" class="btn gray-button mt-3">Cancel</a>
                        <button type="button" class="btn brown-button mt-3" id="toggle-password-form">Change Password</button>
                    </div>
                </form>

                <!-- Change Password Form -->
                <form action="{{ route('user.profile.password.update') }}" method="POST" id="password-form"
                      style="{{ session('password_mode') ? '' : 'display: none;' }}">
                    @csrf
                    @method('PUT')

                    <h4 class="mt-3">Change Password</h4>

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password">
                        @error('current_password') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password">
                        @error('password') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="password_confirmation">
                        @error('password_confirmation') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                    <a href="{{ route('user.profile') }}" class="btn btn-secondary">Cancel</a>
                </form>
            @else

                <!-- Updated Edit Profile Button -->
                <a href="{{ route('user.profile.edit') }}" class="btn btn-edit-profile green-button mt-3">Edit Profile</a>

                <div id="profile-buttons" class="button-container">
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-danger red-button">Logout</button>
                    </form>

                    <form action="{{ route('user.profile.destroy') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit"  class="btn btn-danger red-button" onclick="return confirm('Are you sure you want to delete your account?');">
                            Delete Account
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('toggle-password-form').addEventListener('click', function() {
            document.getElementById('profile-form').style.display = 'none';
            document.getElementById('password-form').style.display = 'block';
        });
    </script>
 
@endsection
