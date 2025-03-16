@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">User Profile</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="text-center mb-3">
        <img src="{{ $user->profile && $user->profile->profile_picture 
            ? asset('storage/' . $user->profile->profile_picture) 
            : asset('images/default-profile.jpg') }}" 
            class="rounded-circle" width="150" height="150" alt="Profile Picture">
    </div>

    @if(session('edit_mode') || session('password_mode'))
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
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio">{{ old('bio', $user->profile->bio) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Enable Two-Factor Authentication (2FA)</label>
                    <input type="hidden" name="two_factor_enabled" value="0">
                    <input type="checkbox" name="two_factor_enabled" id="two_factor_enabled" value="1"
                     {{ $user->two_factor_enabled ? 'checked' : '' }}>
                    <label for="two_factor_enabled">Enable 2FA</label>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
                <a href="{{ route('user.profile') }}" class="btn btn-secondary mt-3">Cancel</a>
                <button type="button" class="btn btn-info mt-3" id="toggle-password-form">Change Password</button>
            </div>
        </form>

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
        <div class="profile-details">
            <p><strong>Username:</strong> {{ $user->username }}</p>
            <p><strong>Name:</strong> {{ $user->fname }} {{ $user->lname }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Gender:</strong> {{ ucfirst($user->profile->gender ?? 'Not specified') }}</p>
            <p><strong>Bio:</strong> {{ $user->profile->bio ?? 'No bio yet.' }}</p>
            <p><strong>Two-Factor Authentication:</strong> {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</p>
        </div>

        <a href="{{ route('user.profile.edit') }}" class="btn btn-warning mt-3">Edit Profile</a>

        <div id="profile-buttons">
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>

            <form action="{{ route('user.profile.destroy') }}" method="POST">
    @csrf

    @method('DELETE')
    <br><button type="submit"  class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account?');">
        Delete Account
    </button>
</form>

        </div>
    @endif
</div>

<script>
    document.getElementById('toggle-password-form').addEventListener('click', function() {
        document.getElementById('profile-form').style.display = 'none';
        document.getElementById('password-form').style.display = 'block';
    });
</script>
@endsection
