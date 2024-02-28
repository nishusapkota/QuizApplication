<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\RegisteredStudentExport;
use App\Http\Requests\StudentFilterRequest;
use App\Http\Repositories\StudentRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    /**
     * @var StudentRepository
     */
    private StudentRepository $studentRepository;

    /**
     * @param  StudentRepository  $studentRepository
     */
    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    /**
     * @param StudentFilterRequest  $request T
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(StudentFilterRequest $request)
    {
        $params = $request->validated();

        $export = $request->has("export");
        $students = $this->studentRepository->getFilteredStudent($params,$export);

        if($export){
            return $this->studentRepository->exportStudents($students);
        }

        return UserResource::collection($students);
    }

    /**
     * @param int $id
     * @return UserResource
     */
    public function show(int $id): UserResource
    {
        $student = $this->studentRepository->show($id);
        return new UserResource($student);
    }
}
