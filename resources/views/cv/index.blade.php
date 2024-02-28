<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} CV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>

<body>
    <section class="p-8">
        <header class="flex justify-between items-center pb-8 border-b-2 border-gray-300">
            <div class="flex items-center w-6/12">
                <img src="{{ public_path($avatar_path) }}" alt="user-avatar"
                    class="w-40 h-40 rounded-full object-cover">
                <div class="my-4 ml-8">
                    <h1 class="text-4xl font-medium">{{ $user->first_name }} <br> {{ $user->last_name }}</h1>
                </div>
            </div>
            <div>
                <ul>
                    <li class="font-light text-lg flex items-center">
                        <span class="mr-2">

                            <img src="{{ public_path('/icons/Email.png') }}" alt="mail-icon" class="w-6 h-6">
                        </span>
                        {{ $user->email }}
                    </li>
                </ul>
            </div>
        </header>
        <main class="flex justify-between mt-8">
            <section class="w-4/12 mr-10">
                <div class="pb-6 border-b-2 border-gray-300">
                    <h2 class="font-medium text-2xl">Career</h2>
                    <p class="mt-2 text-lg font-light">
                        {{ $user->profile->career }}
                    </p>
                </div>
                <div class="mt-6 pb-6 border-b-2 border-gray-300">
                    <h2 class="text-2xl font-medium">Education</h2>
                    <p class="mt-2 font-light text-lg">
                        {{ $user->profile->education }}
                    </p>
                </div>

                <div class="mt-6">
                    <h2 class="text-2xl font-medium">Skills</h2>
                    <div class="skills-container mt-2 flex flex-row flex-wrap gap-1">
                        @foreach ($user->profile->skills as $skill)
                            <span
                                class="bg-blue-100 text-blue-800 text-lg font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
            <aside class="w-8/12 pl-10 border-l-2 border-gray-300">
                <h1 class="font-medium text-2xl">Experience</h1>
                <div class="experience-card border-b-2 border-gray-300 pb-6 mb-6">
                    <p class="mt-2 text-lg font-light">
                        {{ $user->profile->experience }}
                    </p>
                </div>
                <div id="skills">
                    <h1 class="text-2xl font-medium">Passed Quizzes</h1>
                    <ul class="mt-2">
                        @foreach ($results as $result)
                            <li class="list-disc ml-5 my-2 text-xl font-light">{{ $result->quiz->title }}</li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </main>
    </section>
</body>

</html>
