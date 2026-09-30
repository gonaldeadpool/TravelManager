

import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import itLocale from '@fullcalendar/core/locales/it';

window.Alpine = Alpine;

Alpine.data('dashboardWidgetOrder', (config) => ({
	ordineSalvato: [...config.ordine],
	elementoTrascinato: null,
	messaggioOrdine: '',
	init() {
		this.ordinaElementi(this.ordineSalvato);
	},
	ordinaElementi(ordine) {
		const griglia = this.$refs.griglia;
		if (!griglia) return;

		const priorita = new Map(ordine.map((chiave, indice) => [chiave, indice]));
		const elementi = [...griglia.querySelectorAll(':scope > [data-dashboard-widget]')];
		elementi
			.sort((primo, secondo) => (priorita.get(primo.dataset.dashboardWidget) ?? Infinity) - (priorita.get(secondo.dataset.dashboardWidget) ?? Infinity))
			.forEach((elemento) => griglia.append(elemento));
	},
	iniziaTrascinamento(evento) {
		const widget = evento.target.closest('[data-dashboard-widget]');
		if (!widget || !evento.dataTransfer) return;

		this.elementoTrascinato = widget;
		evento.dataTransfer.effectAllowed = 'move';
		evento.dataTransfer.setData('text/plain', widget.dataset.dashboardWidget);
		widget.classList.add('opacity-50');
	},
	riordinaDuranteTrascinamento(evento) {
		const widgetDestinazione = evento.target.closest('[data-dashboard-widget]');
		const widgetTrascinato = this.elementoTrascinato;
		const griglia = this.$refs.griglia;
		if (!widgetDestinazione || !widgetTrascinato || widgetDestinazione === widgetTrascinato || !griglia) return;

		const rettangolo = widgetDestinazione.getBoundingClientRect();
		const scartoX = (evento.clientX - rettangolo.left) / rettangolo.width - 0.5;
		const scartoY = (evento.clientY - rettangolo.top) / rettangolo.height - 0.5;
		const dopo = Math.abs(scartoX) > Math.abs(scartoY) ? scartoX > 0 : scartoY > 0;

		if (dopo) {
			if (widgetDestinazione.nextElementSibling !== widgetTrascinato) griglia.insertBefore(widgetTrascinato, widgetDestinazione.nextElementSibling);
		} else if (widgetDestinazione !== widgetTrascinato.nextElementSibling) {
			griglia.insertBefore(widgetTrascinato, widgetDestinazione);
		}
	},
	async salvaOrdine() {
		const griglia = this.$refs.griglia;
		const ordine = [...griglia.querySelectorAll(':scope > [data-dashboard-widget]')].map((widget) => widget.dataset.dashboardWidget);

		try {
			const risposta = await fetch(config.url, {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': config.csrf,
				},
				body: JSON.stringify({ order: ordine }),
			});
			if (!risposta.ok) throw new Error('Salvataggio ordine non riuscito');

			this.ordineSalvato = ordine;
			this.messaggioOrdine = 'Ordine dei widget salvato.';
		} catch {
			this.ordinaElementi(this.ordineSalvato);
			this.messaggioOrdine = 'Impossibile salvare l’ordine dei widget.';
		}
	},
	terminaTrascinamento() {
		this.elementoTrascinato?.classList.remove('opacity-50');
		this.elementoTrascinato = null;
	},
}));

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
