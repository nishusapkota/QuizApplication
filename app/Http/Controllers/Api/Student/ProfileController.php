<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileStoreRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Repositories\ProfileRepository;

class ProfileController extends Controller
{
    private ProfileRepository $profileRepository;

    /**
     * @param  ProfileRepository $profileRepository
     */
    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    /**
     * @param ProfileStoreRequest $request
     * @return JsonResource
     */
    public function store(ProfileStoreRequest $request): JsonResource
    {
        $data = $request->validated();
        $profile = $this->profileRepository->store($data);
        return (new ProfileResource($profile))->additional(ResponseHelper::stored());
    }

    /**
     * @param Profile $profile
     * @param ProfileUpdateRequest $request
     * @return JsonResource
     */
    public function update(Profile $profile, ProfileUpdateRequest $request): JsonResource
    {
        $data = $request->validated();
        $profile = $this->profileRepository->update($profile, $data);
        return (new ProfileResource($profile))->additional(ResponseHelper::updated($profile));
    }
}
