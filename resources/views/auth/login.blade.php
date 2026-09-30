<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-semibold text-[#13224B]">Connexion</h1>
        <p class="text-sm text-[#13224B]/60 mt-1">Accédez à votre espace Recrutement.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-[#13224B] mb-1.5">
                Email
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="vous@exemple.com"
                class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
            />
            @error('email')
                <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-2">
            <label for="password" class="block text-sm font-medium text-[#13224B] mb-1.5">
                Mot de passe
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
            />
            @error('password')
                <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="text-right mb-6">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="w-full bg-[#1D4ED8] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#1741B8] transition"
        >
            Se connecter
        </button>

        <p class="text-center text-sm text-[#13224B]/60 mt-6">
            Pas encore inscrit ?
            <a href="{{ route('register') }}" class="font-medium text-[#1D4ED8] hover:underline">
                Créer un compte
            </a>
        </p>
    </form>
</x-guest-layout>