<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="mb-10 relative overflow-hidden rounded-2xl bg-[#13224B] text-[#F1F5FB] px-8 py-10 shadow-xl shadow-black/10">
            <div class="absolute inset-x-8 top-6 h-px bg-[#60A5FA]/40"></div>
            <p class="text-[11px] uppercase tracking-[0.25em] text-[#60A5FA] font-mono mb-4">
                Administration plateforme
            </p>
            <h1 class="font-serif text-4xl font-semibold tracking-tight">
                Bienvenue, {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
            </h1>
            <p class="mt-3 text-[#F1F5FB]/60 font-mono text-sm">
                Vue d'ensemble &middot; toutes les entreprises
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 px-5 py-4 rounded-xl bg-[#DBEAFE] border border-[#BFDBFE] text-[#1D4ED8] text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 px-5 py-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 mb-10 bg-white border border-[#DCE6F5] rounded-2xl overflow-hidden divide-x divide-y lg:divide-y-0 divide-[#DCE6F5] shadow-sm">
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Entreprises</p>
                <p class="text-4xl font-mono font-semibold text-[#1D4ED8]">{{ $totalEntreprises }}</p>
            </div>
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Admins</p>
                <p class="text-4xl font-mono font-semibold text-[#13224B]">{{ $totalAdmins }}</p>
            </div>
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Managers</p>
                <p class="text-4xl font-mono font-semibold text-[#13224B]">{{ $totalManagers }}</p>
            </div>
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Équipe RH</p>
                <p class="text-4xl font-mono font-semibold text-[#13224B]">{{ $totalRh }}</p>
            </div>
        </div>

        {{-- Entreprises management --}}
        <div class="mb-4 flex items-end justify-between border-b border-[#DCE6F5] pb-3">
            <h2 class="font-serif text-2xl font-semibold text-[#13224B]">Entreprises</h2>
        </div>

        <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm overflow-hidden mb-12"
             x-data="{ showCreate: {{ $errors->any() ? 'true' : 'false' }} }">

            <div class="p-6 border-b border-[#DCE6F5]">
                <button type="button" @click="showCreate = !showCreate"
                        class="px-4 py-2 rounded-lg bg-[#1D4ED8] text-white text-sm font-semibold hover:bg-[#1741B8] shadow transition">
                    <span x-text="showCreate ? 'Annuler' : '+ Nouvelle entreprise'"></span>
                </button>

                @if($errors->any())
                    <div class="mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                        <p class="font-semibold mb-1">Le formulaire contient des erreurs :</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('entreprises.store') }}" enctype="multipart/form-data" x-show="showCreate" x-cloak
                      class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf

                    <div class="sm:col-span-2">
                        <p class="text-xs font-mono uppercase tracking-wide text-[#94A3B8] mb-1">Entreprise</p>
                    </div>

                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Nom de l'entreprise</label>
                        <input type="text" name="entreprise_nom" required value="{{ old('entreprise_nom') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        @error('entreprise_nom') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Logo (optionnel)</label>
                        <input type="file" name="logo" accept="image/*"
                               class="mt-1 w-full text-sm text-[#64748B] file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#DBEAFE] file:text-[#1D4ED8] hover:file:bg-[#BFDBFE]">
                        @error('logo') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Email entreprise</label>
                        <input type="email" name="entreprise_email" value="{{ old('entreprise_email') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        @error('entreprise_email') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Téléphone</label>
                        <input type="text" name="entreprise_telephone" value="{{ old('entreprise_telephone') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Ville</label>
                        <input type="text" name="entreprise_ville" value="{{ old('entreprise_ville') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Adresse</label>
                        <input type="text" name="entreprise_adresse" value="{{ old('entreprise_adresse') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Pays</label>
                        <input type="text" name="entreprise_pays" value="{{ old('entreprise_pays') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>

                    <div class="sm:col-span-2 pt-2 mt-1 border-t border-[#F1F5FB]">
                        <p class="text-xs font-mono uppercase tracking-wide text-[#94A3B8] mb-1">Compte admin de l'entreprise</p>
                    </div>

                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Nom</label>
                        <input type="text" name="admin_nom" required value="{{ old('admin_nom') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        @error('admin_nom') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Prénom</label>
                        <input type="text" name="admin_prenom" required value="{{ old('admin_prenom') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        @error('admin_prenom') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Email</label>
                        <input type="email" name="admin_email" required value="{{ old('admin_email') }}"
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        @error('admin_email') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Mot de passe</label>
                        <input type="password" name="password" required
                               class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        <p class="mt-1 text-[11px] text-[#94A3B8]">8 car. min., majuscule, minuscule, chiffre, caractère spécial (@$!%*?&).</p>
                        @error('password') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1D4ED8] text-white text-sm font-semibold hover:bg-[#1741B8] shadow transition">
                            Créer l'entreprise
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 border-b border-[#DCE6F5]">
                <h3 class="text-xs font-mono uppercase tracking-wide text-[#64748B]">
                    Entreprises enregistrées ({{ $entreprises->count() }})
                </h3>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-[#F5F6FA] text-[#64748B] text-[11px] uppercase tracking-wide font-mono">
                    <tr>
                        <th class="text-left px-6 py-3">Entreprise</th>
                        <th class="text-left px-6 py-3">Admin(s)</th>
                        <th class="text-left px-6 py-3">Managers</th>
                        <th class="text-left px-6 py-3">RH</th>
                        <th class="text-right px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5FB]">
                    @forelse($entreprises as $entreprise)
                        <tr class="hover:bg-[#F9FAFC] align-top">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($entreprise->logo)
                                        <img src="{{ Storage::disk('public')->url($entreprise->logo) }}" alt="{{ $entreprise->nom }}"
                                             class="h-9 w-9 rounded-md object-cover shrink-0 border border-[#DCE6F5]">
                                    @else
                                        <span class="h-9 w-9 rounded-md bg-[#F1F5FB] border border-[#DCE6F5] shrink-0 flex items-center justify-center text-[11px] font-mono text-[#94A3B8]">
                                            {{ strtoupper(substr($entreprise->nom, 0, 1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-medium text-[#13224B]">{{ $entreprise->nom }}</p>
                                        <p class="text-xs font-mono text-[#64748B] mt-0.5">{{ $entreprise->email ?? '—' }}</p>
                                        @if($entreprise->ville || $entreprise->pays)
                                            <p class="text-xs font-mono text-[#94A3B8] mt-0.5">
                                                {{ collect([$entreprise->ville, $entreprise->pays])->filter()->implode(', ') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @forelse($entreprise->admins as $admin)
                                    <p class="text-[#13224B]">{{ $admin->prenom }} {{ $admin->nom }}</p>
                                    <p class="text-xs font-mono text-[#64748B]">{{ $admin->email }}</p>
                                @empty
                                    <span class="text-xs font-mono text-[#94A3B8]">Aucun admin</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 font-mono font-semibold text-[#1D4ED8]">{{ $entreprise->managers_count }}</td>
                            <td class="px-6 py-4 font-mono font-semibold text-[#13224B]">{{ $entreprise->rh_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('entreprises.destroy', $entreprise->id) }}"
                                          onsubmit="return confirm('Supprimer « {{ $entreprise->nom }} » ainsi que tous ses comptes (admin, managers, RH, candidats) ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-mono text-red-600 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-[#64748B] font-mono text-sm">Aucune entreprise pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-shell>