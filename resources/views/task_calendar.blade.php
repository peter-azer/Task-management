@extends('layout.page')

@section('app-header')
<h1 class="text-xl font-bold">Task Calendar</h1>
@endsection

@section('content')
<div class="container p-4">
    <div id="calendar"></div>
</div>
@endsection


<style>
    .fc-daygrid-event {
        border: none !important;
        margin-top: 4px !important;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        const tasks = @json($userTasks);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: tasks,

            // Customize how events look
            eventContent: function(arg) {
                let task = arg.event.extendedProps; // includes status + extra fields
                let title = arg.event.title;
                let start = arg.event.start.toLocaleDateString();
                let end = arg.event.end ? arg.event.end.toLocaleDateString() : null;

                // status coloring
                let bgColor = 'bg-amber-300'; // default pending
                if (task.status == 1) {
                    bgColor = 'bg-green-300'; // done
                } else if (end === start || end >= new Date()) {
                    bgColor = 'bg-blue-300'; // no dates set
                } else if (end && new Date(end) < new Date() && task.status == 0) {
                    bgColor = 'bg-red-300'; // late
                }

                // build custom HTML
                let innerHtml = `
                    <div class="p-1 w-full text-xs text-white rounded ${bgColor}">
                        <div class="font-bold text-gray-800">${title}</div>
                        <div class="text-gray-600">${start ? start : ''}${end ? ' → ' + end : ''}</div>
                    </div>
                `;

                return {
                    html: innerHtml
                };
            },

            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            }
        });

        calendar.render();
    });
</script>