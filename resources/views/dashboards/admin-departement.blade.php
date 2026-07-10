<x-app-shell>
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Fil d'ariane / retour --}}
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-mono uppercase tracking-wide text-[#1D4ED8] hover:underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour au dashboard
            </a>
        </div>

        {{-- Letterhead --}}
        <div class="mb-10 relative overflow-hidden rounded-2xl bg-[#13224B] text-[#F1F5FB] px-8 py-10 shadow-xl shadow-black/10">
            <div class="absolute inset-x-8 top-6 h-px bg-[#60A5FA]/40"></div>
            <p class="text-[11px] uppercase tracking-[0.25em] text-[#60A5FA] font-mono mb-4">
                Répartition par département
            </p>
            <h1 class="font-serif text-4xl font-semibold tracking-tight capitalize">
                {{ $departement }}
            </h1>
            <p class="mt-3 text-[#F1F5FB]/60 font-mono text-sm">
                {{ $employes->count() }} employé(s) affecté(s)
            </p>
        </div>

        {{-- Liste des employés --}}
        <div class="bg-white rounded-2xl border border-[#DCE6F5] shadow-sm overflow-hidden mb-12">
            <table class="w-full text-sm">
                <thead class="bg-[#F5F6FA] text-[#64748B] text-[11px] uppercase tracking-wide font-mono">
                    <tr>
                        <th class="text-left px-6 py-3">Nom</th>
                        <th class="text-left px-6 py-3">Email</th>
                        <th class="text-left px-6 py-3">Salaire proposé</th>
                        <th class="text-left px-6 py-3">Responsable</th>
                        <th class="text-left px-6 py-3">Date d'affectation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5FB]">
                    @forelse($employes as $employe)
                        <tr class="hover:bg-[#F9FAFC]">
                            <td class="px-6 py-4 font-medium text-[#13224B]">
                                {{ $employe->personne?->prenom }} {{ $employe->personne?->nom }}
                            </td>
                            <td class="px-6 py-4 text-[#64748B]">{{ $employe->personne?->email ?? '—' }}</td>
                            <td class="px-6 py-4 font-mono text-[#1D4ED8]">
                                {{ $employe->salaire_propose !== null ? number_format($employe->salaire_propose, 2) . ' DT' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-[#64748B]">{{ $employe->responsable_nom ?? '—' }}</td>
                            <td class="px-6 py-4 text-[#64748B] font-mono">
                                {{ $employe->date_affectation ? $employe->date_affectation->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-[#64748B] font-mono text-sm">
                                Aucun employé affecté à ce département pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-shell>