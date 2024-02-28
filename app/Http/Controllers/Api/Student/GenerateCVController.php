<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;
use Illuminate\Support\Str;

class GenerateCVController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $user = auth()->user()->load('profile');

        if(!$user->profile){
            return response()->json(['message' => "Please create profile to generate CV"],503);
        }

        $cv_name = str_replace(" ", "_", $user->name) . "_CV.pdf";

        $avatar_path = Str::after($user->profile->avatar, '/app');

        $results = $user->results()
            ->where('passed', 1)
            ->select(['id', 'quiz_id'])
            ->with(['quiz:id,title,pass_percentage'])
            ->get();

        Pdf::view('cv.index', compact('user', 'results', 'avatar_path'))
            ->format(Format::A4)
            ->save(storage_path("app/cv/{$cv_name}"));

        return response()->json(['cv' => asset("cv/{$cv_name}")]);
    }
}
