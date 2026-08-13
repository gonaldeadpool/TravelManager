

import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import itLocale from '@fullcalendar/core/locales/it';

window.Alpine = Alpine;

Alpine.start();

const calendario = document.getElementById('calendario-viaggi');

if (calendario) {
	new Calendar(calendario, {
		plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
		locale: itLocale,
		initialView: 'dayGridMonth',
		firstDay: 1,
		height: 'auto',
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
		},
		buttonText: {
			today: 'Oggi',
			month: 'Mese',
			week: 'Settimana',
			day: 'Giorno',
			list: 'Elenco',
		},
		events: calendario.dataset.eventiUrl,
		eventClick(info) {
			if (info.event.url) {
				info.jsEvent.preventDefault();
				window.location.assign(info.event.url);
			}
		},
		eventDidMount(info) {
			info.el.title = `${info.event.title} - ${info.event.extendedProps.destinazione}`;
		},
	}).render();
}
