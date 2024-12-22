<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialisation du Système - MaBoussole CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">Initialisation du Système</h1>
            <p class="text-gray-400 mt-2">Création du compte Super Administrateur</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <form method="POST" action="{{ url('/system/initialize') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300">Nom complet</label>
                    <input type="text" name="name" id="name" required 
                           class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">Adresse email</label>
                    <input type="email" name="email" id="email" required 
                           class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">Mot de passe</label>
                    <input type="password" name="password" id="password" required 
                           class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                           class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        Initialiser le système
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
