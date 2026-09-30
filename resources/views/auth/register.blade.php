<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-semibold text-[#13224B]">Créer un compte</h1>
        <p class="text-sm text-[#13224B]/60 mt-1">Rejoignez la plateforme en tant que candidat.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label for="nom" class="block text-sm font-medium text-[#13224B] mb-1.5">Nom</label>
                <input
                    id="nom" type="text" name="nom"
                    value="{{ old('nom') }}" required autofocus
                    autocomplete="given-name"
                    class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
                />
                @error('nom') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="prenom" class="block text-sm font-medium text-[#13224B] mb-1.5">Prénom</label>
                <input
                    id="prenom" type="text" name="prenom"
                    value="{{ old('prenom') }}" required
                    autocomplete="family-name"
                    class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
                />
                @error('prenom') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-[#13224B] mb-1.5">Email</label>
            <input
                id="email" type="email" name="email"
                value="{{ old('email') }}" required
                autocomplete="email"
                placeholder="vous@exemple.com"
                class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
            />
            @error('email') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="mb-2">
            <label for="password" class="block text-sm font-medium text-[#13224B] mb-1.5">Mot de passe</label>
            <input
                id="password" type="password" name="password" required
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full border border-[#DCE6F5] rounded-lg px-4 py-2.5 text-sm text-[#13224B] placeholder:text-[#13224B]/30 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] focus:border-transparent transition"
            />
            <p class="text-[#13224B]/45 text-xs mt-1.5 leading-relaxed">
                8 caractères minimum, avec une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&amp;).
            </p>
            @error('password') <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button
            type="submit"
            class="w-full mt-6 bg-[#1D4ED8] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#1741B8] transition"
        >
            Créer mon compte
        </button>

        <p class="text-center text-sm text-[#13224B]/60 mt-6">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-medium text-[#1D4ED8] hover:underline">Se connecter</a>
        </p>
    </form>
</x-guest-layout>