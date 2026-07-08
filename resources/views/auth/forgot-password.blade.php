<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-extrabold text-blue-600">Recrutement</h1>
                <p class="text-gray-500 text-sm mt-1">Mot de passe oublié</p>
            </div>

            <div class="mb-4 text-sm text-gray-600">
                Indiquez-nous votre email et nous vous enverrons un lien de réinitialisation.
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email" type="email" name="email"
                        value="{{ old('email') }}" required autofocus
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition"
                    >
                        Envoyer le lien de réinitialisation
                    </button>
                </div>

                <p class="text-center text-sm text-gray-500 mt-4">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Retour à la connexion</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>