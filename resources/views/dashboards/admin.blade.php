<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="mb-10 relative overflow-hidden rounded-2xl bg-[#13224B] text-[#F1F5FB] px-8 py-10 shadow-xl shadow-black/10">
            <div class="absolute inset-x-8 top-6 h-px bg-[#60A5FA]/40"></div>
            <p class="text-[11px] uppercase tracking-[0.25em] text-[#60A5FA] font-mono mb-4">
                Administration générale
            </p>
            <h1 class="font-serif text-4xl font-semibold tracking-tight">
                Bienvenue, {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
            </h1>
            <p class="mt-3 text-[#F1F5FB]/60 font-mono text-sm">
                Vue d'ensemble &middot; {{ \Illuminate\Support\Carbon::create($annee, $mois, 1)->translatedFormat('F Y') }}
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 px-5 py-4 rounded-xl bg-[#DBEAFE] border border-[#BFDBFE] text-[#1D4ED8] text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-3 mb-10 bg-white border border-[#DCE6F5] rounded-2xl overflow-hidden divide-x divide-y lg:divide-y-0 divide-[#DCE6F5] shadow-sm">
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Employés affectés</p>
                <p class="text-4xl font-mono font-semibold text-[#1D4ED8]">{{ $effectifs['employes'] }}</p>
            </div>
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Managers</p>
                <p class="text-4xl font-mono font-semibold text-[#13224B]">{{ $effectifs['managers'] }}</p>
            </div>
            <div class="p-6">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono mb-3">Équipe RH</p>
                <p class="text-4xl font-mono font-semibold text-[#13224B]">{{ $effectifs['rh'] }}</p>
            </div>
            
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">

            <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm p-6">
                <h2 class="font-serif text-xl font-semibold text-[#13224B] mb-4">Recrutement</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#64748B] font-mono">Offres ouvertes</span>
                        <span class="font-mono font-semibold text-[#1D4ED8]">{{ $offresOuvertes }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#64748B] font-mono">Offres fermées</span>
                        <span class="font-mono font-semibold text-[#13224B]">{{ $offresFermees }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#64748B] font-mono">Candidatures en attente</span>
                        <span class="font-mono font-semibold text-[#60A5FA]">{{ $candidaturesEnAttente }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm p-6">
                <h2 class="font-serif text-xl font-semibold text-[#13224B] mb-4">Masse salariale du mois</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#64748B] font-mono">Bulletins générés</span>
                        <span class="font-mono font-semibold text-[#13224B]">{{ $bulletinsGeneres }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#64748B] font-mono">Total net versé</span>
                        <span class="font-mono font-semibold text-[#1D4ED8]">{{ number_format($masseSalariale, 2) }} DT</span>
                    </div>
                    <a href="{{ route('rh.paiement') }}" class="inline-block mt-2 text-xs font-mono text-[#1D4ED8] hover:underline">
                        Voir le détail des paiements →
                    </a>
                </div>
            </div>
        </div>


        <div class="mb-4 flex items-end justify-between border-b border-[#DCE6F5] pb-3">
            <h2 class="font-serif text-2xl font-semibold text-[#13224B]">Répartition par département</h2>
        </div>
        <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm p-6 mb-12">
            @forelse($departements as $dep)
                <a href="{{ route('admin.departement.employes', $dep->affectation) }}"
                   class="flex items-center justify-between py-2.5 border-b border-[#F1F5FB] last:border-0 group hover:bg-[#F9FAFC] -mx-2 px-2 rounded-lg transition">
                    <span class="text-sm font-medium text-[#13224B] capitalize group-hover:text-[#1D4ED8] group-hover:underline">{{ $dep->affectation }}</span>
                    <span class="flex items-center gap-2">
                        <span class="text-sm font-mono text-[#64748B]">{{ $dep->total }} employé(s)</span>
                        <svg class="h-4 w-4 text-[#94A3B8] group-hover:text-[#1D4ED8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>
            @empty
                <p class="text-sm text-[#64748B] font-mono">Aucun employé affecté pour le moment.</p>
            @endforelse
        </div>


        <div class="mb-4 flex items-end justify-between border-b border-[#DCE6F5] pb-3">
            <h2 class="font-serif text-2xl font-semibold text-[#13224B]">Gestion des comptes</h2>
        </div>

        @if(session('error'))
            <div class="mb-4 px-5 py-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm overflow-hidden mb-12" x-data="{ showCreate: false, role: 'manager' }">

            <div class="p-6 border-b border-[#DCE6F5]">
                <button type="button" @click="showCreate = !showCreate"
                        class="px-4 py-2 rounded-lg bg-[#1D4ED8] text-white text-sm font-semibold hover:bg-[#1741B8] shadow transition">
                    <span x-text="showCreate ? '− Annuler' : '+ Nouveau compte'"></span>
                </button>

                <form method="POST" action="{{ route('personnes.store') }}" x-show="showCreate" x-cloak
                      class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Rôle</label>
                        <select name="role" x-model="role" required class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                            <option value="manager">Manager</option>
                            <option value="rh">RH</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Nom</label>
                        <input type="text" name="nom" required class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Prénom</label>
                        <input type="text" name="prenom" required class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Email</label>
                        <input type="email" name="email" required class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div x-show="role === 'manager'" x-cloak>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Département</label>
                        <input type="text" name="departement" :required="role === 'manager'" class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-mono uppercase tracking-wide text-[#64748B]">Mot de passe</label>
                        <input type="password" name="password" required class="mt-1 w-full rounded-lg border border-[#DCE6F5] focus:border-[#1D4ED8] focus:ring-[#1D4ED8] text-sm">
                        <p class="mt-1 text-[11px] text-[#94A3B8]">8 car. min., majuscule, minuscule, chiffre, caractère spécial (@$!%*?&).</p>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1D4ED8] text-white text-sm font-semibold hover:bg-[#1741B8] shadow transition">
                            Créer le compte
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 border-b border-[#DCE6F5]">
                <h3 class="text-xs font-mono uppercase tracking-wide text-[#64748B]">
                    Managers &amp; RH existants ({{ $personnes->whereIn('role', ['manager', 'rh'])->count() }})
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-[#F5F6FA] text-[#64748B] text-[11px] uppercase tracking-wide font-mono">
                    <tr>
                        <th class="text-left px-6 py-3">Nom</th>
                        <th class="text-left px-6 py-3">Rôle</th>
                        <th class="text-left px-6 py-3">Email</th>
                        <th class="text-left px-6 py-3">Département</th>
                        <th class="text-right px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5FB]">
                    @forelse($personnes->whereIn('role', ['manager', 'rh']) as $p)
                        <tr class="hover:bg-[#F9FAFC]">
                            <td class="px-6 py-4 font-medium text-[#13224B]">{{ $p->prenom }} {{ $p->nom }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono uppercase tracking-wide {{ $p->role === 'rh' ? 'bg-[#DBEAFE] text-[#1D4ED8]' : 'bg-[#F1F5FB] text-[#64748B]' }}">
                                    {{ $p->role === 'rh' ? 'RH' : 'Manager' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[#64748B]">{{ $p->email }}</td>
                            <td class="px-6 py-4 text-[#64748B]">{{ $p->departement ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('personnes.destroy', $p->id) }}" onsubmit="return confirm('Supprimer ce compte définitivement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-mono text-red-600 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-[#64748B] font-mono text-sm">Aucun manager ou RH pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-shell>