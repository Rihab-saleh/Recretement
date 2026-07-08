<x-app-shell>
    <div class="max-w-4xl mx-auto px-6 py-8">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Mes congés</h1>
            <a href="{{ route('conges.create') }}"
               class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow transition">
                + Nouvelle demande
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4 text-xs font-medium text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#60A5FA"></span> En attente
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#1D4ED8"></span> Accepté
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background:#B4472B"></span> Refusé
                    </span>
                </div>
                <p class="text-xs text-gray-400">Cliquez sur une date pour demander un congé</p>
            </div>
            <div id="calendar"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                height: 'auto',
                selectable: true,
                selectMirror: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth',
                },
                events: '{{ route('conges.events') }}',

                dateClick: function (info) {
                    window.location.href = '{{ route('conges.create') }}'
                        + '?start=' + info.dateStr
                        + '&end=' + info.dateStr;
                },

                select: function (info) {
                    const end = new Date(info.end);
                    end.setDate(end.getDate() - 1);
                    const endStr = end.toISOString().slice(0, 10);

                    window.location.href = '{{ route('conges.create') }}'
                        + '?start=' + info.startStr
                        + '&end=' + endStr;
                },
            });
            calendar.render();
        });
    </script>
</x-app-shell>