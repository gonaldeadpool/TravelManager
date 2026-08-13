

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
	const menuContesto = document.createElement('button');
	menuContesto.type = 'button';
	menuContesto.textContent = 'Nuovo viaggio';
	menuContesto.className = 'fixed z-50 hidden rounded bg-gray-800 px-3 py-2 text-sm text-white shadow hover:bg-gray-700';
	document.body.append(menuContesto);

	let dataSelezionata = null;
	const nascondiMenu = () => menuContesto.classList.add('hidden');

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
		dayCellDidMount(info) {
			info.el.addEventListener('contextmenu', (evento) => {
				evento.preventDefault();
				const anno = info.date.getFullYear();
				const mese = String(info.date.getMonth() + 1).padStart(2, '0');
				const giorno = String(info.date.getDate()).padStart(2, '0');
				dataSelezionata = `${anno}-${mese}-${giorno}`;
				menuContesto.style.left = `${evento.clientX}px`;
				menuContesto.style.top = `${evento.clientY}px`;
				menuContesto.classList.remove('hidden');
			});
		},
	}).render();

	menuContesto.addEventListener('click', () => {
		const url = new URL(calendario.dataset.nuovoViaggioUrl, window.location.origin);
		url.searchParams.set('data_partenza', dataSelezionata);
		window.location.assign(url);
	});

	document.addEventListener('click', nascondiMenu);
	document.addEventListener('scroll', nascondiMenu, true);
}
