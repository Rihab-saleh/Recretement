<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Letterhead --}}
        <div class="mb-10 relative overflow-hidden rounded-2xl bg-[#13224B] text-[#F1F5FB] px-8 py-10 shadow-xl shadow-black/10">
            <div class="absolute inset-x-8 top-6 h-px bg-[#60A5FA]/40"></div>
            <p class="text-[11px] uppercase tracking-[0.25em] text-[#60A5FA] font-mono mb-4">
                Service Ressources Humaines
            </p>
            <h1 class="font-serif text-4xl font-semibold tracking-tight">
                Bienvenue, {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
            </h1>
            <p class="mt-3 text-[#F1F5FB]/60 font-mono text-sm">
                Tableau de bord &middot; Recrutement &amp; Intégration
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 px-5 py-4 rounded-xl bg-[#DBEAFE] border border-[#BFDBFE] text-[#1D4ED8] text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats ledger --}}
        <div class="grid grid-cols-2 mb-10 bg-white border border-[#DCE6F5] rounded-2xl overflow-hidden divide-x divide-[#DCE6F5] shadow-sm">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-4 w-4 text-[#60A5FA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12M6 21h12M6 3c0 4 4 6 6 6s6-2 6-6M6 21c0-4 4-6 6-6s6 2 6 6" />
                    </svg>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono">En attente de finalisation</p>
                </div>
                <p class="text-4xl font-mono font-semibold text-[#60A5FA]">{{ $enAttente }}</p>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-4 w-4 text-[#1D4ED8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-[#64748B] font-mono">Employés affectés</p>
                </div>
                <p class="text-4xl font-mono font-semibold text-[#1D4ED8]">{{ $affectes }}</p>
            </div>
        </div>

        {{-- Candidats à affecter --}}
        <div class="mb-4 flex items-end justify-between border-b border-[#DCE6F5] pb-3">
            <h2 class="font-serif text-2xl font-semibold text-[#13224B]">Candidats à affecter</h2>
            <span class="text-xs font-mono uppercase tracking-wide text-[#64748B]">{{ $enAttente }} en attente</span>
        </div>

        <div class="space-y-4 mb-12 mt-5">
            @forelse($candidatsEnAttente as $candidature)
                <div class="relative bg-white rounded-xl border border-[#DCE6F5] shadow-sm hover:shadow-md transition p-6 pl-8 overflow-hidden"
                    x-data="{ showAffect: false }">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#60A5FA]"></div>

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="h-11 w-11 rounded-full bg-[#13224B] text-[#F1F5FB] flex items-center justify-center font-serif font-semibold text-lg shrink-0">
                                {{ strtoupper(substr($candidature->personne?->prenom ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-serif text-lg font-semibold text-[#13224B]">
                                    {{ $candidature->personne?->prenom }} {{ $candidature->personne?->nom }}
                                </h3>
                                <p class="text-xs font-mono uppercase tracking-wide text-[#64748B] mt-0.5">
                                    {{ $candidature->offre?->intitule ?? '—' }} &middot;
                                    {{ $candidature->offre?->departement ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="showAffect = !showAffect"
                            class="px-5 py-2.5 bg-[#13224B] text-white rounded-lg text-sm font-semibold hover:bg-[#1B2E63] transition shrink-0">
                            Affecter le candidat
                        </button>
                    </div>

                    <div x-show="showAffect" x-cloak x-transition class="mt-5">
                        <form method="POST" action="{{ route('rh.candidatures.affecter', $candidature->id) }}"
                            class="bg-[#EFF6FF] border border-[#BFDBFE] rounded-xl p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-mono uppercase tracking-wide text-[#1E40AF] mb-1.5">
                                    Département d'affectation
                                </label>
                                <input type="text" name="departement" required readonly
                                    value="{{ $candidature->offre?->departement }}"
                                    class="w-full border border-[#BFDBFE] bg-[#F3F4F6] rounded-lg px-3 py-2 text-sm text-[#6B7280] cursor-not-allowed focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-mono uppercase tracking-wide text-[#1E40AF] mb-1.5">
                                    Salaire proposé (DT)
                                </label>
                                <input type="number" step="0.01" min="0" name="salaire_propose" required readonly
                                    value="{{ $candidature->offre?->salaire }}"
                                    class="w-full border border-[#BFDBFE] bg-[#F3F4F6] rounded-lg px-3 py-2 text-sm font-mono text-[#6B7280] cursor-not-allowed focus:outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-mono uppercase tracking-wide text-[#1E40AF] mb-1.5">
                                    Responsable attribué
                                </label>
                                <input type="text" name="responsable_nom" required
                                    class="w-full border border-[#BFDBFE] bg-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#60A5FA] focus:border-[#60A5FA]">
                            </div>
                            <div class="md:col-span-3 flex gap-2 pt-1">
                                <button type="submit"
                                    class="px-4 py-2 bg-[#13224B] text-white rounded-lg text-sm font-semibold hover:bg-[#1B2E63] transition">
                                    Confirmer l'affectation et notifier l'employé
                                </button>
                                <button type="button" @click="showAffect = false"
                                    class="px-4 py-2 border border-[#BFDBFE] rounded-lg text-sm text-[#1E40AF] hover:bg-white transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-[#C7D9F5] p-10 text-center">
                    <svg class="h-9 w-9 mx-auto text-[#93B4E0] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                    <p class="text-[#64748B] font-mono text-sm">Aucun dossier en attente d'affectation.</p>
                </div>
            @endforelse
        </div>

        {{-- Liste des candidats affectés --}}
        <div class="mb-4 flex items-end justify-between border-b border-[#DCE6F5] pb-3">
            <h2 class="font-serif text-2xl font-semibold text-[#13224B]">Candidats affectés</h2>
            <span class="text-xs font-mono uppercase tracking-wide text-[#64748B]">{{ $affectes }} employé(s) intégré(s)</span>
        </div>

        <div class="mt-5">
            @forelse($candidatsAffectes as $candidature)
                @php $c = $candidature->personne?->candidat; @endphp
                <div class="relative bg-white rounded-xl border border-[#DCE6F5] shadow-sm hover:shadow-md transition p-6 pl-8 mb-4 overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#1D4ED8]"></div>

                    {{-- stamp --}}
                    <div class="absolute top-4 right-5 rotate-[-8deg] hidden sm:block">
                        <div class="h-[70px] w-[70px] rounded-full border-2 border-[#1D4ED8]/70 text-[#1D4ED8] flex flex-col items-center justify-center leading-tight">
                            <span class="text-[9px] font-bold tracking-widest uppercase">Affecté</span>
                            <span class="text-[8px] font-mono mt-0.5">{{ optional($c?->date_affectation)->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 pr-16 sm:pr-20">
                        <div class="flex items-center gap-4 shrink-0">
                            <div
                                class="h-11 w-11 rounded-full bg-[#DBEAFE] text-[#1D4ED8] flex items-center justify-center font-serif font-semibold text-lg shrink-0">
                                {{ strtoupper(substr($candidature->personne?->prenom ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-serif text-lg font-semibold text-[#13224B]">
                                    {{ $candidature->personne?->prenom }} {{ $candidature->personne?->nom }}
                                </h3>
                                <p class="text-xs font-mono text-[#64748B] mt-0.5">{{ $candidature->personne?->email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-3 text-sm border-t sm:border-t-0 sm:border-l border-[#DCE6F5] pt-4 sm:pt-0 sm:pl-6 w-full lg:w-auto">
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-wide text-[#64748B]">Poste</p>
                                <p class="font-medium text-[#13224B] mt-0.5">{{ $candidature->offre?->intitule ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-wide text-[#64748B]">Département</p>
                                <p class="font-medium text-[#13224B] mt-0.5">{{ $c?->affectation ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-wide text-[#64748B]">Salaire proposé</p>
                                <p class="font-mono font-medium text-[#13224B] mt-0.5">
                                    {{ $c?->salaire_propose ? number_format($c->salaire_propose, 2) . ' DT' : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-wide text-[#64748B]">Responsable</p>
                                <p class="font-medium text-[#13224B] mt-0.5">{{ $c?->responsable_nom ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-dashed border-[#C7D9F5] p-12 text-center">
                    <svg class="h-10 w-10 mx-auto text-[#93B4E0] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l1-3a2 2 0 012-2h12a2 2 0 012 2l1 3M9 12h6" />
                    </svg>
                    <h3 class="font-serif text-xl font-semibold text-[#13224B]">Aucun dossier clos pour le moment</h3>
                    <p class="text-[#64748B] font-mono text-sm mt-2">
                        Les candidats affectés apparaîtront ici une fois leur intégration finalisée.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>