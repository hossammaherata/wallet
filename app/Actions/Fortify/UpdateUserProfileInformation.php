<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            // 'user_name' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ])->validateWithBag('updateProfileInformation');

        $updateData = [
            'name' => $input['name'],
            'email' => $input['email'],
        ];

        // Handle profile_photo_path upload using Storage facade (same mechanism as admin/store profile)
        if (isset($input['photo']) && is_object($input['photo'])) {
            // Delete old photo if exists
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $photo = $input['photo'];
            $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('profile-photos', $photoName, 'public');
            $updateData['profile_photo_path'] = $photoPath;
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $updateData);
        } else {
            $user->forceFill($updateData)->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $input['email_verified_at'] = null;
        $user->forceFill($input)->save();
        $user->sendEmailVerificationNotification();
    }
}
