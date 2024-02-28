<?php

namespace App\Http\Repositories;

use App\Models\Profile;
use Illuminate\Support\Facades\Storage;

class ProfileRepository
{
    /**
     * @param array $data
     * @return Profile
     */
    public function store(array $data): Profile
    {
        if(isset($data['avatar'])){
            $name = date('ymd') . time() . '.' . $data['avatar']->extension();
            $data['avatar'] = $data['avatar']->storeAs('images/profiles', $name);
        }
        return Profile::create($data);
    }

    /**
     * @param Profile $profile
     * @param array $data
     * @return Profile
     */
    public function update(Profile $profile,array $data): Profile
    {
        if (isset($data['avatar'])) {
            Storage::delete($profile->avatar);
            $name = date('ymd') . time() . '.' . $data['avatar']->extension();
            $data['avatar'] = $data['avatar']->storeAs('images/profiles', $name);
        }
        $profile->update($data);

        return $profile;
    }
}
