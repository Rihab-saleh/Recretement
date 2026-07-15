<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-blue-600">Recrutement</h1>
                <p class="text-gray-500 text-sm mt-1">Créer un compte — Rejoignez-nous dès maintenant</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input
                        id="nom" type="text" name="nom"
                        value="{{ old('nom') }}" required autofocus
                        autocomplete="given-name"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input
                        id="prenom" type="text" name="prenom"
                        value="{{ old('prenom') }}" required
                        autocomplete="family-name"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('prenom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email" type="email" name="email"
                        value="{{ old('email') }}" required
                        autocomplete="email"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-2">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input
                        id="password" type="password" name="password" required
                        autocomplete="new-password"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p class="text-gray-400 text-xs mt-1">
                        Min 8 caractères, une majuscule, une minuscule, un chiffre, un caractère spécial (@$!%*?&)
                    </p>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6">
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition"
                    >
                        S'inscrire
                    </button>
                </div>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Se connecter</a>
                </p>

            </form>
        </div>
    </div>

</x-guest-layout>